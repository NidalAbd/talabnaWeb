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
