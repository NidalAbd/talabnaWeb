<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Deal;
use App\Models\Message;
use App\Models\ServicePost;
use App\Notifications\ChatMessageFcmNotification;
use App\Services\Chat\ChatLanguageService;
use App\Services\Chat\ChatTranslationBatcher;
use App\Services\Chat\ChatTranslationQuota;
use App\Support\Blocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Real in-app chat between users — separate from (and alongside) the
 * external contact sheet (WhatsApp/call/email). Deliberately simple:
 * polling-based on the client, no websockets/push, matching the scope
 * this needed.
 */
class ConversationController extends Controller
{
    public function __construct(
        private readonly ChatLanguageService $lang,
        private readonly ChatTranslationQuota $quota,
        private readonly ChatTranslationBatcher $batcher,
    ) {
    }

    /** Set by withTranslations(): a message needed translating but the reader has no points. */
    private bool $translationNeedsPoints = false;

    /** List the authenticated user's conversations, most recent first. */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $blocked = Blocks::blockedBy($userId);

        $conversations = Conversation::where(fn ($q) => $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId))
            ->when($blocked, fn ($q) => $q->whereNotIn('user_one_id', $blocked)->whereNotIn('user_two_id', $blocked))
            ->with(['userOne.photos', 'userTwo.photos', 'servicePost.photos'])
            ->withCount(['messages as unread_for_me' => fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at')])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $conversations->getCollection()->transform(function (Conversation $conversation) use ($userId) {
            return $this->formatConversation($conversation, $userId);
        });

        return response()->json($conversations);
    }

    /** Start (or fetch the existing) conversation with another user. */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|integer|exists:users,id',
            'service_post_id' => 'nullable|integer|exists:service_posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userId = Auth::id();
        $recipientId = (int) $request->recipient_id;

        if ($recipientId === $userId) {
            return response()->json(['error' => 'Cannot start a conversation with yourself'], 422);
        }

        if (Blocks::exists($userId, $recipientId)) {
            return response()->json(['error' => 'You cannot message this user'], 403);
        }

        $conversation = Conversation::between($userId, $recipientId, $request->service_post_id);
        $conversation->load(['userOne.photos', 'userTwo.photos', 'servicePost.photos']);

        return response()->json($this->formatConversation($conversation, $userId), 201);
    }

    /** One conversation (used when a push notification opens a chat). */
    public function show(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }
        $conversation->load(['userOne.photos', 'userTwo.photos', 'servicePost.photos']);

        return response()->json($this->formatConversation($conversation, $userId));
    }

    /**
     * Paginated messages, newest first, plus live state for the header: whether the other
     * person is typing, when they were last seen, and the deal in this chat (if any).
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        // The app polls every few seconds while the chat is on screen: no push for this chat meanwhile.
        ChatMessageFcmNotification::markViewing($conversation->id, $userId);

        $perPage = min(50, (int) $request->input('per_page', 30));
        $query = $conversation->messages()->with('replyTo')->orderByDesc('id');
        // Cheap polling: only what is new since the last message the app has.
        if ($request->filled('after_id')) {
            $query->where('id', '>', (int) $request->input('after_id'));
        }
        $page = $query->paginate($perPage);

        $other = $conversation->otherUser($userId);
        // The deal for the listing the chat is about now (a chat can move on to another listing).
        $deal = Deal::where('conversation_id', $conversation->id)
            ->where('service_post_id', $conversation->service_post_id)->orderByDesc('id')->first();

        return response()->json([
            'data' => $this->withTranslations($page->getCollection(), $userId),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'state' => [
                'other_typing' => Cache::has("chat:typing:{$conversation->id}:{$other->id}"),
                'other_last_seen_at' => optional($other->last_seen_at)->toIso8601String(),
                'other_online' => $other->last_seen_at && $other->last_seen_at->gt(now()->subMinutes(2)),
                'other_read_up_to' => Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', $userId)->whereNotNull('read_at')->max('id'),
                'deal' => $deal?->toPublic(),
                // Translation is paid with points: 1 point = MESSAGES_PER_POINT messages.
                'translation' => [
                    'remaining' => $this->quota->remaining($userId),
                    'per_point' => ChatTranslationQuota::MESSAGES_PER_POINT,
                    'needs_points' => $this->translationNeedsPoints,
                ],
            ],
        ]);
    }

    /**
     * Send a message: text, image (file), voice (file + duration), location (lat/lng/label)
     * or post (a listing card). Optional reply_to_id. The other person gets a push.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        $other = $conversation->otherUser($userId);
        if ($other && Blocks::exists($userId, $other->id)) {
            return response()->json(['error' => 'You cannot message this user'], 403);
        }

        $type = $request->input('type', 'text');
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:text,image,voice,location,post',
            'body' => ($type === 'text' ? 'required' : 'nullable') . '|string|max:2000',
            'file' => in_array($type, ['image', 'voice'], true)
                ? ($type === 'image' ? 'required|file|mimes:jpg,jpeg,png,webp,heic|max:8192' : 'required|file|mimes:m4a,aac,mp3,mp4,ogg,wav,webm|max:6144')
                : 'nullable',
            'duration' => 'nullable|integer|min:0|max:600',
            'lat' => $type === 'location' ? 'required|numeric|between:-90,90' : 'nullable',
            'lng' => $type === 'location' ? 'required|numeric|between:-180,180' : 'nullable',
            'label' => 'nullable|string|max:200',
            'service_post_id' => $type === 'post' ? 'required|integer|exists:service_posts,id' : 'nullable',
            'reply_to_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $meta = null;
        if (in_array($type, ['image', 'voice'], true)) {
            $path = $request->file('file')->store("chat/{$conversation->id}", 'public');
            $meta = ['url' => 'storage/' . $path];
            if ($type === 'voice') $meta['duration'] = (int) $request->input('duration', 0);
        } elseif ($type === 'location') {
            $meta = ['lat' => (float) $request->lat, 'lng' => (float) $request->lng, 'label' => $request->label];
        } elseif ($type === 'post') {
            $post = ServicePost::with('photos')->find($request->service_post_id);
            $meta = [
                'id' => $post->id,
                'title' => $this->localized($post->title),
                'price' => $post->price,
                'currency' => $post->price_currency_code,
                'photo' => $this->cover($post),
            ];
            // A card for one of our own listings becomes the chat's pinned topic.
            if (in_array($post->user_id, [$conversation->user_one_id, $conversation->user_two_id], true)) {
                $conversation->switchTopic($post->id);
            }
        }

        $replyTo = null;
        if ($request->filled('reply_to_id')) {
            $replyTo = Message::where('id', $request->reply_to_id)->where('conversation_id', $conversation->id)->value('id');
        }

        $body = (string) ($request->body ?? '');
        // Release C: looks like a common scam (codes, paying first, gift cards, payment links)? The receiver sees a warning.
        if ($type === 'text' && ($warning = \App\Services\ChatSafety::warningFor($body))) {
            $meta = array_merge((array) ($meta ?? []), ['warning' => $warning]);
        }
        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'type' => $type,
            'body' => $body,
            // Written-in language, so the other person can read it translated.
            'lang' => in_array($type, ['text', 'post'], true) && $body !== ''
                ? ($this->lang->detect($body) ?? '-') : null,
            'meta' => $meta,
            'reply_to_id' => $replyTo,
        ]);
        $message->load('replyTo');

        // The other person reads another language: translate it for them in the next
        // batch (messages from all chats go to the API together), paid from their
        // points bundle. Without points it stays as written.
        $queued = false;
        $target = $other?->locale ? ChatLanguageService::base($other->locale) : null;
        if ($target && $message->lang && $message->lang !== '-' && $message->lang !== $target
            && $this->quota->take($other->id)) {
            $message->translate_to = $target;
            $message->saveQuietly();
            $queued = true;
        }

        $conversation->update([
            'last_message_body' => $message->preview(),
            'last_message_sender_id' => $userId,
            'last_message_at' => $message->created_at,
        ]);
        Cache::forget("chat:typing:{$conversation->id}:{$userId}");

        if ($queued) {
            $batcher = $this->batcher;
            dispatch(fn () => $batcher->flushSoon())->afterResponse();
        }

        // Push to the other person right after the response (no minute-long queue wait).
        if ($other) {
            $senderName = Auth::user()->name ?: Auth::user()->user_name;
            $preview = $message->preview();
            dispatch(function () use ($other, $conversation, $senderName, $preview) {
                try {
                    $other->notify(new ChatMessageFcmNotification($conversation->id, $senderName, $preview));
                } catch (\Throwable $e) {
                    Log::warning('Chat push failed: ' . $e->getMessage());
                }
            })->afterResponse();
        }

        return response()->json(['message' => $message->toPublic()], 201);
    }

    /**
     * Listings either person can bring into the chat ("I need this service"): both
     * participants' published posts, newest first, lightweight.
     */
    public function listings(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }
        $otherId = $conversation->user_one_id === $userId ? $conversation->user_two_id : $conversation->user_one_id;
        $map = fn ($p) => [
            'id' => $p->id,
            'title' => $this->localized($p->title),
            'price' => $p->price,
            'currency' => $p->price_currency_code,
            'photo' => $this->cover($p),
            'owner_id' => $p->user_id,
        ];
        $posts = fn ($uid) => ServicePost::with('photos')
            ->where('user_id', $uid)->where('state', 'published')
            ->orderByDesc('id')->limit(50)
            ->get(['id', 'user_id', 'title', 'price', 'price_currency_code'])
            ->map($map)->values();

        return response()->json(['theirs' => $posts($otherId), 'mine' => $posts($userId)]);
    }

    /** Toggle my reaction (one emoji per person). */
    public function react(Request $request, Message $message): JsonResponse
    {
        $userId = Auth::id();
        $conversation = $message->conversation;
        if (!$conversation || !$this->isParticipant($conversation, $userId) || $message->deleted_at) {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        $emoji = (string) $request->input('emoji', '');
        if ($emoji === '' || mb_strlen($emoji) > 8) {
            return response()->json(['error' => 'Invalid emoji'], 422);
        }
        $reactions = $message->reactions ?: [];
        $already = in_array($userId, $reactions[$emoji] ?? [], true);
        foreach ($reactions as $e => $users) {
            $reactions[$e] = array_values(array_filter($users, fn ($u) => $u !== $userId));
            if (!$reactions[$e]) unset($reactions[$e]);
        }
        if (!$already) $reactions[$emoji][] = $userId;
        $message->update(['reactions' => $reactions ?: null]);

        return response()->json(['message' => $message->toPublic()]);
    }

    /** Delete my message for everyone (within 48 hours). */
    public function destroyMessage(Message $message): JsonResponse
    {
        $userId = Auth::id();
        if ((int) $message->sender_id !== (int) $userId) {
            return response()->json(['error' => 'You can only delete your own messages'], 403);
        }
        if ($message->created_at->lt(now()->subHours(48))) {
            return response()->json(['error' => 'Messages can be deleted for 48 hours'], 422);
        }
        $message->update(['deleted_at' => now(), 'body' => '', 'meta' => null, 'reactions' => null]);
        $conversation = $message->conversation;
        if ($conversation && (int) $conversation->messages()->max('id') === (int) $message->id) {
            $conversation->update(['last_message_body' => $message->preview()]);
        }

        return response()->json(['message' => $message->toPublic()]);
    }

    /** "Typing…" signal, kept for a few seconds. */
    public function typing(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }
        Cache::put("chat:typing:{$conversation->id}:{$userId}", 1, now()->addSeconds(6));

        return response()->json(['success' => true]);
    }

    /** Mark every message from the other participant as read. */
    public function markRead(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ---- Deals: both sides confirm a sale made through this chat ----------------------

    /** Propose "we made a deal" for the listing this chat is about. */
    public function proposeDeal(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (!$this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }
        $post = $conversation->servicePost;
        if (!$post) {
            return response()->json(['error' => 'Deals can only be confirmed in a chat about a listing'], 422);
        }
        $existing = Deal::where('conversation_id', $conversation->id)->where('service_post_id', $post->id)
            ->whereIn('status', ['pending', 'confirmed'])->first();
        if ($existing) {
            return response()->json(['deal' => $existing->toPublic()]);
        }
        $other = $conversation->otherUser($userId);
        $sellerId = (int) $post->user_id;
        $buyerId = $sellerId === (int) $userId ? (int) $other->id : (int) $userId;
        $deal = Deal::create([
            'conversation_id' => $conversation->id,
            'service_post_id' => $post->id,
            'seller_id' => $sellerId,
            'buyer_id' => $buyerId,
            'proposed_by' => $userId,
            'status' => 'pending',
        ]);
        $this->systemMessage($conversation, $userId, 'deal_proposed', $deal);

        return response()->json(['deal' => $deal->toPublic()], 201);
    }

    /** The other side confirms (or declines); the proposer can cancel while pending. */
    public function answerDeal(Request $request, Deal $deal): JsonResponse
    {
        $userId = Auth::id();
        $conversation = Conversation::find($deal->conversation_id);
        if (!$conversation || !$this->isParticipant($conversation, $userId) || $deal->status !== 'pending') {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        $action = $request->input('action');
        if ($action === 'cancel' && (int) $deal->proposed_by === (int) $userId) {
            $deal->update(['status' => 'cancelled']);
        } elseif (in_array($action, ['confirm', 'decline'], true) && (int) $deal->proposed_by !== (int) $userId) {
            $deal->update(['status' => $action === 'confirm' ? 'confirmed' : 'declined', 'confirmed_at' => $action === 'confirm' ? now() : null]);
        } else {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        $this->systemMessage($conversation, $userId, 'deal_' . $deal->status, $deal);

        return response()->json(['deal' => $deal->toPublic()]);
    }

    /**
     * Release C: POST /api/conversations/{conversation}/offer {amount}: the buyer offers a price for the listing.
     * Any earlier pending offer in this chat is withdrawn; the seller gets a push.
     */
    public function makeOffer(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();
        if (! $this->isParticipant($conversation, $userId)) {
            return response()->json(['error' => 'Not a participant in this conversation'], 403);
        }
        $post = $conversation->servicePost;
        if (! $post) {
            return response()->json(['error' => 'Offers are made in a chat about a listing'], 422);
        }
        if ((int) $post->user_id === (int) $userId) {
            return response()->json(['error' => 'The seller can counter an offer, not make one'], 422);
        }
        $d = $request->validate(['amount' => 'required|numeric|min:1|max:999999999']);
        \App\Models\Offer::where('conversation_id', $conversation->id)->where('status', 'pending')->update(['status' => 'withdrawn']);
        $offer = \App\Models\Offer::create([
            'conversation_id' => $conversation->id, 'service_post_id' => $post->id,
            'seller_id' => (int) $post->user_id, 'buyer_id' => (int) $userId, 'from_user_id' => (int) $userId,
            'amount' => $d['amount'], 'currency' => $post->price_currency_code, 'status' => 'pending',
        ]);
        $this->offerEvent($conversation, $userId, 'offer_made', $offer);

        return response()->json(['offer' => $offer->toPublic()], 201);
    }

    /** POST /api/offers/{offer}/answer {action: accept|decline|counter|withdraw, amount (counter)} */
    public function answerOffer(Request $request, \App\Models\Offer $offer): JsonResponse
    {
        $userId = (int) Auth::id();
        $conversation = Conversation::find($offer->conversation_id);
        if (! $conversation || ! $this->isParticipant($conversation, $userId) || $offer->status !== 'pending') {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        $d = $request->validate(['action' => 'required|in:accept,decline,counter,withdraw', 'amount' => 'required_if:action,counter|nullable|numeric|min:1']);
        $mine = (int) $offer->from_user_id === $userId;
        if (($d['action'] === 'withdraw') !== $mine) {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($d['action'] === 'counter') {
            $offer->update(['status' => 'countered']);
            $counter = \App\Models\Offer::create([
                'conversation_id' => $offer->conversation_id, 'service_post_id' => $offer->service_post_id,
                'seller_id' => $offer->seller_id, 'buyer_id' => $offer->buyer_id, 'from_user_id' => $userId,
                'amount' => $d['amount'], 'currency' => $offer->currency, 'status' => 'pending', 'parent_id' => $offer->id,
            ]);
            $this->offerEvent($conversation, $userId, 'offer_countered', $counter);

            return response()->json(['offer' => $counter->toPublic()]);
        }
        $offer->update(['status' => ['accept' => 'accepted', 'decline' => 'declined', 'withdraw' => 'withdrawn'][$d['action']]]);
        if ($offer->status === 'accepted') {
            // The seller agreed a price: the listing shows as reserved (the seller can change it any time).
            $post = \App\Models\ServicePost::find($offer->service_post_id);
            if ($post && $post->state === 'published' && ! $post->reserved_at) {
                $post->forceFill(['reserved_at' => now()])->save();
            }
        }
        $this->offerEvent($conversation, $userId, 'offer_'.$offer->status, $offer);

        return response()->json(['offer' => $offer->toPublic()]);
    }

    private function offerEvent(Conversation $conversation, int $userId, string $event, \App\Models\Offer $offer): void
    {
        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'type' => 'system',
            'body' => $event,
            'meta' => ['event' => $event, 'offer' => $offer->toPublic()],
        ]);
        $conversation->update(['last_message_body' => $event, 'last_message_sender_id' => $userId, 'last_message_at' => $message->created_at]);
        try {
            $other = $conversation->otherUser($userId);
            $sender = \App\Models\User::find($userId);
            $amount = rtrim(rtrim(number_format((float) $offer->amount, 2, '.', ','), '0'), '.').' '.($offer->currency ?? '');
            $other?->notify(new \App\Notifications\ChatMessageFcmNotification($conversation->id, $sender?->user_name ?? $sender?->name ?? 'Talabna',
                match ($event) {
                    'offer_made' => "Offer: {$amount}",
                    'offer_countered' => "Counter-offer: {$amount}",
                    'offer_accepted' => "Offer accepted: {$amount}",
                    'offer_declined' => 'Offer declined',
                    default => 'Offer withdrawn',
                }));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('offer push failed', ['offer' => $offer->id, 'error' => $e->getMessage()]);
        }
    }

    private function systemMessage(Conversation $conversation, int $userId, string $event, Deal $deal): void
    {
        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'type' => 'system',
            'body' => $event,
            'meta' => ['event' => $event, 'deal' => $deal->toPublic()],
        ]);
        $conversation->update(['last_message_body' => $event, 'last_message_sender_id' => $userId, 'last_message_at' => $message->created_at]);
    }

    private function localized($value): ?string
    {
        $v = is_string($value) ? (json_decode($value, true) ?? $value) : $value;
        if (!is_array($v)) return $v;
        return $v[app()->getLocale()] ?? $v['en'] ?? $v['ar'] ?? (reset($v) ?: null);
    }

    /** A listing's thumbnail: its first picture; a video only when it has nothing else. */
    private function cover($post): ?string
    {
        $media = $post->photos ?? collect();
        $isVideo = fn ($m) => $m->isVideo || preg_match('/\.(mp4|mov|m4v|webm|3gp)$/i', (string) $m->src);
        return optional($media->first(fn ($m) => !$isVideo($m)) ?? $media->first())->src;
    }

    /** Messages translated per request; the rest follow on the next load. */
    private const TRANSLATE_BUDGET = 8;

    /**
     * Present messages for [userId], adding the body in the reader's app language
     * when the other person wrote in a different one. Translations are cached on
     * the message, so each one is paid for once (from the reader's points bundle,
     * see ChatTranslationQuota).
     */
    private function withTranslations($messages, int $userId)
    {
        $myLang = ChatLanguageService::base(App::getLocale() ?: Auth::user()->locale);
        $budget = self::TRANSLATE_BUDGET;
        $translations = [];
        $todo = []; // message id => body, translated together in one call
        $paid = []; // already paid at send time (no refund on failure)

        foreach ($messages as $m) {
            $body = (string) $m->body;
            if ($m->sender_id === $userId || $m->deleted_at || $body === ''
                || !in_array($m->type ?? 'text', ['text', 'post'], true)) {
                continue;
            }
            // Messages from before translation existed: detect once.
            if ($m->lang === null && $budget > 0) {
                $local = ChatLanguageService::detectByScript($body);
                if ($local === null) $budget--;
                $m->lang = $local ?? $this->lang->detect($body) ?? '-';
                $m->saveQuietly();
            }
            if (!$m->lang || $m->lang === '-' || $m->lang === $myLang) continue;

            // Already translated for this reader: free. A new translation uses one
            // message of the reader's points bundle.
            if (($cached = $m->translationIn($myLang)) !== null) {
                $translations[$m->id] = $cached;
            } elseif ($m->translate_to === $myLang) {
                // Paid when sent and waiting for its batch. If the batch missed it
                // (lock race), translate it here without charging again.
                if ($m->created_at && $m->created_at->lt(now()->subSeconds(15)) && $budget > 0) {
                    $budget--;
                    $todo[$m->id] = $body;
                    $paid[$m->id] = true;
                }
            } elseif ($budget > 0) {
                if ($this->quota->take($userId)) {
                    $budget--;
                    $todo[$m->id] = $body;
                } else {
                    $this->translationNeedsPoints = true;
                }
            }
        }

        if ($todo) {
            $done = $this->lang->translateMany($todo, $myLang);
            foreach ($todo as $id => $_) {
                $m = $messages->firstWhere('id', $id);
                if (isset($done[$id])) {
                    $translations[$id] = $done[$id];
                    if ($m) {
                        $m->translate_to = null;
                        $m->rememberTranslation($myLang, $done[$id]);
                    }
                } elseif (empty($paid[$id])) {
                    $this->quota->refund($userId);
                }
            }
        }

        return $messages->map(fn (Message $m) => $m->toPublic($translations[$m->id] ?? null))->values();
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return in_array($userId, [$conversation->user_one_id, $conversation->user_two_id], true);
    }

    private function formatConversation(Conversation $conversation, int $userId): array
    {
        $other = $conversation->otherUser($userId);
        $unread = isset($conversation->unread_for_me) ? (int) $conversation->unread_for_me : Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
        $post = $conversation->servicePost;

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $other->id,
                'user_name' => $other->user_name,
                'name' => $other->name,
                'avatar' => optional($other->photos->first())->src,
                'verified' => (bool) $other->phone_verified_at,
                'last_seen_at' => optional($other->last_seen_at)->toIso8601String(),
                'deals' => ($other->show_deals ?? true) ? Deal::statsFor((int) $other->id) : null,
            ],
            'service_post' => $post ? [
                'id' => $post->id,
                'title' => $this->localized($post->title),
                'price' => $post->price,
                'currency' => $post->price_currency_code,
                'photo' => $post->relationLoaded('photos') ? $this->cover($post) : null,
                'owner_id' => $post->user_id,
            ] : null,
            'last_message_body' => $conversation->last_message_body,
            'last_message_at' => $conversation->last_message_at,
            'last_message_is_mine' => $conversation->last_message_sender_id === $userId,
            'unread_count' => $unread,
        ];
    }
}
