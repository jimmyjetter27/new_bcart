<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PayStackService;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    public $paystack;
    public function __construct(PayStackService $payStackService)
    {
        $this->paystack = $payStackService;
    }

    public function listBanks()
    {
        // Fetch all banks
        $banks = $this->paystack->listBanks();

        // Filter out records with code 'ATL', 'MTN', or 'VOD' and keep only numeric codes and currency is GHS
        $filteredBanks = array_filter($banks['data'], function ($bank) {
            return is_numeric($bank['code']) && $bank['currency'] != 'USD';
        });

        $cleanedBanks = array_map(function ($bank) {
            unset(
                $bank['id'],
                $bank['createdAt'],
                $bank['updatedAt'],
                $bank['slug'],
                $bank['longcode'],
                $bank['gateway'],
                $bank['is_deleted'],
            );
            return $bank;
        }, $filteredBanks);

        // Return the filtered list of banks
        return response()->json([
            'status' => true,
            'message' => 'Banks retrieved',
            'data' => array_values($cleanedBanks),
        ]);
    }

}
