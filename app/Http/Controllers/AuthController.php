<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator as Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    public function register(Request $request)
    {
        if (!Tenant::getTenant()) {
            return response()->json(['error' => 'Not Found']);
        }
        $request->tenant_id = Tenant::getTenant()->id;
        $email = $request->email;
        $tenant_id = $request->tenant_id;
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users')->where(function ($query) use ($email, $tenant_id) {
                    return $query->where('email', $email)
                        ->where('tenant_id', $tenant_id);
                }),
            ],
            'name' => 'required',
            // 'email' => 'required|string|email|unique:users|' . $request->tenant_id,
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            [
                'tenant_id' => $tenant_id,
                'password' => bcrypt($request->password)
            ]
        ));

        return response()->json([
            'message' => 'registered Successfully',
            'user' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        if (!Tenant::getTenant()) {
            return response()->json(['error' => 'Not Found']);
        }
        $tenant_id = Tenant::getTenant()->id;
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth()->attempt(array_merge($validator->validate(), [
            'tenant_id' => $tenant_id
        ]))) {
            return response()->json(['error' => 'Unauthorized'], 402);
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json(
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expire_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ],
            200
        );
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'logout Successfully'
        ]);
    }
}
