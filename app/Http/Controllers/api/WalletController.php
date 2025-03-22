<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class WalletController extends Controller
{
    public function Deposit(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'amount' => 'required',
            'product' => 'required',
            'type' => 'required',
            'paymentnumber' => 'required|min:11|max:11',
            'trxid' => 'required|max:12',
        ]);

        $user = $request->user();
        $trxId = $request->input('trxid');
        $paymentNumber = $request->input('paymentnumber');
        $amount = $request->input('amount');
        $product = $request->input('product');
        $status = 'processing';  // Default status
        // Check if the trxId already exists in orders or gateway (used trxId)
        $trxIdExists = DB::table('myorders')->where('trxid', $trxId)->exists();
        $paymentCheck = DB::table('getway')->where('trxid', $trxId)->first();

        if ($trxIdExists || ($paymentCheck && $paymentCheck->status === 'used')) {
            return response()->json([
                'status' => false,
                'message' => 'Trx ID already exists',
            ]);
        }

        // Wrap the operations in a transaction for atomicity
        try {
            DB::transaction(function () use ($user, $trxId, $paymentNumber, $amount, $product, $paymentCheck, &$status, &$orderData, $request) {

                // If payment is found and unused, update the wallet balance and set the trxId as used
                if ($paymentCheck && $paymentCheck->status === 'unused') {

                    $amount = $paymentCheck->amount;

                    DB::table('wallet')->updateOrInsert(
                        ['userId' => $user->id],
                        ['balance' => DB::raw("balance + {$paymentCheck->amount}")]
                    );

                    // Mark trxId as used in gateway table
                    DB::table('getway')->where('trxid', $trxId)->update(['status' => 'used']);

                    // Set status to auto-completed
                    $status = 'Auto Completed';
                }

                // Create the order in the `myorders` table
                $orderId = DB::table('myorders')->insertGetId([
                    'userdata' => 'wallet',
                    'item_id' => 0,
                    'status' => $status,
                    'username' => $user->username,
                    'number' => $user->phonenumber,
                    'user_id' => $user->id,
                    'bkash_number' => $paymentNumber,
                    'trxid' => $trxId,
                    'datetime' => now(),
                    'itemtitle' => $product,
                    'total' => $amount,
                    'token' => $request->input('token'),
                    'type' => 'Top Up BD',
                ]);

                if($status == 'Auto Completed'){
                    // Log wallet transaction
                    DB::table('wallet_history')->insert([
                        'userId' => $user->id,
                        'message' => "+$amount ৳",
                        'orderID' => $orderId,
                    ]);
                }



                // Fetch the newly created order
                $orderData = DB::table('myorders')->find($orderId);
            });

            // Return success response after transaction
            return response()->json([
                'status' => true,
                'message' => 'Product ordered successfully',
                'data' => $orderData,
            ]);

        } catch (\Exception $e) {
            // Handle transaction failure
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing the transaction',
            ], 500);
        }
    }


    public function WalletHistory(Request $request) :JsonResponse
    {
        $user = $request->user();
        $wallet = DB::table('wallet_history')->where('userId', $user->id)->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $wallet,
        ]);

    }


}
