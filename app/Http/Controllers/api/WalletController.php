<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function Deposit(request $request)
    {

        $validate = $request->validate([
            'amount' => 'required',
            'type' => 'required',
            'paymentnumber' => 'required|min:11|max:11',
            'trxid' => 'required|max:12',

        ]);

        $user = $request->user();
        $amount = $request->input('amount');
        $type = $request->input('type');
        $paymentNumber = $request->input('paymentnumber');
        $trxId = $request->input('trxid');

        $paymentCheck = DB::table('getway')->where('trxid', $trxId)->first();

        if($paymentCheck){
            if($paymentCheck->status == 'used'){
                return response()->json([
                    'status' => false,
                    'message' => 'Trx id already exist'
                ]);
            }
        }

        $paymentCheck = DB::table('getway')->where('trxid', $trxId)->where('status', '=', 'unused')->first();
        if ($paymentCheck) {
           $walletUser = DB::table('wallet')->where('userId', '=', $user->id)->first();

           if($walletUser){
               DB::table('wallet')->where('userId', '=', $user->id)->increment('balance', $paymentCheck->amount);
           }else{
               DB::table('wallet')->insert([
                   'userId' => $user->id,
                   'balance' => $paymentCheck->amount,
                   'message' => $trxId,
               ]);
           }

            DB::table('wallet_history')->insert([
                'userId' => $user->id,
                'message' => "+$paymentCheck->amount ৳ added in wallet",
                'orderID' => 0
            ]);
            DB::table('getway')->where('trxid', $trxId)->update([
                'status' => 'used'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Success'
            ]);
        }else{
            $msg = $amount . '৳ Wallet';
            $orderId = DB::table('myorders')->insertGetId([
                'userdata' => 'wallet',
                'item_id' => 0,
                'status' => 'processing',
                'username' => $user->username,
                'number' => $user->phonenumber,
                'user_id' => $user->id,
                'bkash_number' => $paymentNumber,
                'trxid' => $trxId,
                'itemtitle' => $msg,
                'total' => $amount,
                'token' => $request->get('token'),
                'type' => 'Top Up BD'
            ]);

            $orderData = DB::table('myorders')->find($orderId);

            return response()->json([
                'status' => true,
                'message' => 'Product ordered successfully',
                'data' => $orderData
            ]);

        }


    }
}
