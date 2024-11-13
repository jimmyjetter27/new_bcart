<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

    public function paystackCallback(Request $request)
    {
        // Retrieve the reference from the request
        $reference = $request->query('reference');

        // Log the incoming callback for debugging
        Log::info('Paystack callback received for reference: ' . $reference);

        // Verify the transaction with Paystack
        $paymentService = app(PayStackService::class);
        $verificationResult = $paymentService->verifyPayment($reference);

        if ($verificationResult['status'] && $verificationResult['data']['status'] === 'success') {
            // Transaction is successful
            $transaction = Transaction::where('transaction_id', $reference)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
//                    'paid_at' => $verificationResult['data']['paid_at'], // Store payment date
//                    'gateway_response' => $verificationResult['data']['gateway_response'],
                    'payment_method' => $verificationResult['data']['channel'],
//                    'fees' => $verificationResult['data']['fees'],
//                    'receipt_number' => $verificationResult['data']['receipt_number'],
                ]);

                // Update the associated order
                $order = $transaction->order;
                $order->update(['transaction_status' => 'completed']);

                // Redirect to frontend with a success message
                return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Successful');
            }

            // If the transaction was not found, log an error
            Log::error("Transaction not found for reference: $reference");
            return redirect()->to(env('FRONTEND_URL') . '/search?message=Transaction not found');
        }

        // If the payment was not successful
        if ($verificationResult['status'] === false || $verificationResult['data']['status'] !== 'success') {
            // Update transaction and order to failed
            $transaction = Transaction::where('transaction_id', $reference)->first();

            if ($transaction) {
                $transaction->update(['status' => 'failed']);
                $transaction->order->update(['transaction_status' => 'failed']);
            }

            // Redirect to frontend with a failure message
            return redirect()->to(env('FRONTEND_URL') . '/search?message=Payment Failed');
        }

        // Log any other unexpected response
        Log::error("Unexpected verification response for reference: $reference", $verificationResult);
        return redirect()->to(env('FRONTEND_URL') . '/search?message=Unexpected response');
    }

}
