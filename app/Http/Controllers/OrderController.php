<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Orderable;
use App\Models\Photo;
use App\Models\Transaction;
use App\Services\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        if ($photos->isEmpty() || $photos->contains(fn($photo) => is_null($photo->price))) {
            return response()->json([
                'success' => false,
                'message' => 'Please ensure all selected photos have prices set.',
            ], 400);
        }

        // Filter out photos already purchased by the user
        $alreadyPurchasedPhotoIds = Orderable::where('orderable_type', Photo::class)
            ->whereIn('orderable_id', $photoIds)
            ->whereHas('order', fn($query) => $query->where('customer_id', $user->id))
            ->pluck('orderable_id')
            ->toArray();

        $newPhotos = $photos->filter(fn($photo) => !in_array($photo->id, $alreadyPurchasedPhotoIds));

        if ($newPhotos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You have already purchased all selected photos.',
            ], 400);
        }

        // Calculate total price for new photos only
        $totalPrice = $newPhotos->sum('price');

        $paymentInfo = $user->paymentInfo;
        if (!$paymentInfo || !$paymentInfo->preferred_payment_account) {
            return response()->json([
                'success' => false,
                'message' => 'Please set up your preferred payment method before purchasing.',
            ], 400);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'customer_id' => $user->id,
                'order_number' => Str::uuid(),
                'total_price' => $totalPrice,
                'transaction_status' => 'pending'
            ]);

            // Attach new photos to the order through the orderables table
            foreach ($newPhotos as $photo) {
                \App\Models\Orderable::create([
                    'order_id' => $order->id,
                    'orderable_id' => $photo->id,
                    'orderable_type' => Photo::class,
                ]);
            }

            // Initialize payment with Paystack
            $transactionResult = $paymentService->initializePayment([
                'email' => env('PAYSTACK_USER_EMAIL'),
                'amount' => $totalPrice,
                'currency' => 'GHS'
            ]);

            // Save transaction
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionResult['data']['reference'],
                'payment_method' => $paymentInfo->preferred_payment_account,
                'amount' => $totalPrice,
                'status' => 'pending',  // Initial status is pending
                'transaction_date' => now(),
            ]);

            DB::commit();

            // Pass authorization URL to the frontend
            return response()->json([
                'success' => true,
                'message' => 'Payment initialization successful. Redirecting to payment page...',
                'data' => [
                    'authorization_url' => $transactionResult['data']['authorization_url'],
                    'order' => $order
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Transaction Failed Err: '. $e->getMessage());
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed. Please try again later.',
            ], 500);
        }
    }

}
