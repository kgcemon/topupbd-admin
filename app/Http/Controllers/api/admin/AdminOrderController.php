<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use App\services\NotificationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(){
        $orders = DB::table('myorders')
            ->whereIn('status', ['processing', 'Auto Topup', 'Payment Verified'])
            ->get();
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function updateOrder(Request $request):JsonResponse{
        $validated = $request->validate([
            'id' => 'required',
            'status' => 'required|integer',
        ]);
        $id = $request->input('id');
        $status = $request->input('status');
        if($status == 1){
            $token = DB::table('myorders')->where('id', $id)->select('token')->first();
            $updateOrder =  DB::table('myorders')->where('id', $id)->update(['status' => 'পেমেন্ট না করায় ডিলেট করা হয়েছে']);
            if($updateOrder){
                try {
                    NotificationServices::sendNotification($token,"$id পেমেন্ট না করায় ডিলেট করা হয়েছে");
                }catch (\Exception $exception){

                }
                return response()->json([
                    'success' => true,
                    'message' => 'Order has been cancelled'
                ]);
            }
        }elseif ($status == 2) {
            $updateOrder =  DB::table('myorders')->where('id', $id)->update(['status' => 'Complete']);
            $token = DB::table('myorders')->where('id', $id)->select('token')->first();
            if($updateOrder){
                try {
                    NotificationServices::sendNotification($token,"$id Number Order has been Complete");
                }catch (\Exception $exception){

                }
                return response()->json([
                    'success' => true,
                    'message' => "$id Number Order has been Complete"
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'order update failed'
        ]);

    }
}
