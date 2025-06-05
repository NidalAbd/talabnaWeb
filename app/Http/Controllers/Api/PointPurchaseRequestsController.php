<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\point_purchase_requests;
use Illuminate\Http\Request;
use Stripe\Stripe;

class PointPurchaseRequestsController extends Controller
{

    public function index($userId)
    {
        $purchaseRequests = point_purchase_requests::where('user_id', $userId)
            ->latest('created_at')->take(1)->get();
        return response()->json($purchaseRequests);
    }

    // Add this to your PointPurchaseRequestsController or create a new controller

    public function getTransferHistory($userId)
    {
        // Get transfer transactions for this user (sent or received)
        $transfers = \App\Models\point_transactions::where(function($query) use ($userId) {
            $query->where('from_user_id', $userId)
                ->orWhere('to_user_id', $userId);
        })
            ->where('type', 'transfer')
            ->latest('created_at')
            ->get();

        // Format each transfer to match your PurchaseRequest format
        $formattedTransfers = $transfers->map(function($transfer) use ($userId) {
            $isIncoming = $transfer->to_user_id == $userId;

            return [
                'id' => $transfer->id,
                'user_id' => $userId,
                'points_requested' => $transfer->point,
                'price_per_point' => "0",
                'total_price' => "0",
                'status' => 'approved', // Transfers are always approved
                'created_at' => $transfer->created_at,
                'updated_at' => $transfer->updated_at,
                // Add these fields to help with display but they won't affect your model
                'is_transfer' => true,
                'transfer_direction' => $isIncoming ? 'in' : 'out',
                'other_user_id' => $isIncoming ? $transfer->from_user_id : $transfer->to_user_id
            ];
        });

        return response()->json($formattedTransfers);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'points_requested' => 'required|integer|min:1',
        ]);

        $price_per_point = 7.5;
        $total_price = $price_per_point * $request->points_requested;

        $pointPurchaseRequest = point_purchase_requests::create([
            'user_id' => auth()->id(),
            'points_requested' => $request->points_requested,
            'price_per_point' => $price_per_point,
            'total_price' => $total_price,
            'status' => 'pending',
        ]);

        // Create Stripe payment intent
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $total_price * 100,
            'currency' => 'usd',
            'metadata' => [
                'purchase_request_id' => $pointPurchaseRequest->id,
            ],
        ]);

        // Save the client secret in the database
        $pointPurchaseRequest->client_secret = $paymentIntent->client_secret;
        $pointPurchaseRequest->save();

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'message' => 'Your point purchase request has been submitted.',
            'data' => $pointPurchaseRequest,
        ]);
    }


    public function destroy(point_purchase_requests $point_purchase_requests)
    {
        $purchaseRequest = point_purchase_requests::findOrFail($point_purchase_requests);
        $purchaseRequest->delete();

        return redirect()->route('purchase_requests.index')->with('success', 'Purchase request deleted successfully!');

    }
}
