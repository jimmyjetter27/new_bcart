<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Transaction;
use App\Services\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }

//    public function buyPhotos(Request $request)
//    {
//        $user = Auth::user();
//
//        // Validate the request
//        $request->validate([
//            'photo_ids' => 'required|array|min=1',  // Ensure at least one photo is selected
//            'photo_ids.*' => 'exists:photos,id',    // Ensure each photo exists
//            'payment_information_id' => 'required|exists:payment_information,id',
//            'payment_method' => 'required|string',
//        ]);
//
//        $totalPrice = 0;
//        $photos = Photo::whereIn('id', $request->photo_ids)->get();
//        $orders = [];
//        $transactions = [];
//
//        foreach ($photos as $photo) {
//            // Check if the user has already purchased the photo
//            if ($photo->hasPurchasedPhoto($user->id)) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'You have already purchased one of the selected photos.'
//                ], 400);
//            }
//
//            // Accumulate total price
//            $totalPrice += $photo->price;
//
//            // Create an order for each photo
//            $order = Order::create([
//                'customer_id' => $user->id,
//                'orderable_type' => Photo::class,
//                'orderable_id' => $photo->id,
//                'order_number' => Str::uuid(),
//                'total_price' => $photo->price,
//                'transaction_status' => 'pending',
//            ]);
//
//            // Create a transaction for each order
//            $transaction = Transaction::create([
//                'order_id' => $order->id,
//                'payment_information_id' => $request->payment_information_id,
//                'transaction_id' => Str::uuid(),
//                'payment_method' => $request->payment_method,
//                'amount' => $photo->price,
//                'status' => 'pending',
//                'transaction_date' => now(),
//            ]);
//
//            // Add to response collection
//            $orders[] = $order;
//            $transactions[] = $transaction;
//        }
//
//        // After creating all orders, you can initiate a single payment process for the total price.
//        // Process the payment (e.g., using a payment gateway, Mobile Money, etc.)
//
//        // For now, assume the transaction is successful and mark orders as completed
//        foreach ($orders as $order) {
//            $order->update(['transaction_status' => 'completed']);
//        }
//
//        foreach ($transactions as $transaction) {
//            $transaction->update(['status' => 'completed']);
//        }
//
//        return response()->json([
//            'success' => true,
//            'message' => 'Photos purchased successfully.',
//            'data' => [
//                'orders' => $orders,
//                'transactions' => $transactions,
//            ],
//            'total_price' => $totalPrice
//        ]);
//    }

    public function buyPhotos(Request $request, PayStackService $paymentService)
    {
        $user = Auth::user();
        $photoIds = $request->input('photo_ids');

        // Fetch selected photos
        $photos = Photo::whereIn('id', $photoIds)->get();

        // Check if the pricing table is populated
        if ($photos->isEmpty() || $photos->contains(fn($photo) => is_null($photo->price))) {
            return response()->json([
                'success' => false,
                'message' => 'Please ensure all selected photos have prices set.',
            ], 400);
        }

        // Calculate total price
        $totalPrice = $photos->sum('price');

        // Fetch the user's preferred payment method
        $paymentInfo = $user->paymentInfo;
        if (!$paymentInfo || !$paymentInfo->preferred_payment_account) {
            return response()->json([
                'success' => false,
                'message' => 'Please set up your preferred payment method before purchasing.',
            ], 400);
        }

        $preferredPaymentMethod = $paymentInfo->preferred_payment_account;

        // Create Order
        $order = Order::create([
            'customer_id' => $user->id,
            'order_number' => Str::uuid(),
            'total_price' => $totalPrice,
            'transaction_status' => 'pending'
        ]);

        // Attach photos to the order (through polymorphic relation)
        foreach ($photos as $photo) {
            $order->orderable()->associate($photo);
        }

        // Process the payment using Paystack (using preferred payment method)
        if ($preferredPaymentMethod === 'bank_account') {
            $transactionResult = $paymentService->chargeWithCard();
        } else {
            $transactionResult = $paymentService->chargeWithMobileMoney();
        }

        // Create transaction record
        $transaction = Transaction::create([
            'order_id' => $order->id,
            'transaction_id' => $transactionResult['transaction_id'],
            'payment_method' => $preferredPaymentMethod,
            'amount' => $totalPrice,
            'status' => $transactionResult['status'],
            'transaction_date' => now(),
        ]);

        // Update order status based on the transaction result
        $order->update([
            'transaction_status' => $transaction->status === 'completed' ? 'completed' : 'failed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Photos purchased successfully.',
            'order' => $order
        ]);
    }


}
