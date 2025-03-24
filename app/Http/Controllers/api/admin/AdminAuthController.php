<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function adminLogin(Request $request): JsonResponse{
        $validate = $request->validate([
            'email'=>'required|email|exists:admin,email',
            'password'=>'required'
        ]);

        $admin =  Admin::where('email', $validate['email'])->firstOrFail();

        if($admin == null){
            return response()->json([
                'success'=>false,
                'message' => 'Invalid email or password'
            ]);
        }

        if (!Hash::check($validate['password'], $admin->password) ) {
            return response()->json([
                'status'=>false,
                'message'=>'Invalid email or password'
            ]);
        }

        $token = $admin->createToken('MyApp')->plainTextToken;
        return response()->json([
            'status'=>true,
            'message'=> 'Login Successful',
            'token'=>$token,
            'user'=>$admin
        ]);

    }
}
