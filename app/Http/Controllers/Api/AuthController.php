<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $fields = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]
        );
        $fields['password'] = bcrypt($fields['password']);
        $user = User::create($fields);
        $token = $user->createToken(name: $fields['name'], expiresAt: new DateTime('+1 week') )->plainTextToken;
        return response()->json(['success' => true, 'token' => $token], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user) return response()->json(['status' => false, 'message' => 'User not found']);
        if(!Auth::attempt($fields)){
            return response()->json(['status' => false, 'message' => 'Invalid password']);
        }
        return response()->json(['status' => true, 'token' => $user->createToken(name: 'token', expiresAt: new DateTime('+1 week'))->plainTextToken]);
    }
}
