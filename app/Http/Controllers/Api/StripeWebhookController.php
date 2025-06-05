<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\point_purchase_notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\point_purchase_requests;
use App\Models\User;
use App\Models\palservice_points;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;

            default:
                Log::info('Received unknown event type ' . $event->type);
                return response()->json(['status' => 'unhandled event type'], 200);
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $purchaseRequest = point_purchase_requests::where('client_secret', $paymentIntent->client_secret)->first();

        if ($purchaseRequest) {
            DB::beginTransaction();
            try {
                // Approve the purchase request
                $purchaseRequest->status = 'approved';
                $purchaseRequest->save();

                // Add or deduct points from user's balance
                $user = User::find($purchaseRequest->user_id);
                $points = palservice_points::firstOrCreate(
                    ['user_id' => $user->id],
                    ['point' => 0]
                );

                $points->point += $purchaseRequest->points_requested;
                $points->save();

                // Notify the user about the successful purchase
                $this->notifyUser($user, 'Your point purchase was successful.', 'success', $purchaseRequest->points_requested);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error approving purchase request: ' . $e->getMessage());
                $this->notifyUser($user, 'There was an error processing your point purchase.', 'error');
            }
        } else {
            Log::error('Purchase request not found for client secret: ' . $paymentIntent->client_secret);
        }
    }

    protected function handlePaymentIntentCanceled($paymentIntent)
    {
        Log::info('Payment canceled for client secret: ' . $paymentIntent->client_secret);

        $purchaseRequest = point_purchase_requests::where('client_secret', $paymentIntent->client_secret)->first();

        if ($purchaseRequest) {
            $purchaseRequest->status = 'canceled';
            $purchaseRequest->save();

            $user = User::find($purchaseRequest->user_id);
            $this->notifyUser($user, 'Your point purchase was canceled.', 'error');
        }
    }

    protected function handlePaymentIntentFailed($paymentIntent)
    {
        Log::error('Payment failed for client secret: ' . $paymentIntent->client_secret);

        $purchaseRequest = point_purchase_requests::where('client_secret', $paymentIntent->client_secret)->first();

        if ($purchaseRequest) {
            $purchaseRequest->status = 'failed';
            $purchaseRequest->save();

            $user = User::find($purchaseRequest->user_id);
            $this->notifyUser($user, 'Your point purchase failed. Please try again.', 'error');
        }
    }

    protected function handleChargeRefunded($charge)
    {
        Log::info('Charge refunded for payment intent: ' . $charge->payment_intent);

        $purchaseRequest = point_purchase_requests::where('client_secret', $charge->payment_intent)->first();

        if ($purchaseRequest) {
            $purchaseRequest->status = 'refunded';
            $purchaseRequest->save();

            $user = User::find($purchaseRequest->user_id);
            $points = palservice_points::where('user_id', $user->id)->first();
            if ($points) {
                $points->point -= $purchaseRequest->points_requested;
                $points->save();
            }

            $this->notifyUser($user, 'Your point purchase was refunded.', 'info');
        }
    }

    protected function notifyUser($user, $message, $status, $points_requested = null)
    {
        if (empty($user->fcm_token)) {
            Log::error('User device token is not registered for user ID: ' . $user->id);
            return;
        }

        // Create and save the notification
        $notification = new Notification([
            'message' => $message,
            'user_id' => $user->id,
        ]);
        $user->notifications()->save($notification);

        // Send point purchase notification
        $subject = 'Your point purchase confirmation';
        $notification = new point_purchase_notifications($points_requested, $subject, $user->fcm_token);
        $user->notify($notification);
    }
}



