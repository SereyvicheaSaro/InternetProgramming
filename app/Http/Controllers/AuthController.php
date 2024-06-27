<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    // Register user
    public function register(Request $request)
    {
        try {
            $registerUserData = $request->validate([
                'name'          => 'required|string',
                'email'         => 'required|string|email|unique:users',
                'password'      => 'required|min:8',
                'c_password'    => 'required|same:password',
                'role_id'       => 'required|integer'
            ]);

            $user = User::create([
                'name'      => $registerUserData['name'],
                'role_id'   => $registerUserData['role_id'],
                'email'     => $registerUserData['email'],
                'password'  => Hash::make($registerUserData['password']),
            ]);

            return response()->json([
                'message' => 'User Created',
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            // Validation error
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Unexpected error
            return response()->json([
                'message' => 'Server Error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'     => 'required|string|email',
                'password'  => 'required|min:8'
            ]);
            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized',
                ], Response::HTTP_UNAUTHORIZED);
            }
            $user = Auth::guard('api')->user();
            return response()->json([
                'message'       => 'Success',
                'user'          => $user,
                'access_token'  => $token,
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            // Validation error
            return response()->json([
                'message'   => 'Validation Error',
                'errors'    => $e->errors(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Unexpected error
            return response()->json([
                'message' => 'Server Error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function me()
    {
        // Here we just get information about current user
        try {
            return response()->json(auth()->user());
        } catch (\Exception $e) {
            // Unexpected error
            return response()->json([
                'message' => 'Server Error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            //Unexpected error
            return response()->json([
                'message' => 'Server Error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refresh()
    {
        // When access token will be expired, we are going to generate a new one wit this function 
        // and return it here in response
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        // This function is used to make JSON response with new
        // access token of current user
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 180
        ]);
    }
}