<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $dashboardData = [
            'totalPendingOrder' => DB::table('myorders')
                ->whereIn('status', ['processing', 'Auto Topup', 'Payment Verified', 'Auto Failed'])
                ->count(),
            'totalOrder' => DB::table('myorders')->count(),

            'totalUsers' => DB::table('account')->count(),

            'totalProducts' => DB::table('catagory')->count(),

            'totalOrders' => DB::table('myorders')->count(),

            'yesterdayOrders' =>  DB::table('myorders')
                ->whereIn('status', ['Auto Completed','Done', 'Complete'])
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->count(),

            'yesterdayTotalSell' =>  DB::table('myorders')
                ->whereIn('status', ['Auto Completed','Done', 'Complete'])
                ->whereDate('created_at', now()->subDay()->toDateString())
                ->sum('total'),


        'todayTotalOrder' => DB::table('myorders')
                ->whereDate('created_at', now()->toDateString())->whereIn('status', ['Auto Completed','Done', 'Complete'])
                ->count(),

            'todayTotalSell' => DB::table('myorders')
                ->whereDate('created_at', now()->toDateString())
                ->whereIn('status', ['Auto Completed','Done', 'Complete'])->sum('total'),

            'totalWalletBalance' => DB::table('wallet')->sum('balance'),

            'totalStockCodes' => DB::table('uc_codes')
                ->where('used', 0)
                ->count(),
        ];


        // Return the response in JSON format with success status
        return response()->json([
            'status' => 'success',
            'data' => $dashboardData,
        ], 200);
    }
}
