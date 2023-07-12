<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function register (Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password'
            ]);
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            return $this->sendResponse('User register successfully', $user);
        } catch (Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    public function login (Request $request) {
        try {
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            if (auth()->attempt($credentials)) {
                $user = Auth::user();
                // $user['token'] = auth()->user()->createToken('parking_token')->accessToken;
                $res['token'] = $user->createToken('parking_token')->accessToken;
                $res['user'] = (new UserController)->index($user->id);
                return $this->sendResponse("User login successfully", $res);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    public function logout () {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
