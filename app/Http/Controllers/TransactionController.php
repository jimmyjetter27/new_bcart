<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orderable;
use App\Models\Photo;
use App\Models\Transaction;
use App\Services\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

//    public function paystackCallback(Request $request)
//    {
//        $reference = $request->query('reference');
//        Log::info('Paystack callback received for reference: ' . $reference);
//
//        $paymentService = app(PayStackService::class);
//        $verificationResult = $paymentService->verifyPayment($reference);
//
//        if ($verificationResult['status'] && $verificationResult['data']['status'] === 'success') {
//            $transaction = Transaction::where('transaction_id', $reference)->first();
//
//            if ($transaction) {
//                $transaction->update([
//                    'status' => 'completed',
//                    'payment_method' => $verificationResult['data']['channel'],
//                ]);
//
//                // Update the associated order and assign photos to the user
//                $order = $transaction->order;
//                $order->update(['transaction_status' => 'completed']);
//
//                foreach ($order->orderables as $orderable) {
//                    Orderable::create([
//                        'order_id' => $order->id,
//                        'orderable_id' => $orderable->orderable_id,
//                        'orderable_type' => Photo::class,
//                    ]);
//                }
//
//                return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Successful');
//            }
//
//            Log::error("Transaction not found for reference: $reference");
//            return redirect()->to(env('FRONTEND_URL') . '/search?message=Transaction not found');
//        }
//
//        if ($verificationResult['status'] === false || $verificationResult['data']['status'] !== 'success') {
//            $transaction = Transaction::where('transaction_id', $reference)->first();
//
//            if ($transaction) {
//                $transaction->update(['status' => 'failed']);
//                $transaction->order->update(['transaction_status' => 'failed']);
//            }
//
//            return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Failed');
//        }
//
//        Log::error("Unexpected verification response for reference: $reference", $verificationResult);
//        return redirect()->to(env('FRONTEND_URL') . '/search?message=Unexpected response');
//    }

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');
        Log::info('Paystack callback received for reference: ' . $reference);

        $paymentService = app(PayStackService::class);
        $verificationResult = $paymentService->verifyPayment($reference);

        if ($verificationResult['status'] && $verificationResult['data']['status'] === 'success') {
            $transaction = Transaction::where('transaction_id', $reference)->first();

            if ($transaction) {
                // Update the transaction status
                $transaction->update([
                    'status' => 'completed',
                    'payment_method' => $verificationResult['data']['channel'],
                ]);

                // Update the order status
                $order = $transaction->order;
                $order->update(['transaction_status' => 'completed']);

                // Attach photos to the order
                $photoIds = Orderable::where('order_id', $order->id)
                    ->where('orderable_type', Photo::class)
                    ->pluck('orderable_id');

                $order->photos()->syncWithoutDetaching($photoIds);

                return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Successful');
            }

            Log::error("Transaction not found for reference: $reference");
            return redirect()->to(env('FRONTEND_URL') . '/search?message=Transaction not found');
        }

        if ($verificationResult['status'] === false || $verificationResult['data']['status'] !== 'success') {
            $transaction = Transaction::where('transaction_id', $reference)->first();

            if ($transaction) {
                $transaction->update(['status' => 'failed']);
                $transaction->order->update(['transaction_status' => 'failed']);
            }

            return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Failed');
        }

        Log::error("Unexpected verification response for reference: $reference", $verificationResult);
        return redirect()->to(env('FRONTEND_URL') . '/search?message=Unexpected response');
    }


}
