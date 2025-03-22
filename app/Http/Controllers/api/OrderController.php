<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function AddProductOrder(Request $request): JsonResponse
    {
        try {

            $validatedData = $request->validate([
                'product_id' => 'required|exists:products,id',
                'playerId' => 'required',
                'token' => 'nullable',
            ]);

            $user = $request->user();
            $id = $user->id;

            $balance = DB::table('wallet')->where('userId', $id)->value('balance');

            $product = DB::table('products')->find($validatedData['product_id']);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if ($balance < $product->price) {
                return response()->json([
                    'status' => false,
                    'message' => 'You don\'t have enough balance'
                ], 400);
            }

            // Deduct balance from user's wallet
            DB::table('wallet')->where('userId', $id)->decrement('balance', $product->price);

            $orderId = DB::table('myorders')->insertGetId([
                'userdata' => $validatedData['playerId'],
                'item_id' => $validatedData['product_id'],
                'status' => 'Auto Topup',
                'username' => $user->username,
                'number' => $user->phonenumber,
                'user_id' => $id,
                'bkash_number' => 'wallet',
                'trxid' => 'wallet',
                'itemtitle' => $product->name,
                'total' => $product->price,
                'token' => $request->input('token'),
                'datetime' => now(),
                'type' => 'Top Up BD'
            ]);

            DB::table('wallet_history')->insert([
                'userId' => $id,
                'message' => "-$product->price",
                'orderID' => $orderId
            ]);


            $orderData = DB::table('myorders')->find($orderId);

            return response()->json([
                'status' => true,
                'message' => 'Product ordered successfully',
                'data' => $orderData
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your order'
            ], 500);
        }
    }

    public function GetMyOrders(Request $request): JsonResponse
    {
        // Validate the incoming request
        $userId = $request->user_id;

        try {
            // Fetch the orders with pagination
            $orders = DB::table('myorders')->select([
                'id',
                'status',
                'username',
                'number',
                'user_id',
                'bkash_number',
                'trxid',
                'userdata',
                'item_id',
                'itemtitle',
                'total',
                'datetime'
            ])->where('user_id', $userId)->orderBy('id', 'desc')->paginate(10);

            return response()->json([
                'status' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (\Exception $e) {
            // Handle exception and return error response
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching the orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
