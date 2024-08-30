<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendVerificationCodeSMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    //
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $verificationCode = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        try {
            $user->notify(new SendVerificationCodeSMS($verificationCode));
            $user->verification_code = $verificationCode;
            $user->save();
        } catch (\Exception $e) {
            Log::error('Failed to send the  SMS: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send verification code'], 500);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function is_code_sent(string $phone)
    {
        // dd("okok");
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json(['message' => 'There isn\'t user with this phone number '], 400);
        }
        if (!is_null($user->verification_code) && $user->verification_code !== '') {
            return response()->json([
                'message' => 'Verification code has been sent',
                'verification_code' => $user->verification_code
            ], 200);
        }

        return response()->json([
            'message' => 'Verification code has not been sent'
        ], 404);
    }


    public function verify(Request $request)
    {
        $user = User::where('phone', $request->phone)
            ->where('verification_code', $request->verification_code)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Verification code or phone number is incorrect'], 400);
        }

        $user->is_verified = true;
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'User verified successfully']);
    }
}
