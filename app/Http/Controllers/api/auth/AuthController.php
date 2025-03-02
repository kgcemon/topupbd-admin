<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    function Login (Request $request):JsonResponse
    {
        $validate = $request->validate([
            'phone' => 'required|string|min:11|max:11',
            'password' => 'required|string|min:3'
        ]);

        $phoneNumber = $validate['phone'];
        $password = $validate['password'];

        // Find the user by email
        $user = User::where('phonenumber', $phoneNumber)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($password === $user->password) {
            $token = $user->createToken('MyAppToken')->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

    }

    public function loginWithGoogleToken(Request $request): JsonResponse
    {
        $idToken = $request->input('token');
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        try {
            $payload = $client->verifyIdToken($idToken);
            if ($payload) {

                $googleId = $payload['sub'];
                $name = $payload['name'];
                $email = $payload['email'];
                $avatar = $payload['picture'];

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'google_id'=> $googleId,
                        'username' => $name,
                        'email' => $email,
                        'img' => $avatar,
                    ]
                );

                $token = $user->createToken('API Token')->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user,
                ]);
            } else {
                // Invalid token
                return response()->json(['error' => 'Invalid token'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Token verification failed: ' . $e->getMessage()], 400);
        }
    }


//    public function register(Request $request): JsonResponse{
//        $validate = $request->validate([
//            'username' => 'required|string|min:3',
//            'phone' => 'required|string|min:11|max:11|unique:users',
//            'password' => 'required|string|min:3'
//        ]);
//
//    }


 public function profile(Request $request): JsonResponse
 {
     try {
         $user = $request->user();

         $walletBalance = DB::table('wallet')->where('userId', $user->id)->select('balance')->first();

         return response()->json([
             'success' => true,
             'balance' => $walletBalance->balance ?? 0,
             'data' =>  $user
         ]);
     }catch (\Exception $e) {
         return response()->json([
             'success' => false,
             'message' => $e->getMessage(),
         ]);
     }
 }



}
