<?php

namespace App\Services\Chat;

use App\Models\palservice_points;
use App\Models\point_transactions;
use Illuminate\Support\Facades\DB;

/**
 * Chat translation is paid by the reader with points. The first translation
 * takes 1 point and opens a bundle of MESSAGES_PER_POINT translated messages;
 * when the bundle is used up the next translation takes another point. With
 * no points left, messages stay in their original language.
 *
 * Pricing (2026-10-05): a point sells for $0.80–0.99 (≈$0.56–0.85 after store
 * fees); one gpt-4o-mini translation costs ≈$0.0001, so 100 messages cost us
 * ≈$0.01 per point sold.
 */
class ChatTranslationQuota
{
    public const MESSAGES_PER_POINT = 100;

    /** Use one translated message from [userId]'s bundle, buying a new bundle with 1 point if needed. */
    public function take(int $userId): bool
    {
        return DB::transaction(function () use ($userId) {
            DB::table('chat_translation_quotas')->insertOrIgnore([
                'user_id' => $userId, 'remaining' => 0, 'points_spent' => 0, 'translated' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $row = DB::table('chat_translation_quotas')->where('user_id', $userId)->lockForUpdate()->first();

            if ($row->remaining > 0) {
                DB::table('chat_translation_quotas')->where('user_id', $userId)->update([
                    'remaining' => $row->remaining - 1, 'translated' => $row->translated + 1, 'updated_at' => now(),
                ]);
                return true;
            }

            $wallet = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || (int) $wallet->point < 1) return false;

            $wallet->point = (int) $wallet->point - 1;
            $wallet->save();
            point_transactions::create([
                'from_user_id' => $userId,
                'to_user_id' => null,
                'type' => 'used',
                'point' => 1,
                'status' => 'completed',
                'metadata' => json_encode(['reason' => 'chat_translation', 'messages' => self::MESSAGES_PER_POINT]),
            ]);
            DB::table('chat_translation_quotas')->where('user_id', $userId)->update([
                'remaining' => self::MESSAGES_PER_POINT - 1,
                'points_spent' => $row->points_spent + 1,
                'translated' => $row->translated + 1,
                'updated_at' => now(),
            ]);
            return true;
        });
    }

    /** Give back a message that was paid for but could not be translated. */
    public function refund(int $userId): void
    {
        DB::table('chat_translation_quotas')->where('user_id', $userId)->update([
            'remaining' => DB::raw('remaining + 1'),
            'translated' => DB::raw('GREATEST(translated - 1, 0)'),
        ]);
    }

    public function remaining(int $userId): int
    {
        return (int) DB::table('chat_translation_quotas')->where('user_id', $userId)->value('remaining');
    }
}
