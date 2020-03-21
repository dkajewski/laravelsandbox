<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Group;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register user. Using transactions to insert also user group.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ];

        DB::beginTransaction();
        try {
            $newUserId = DB::table('users')->insertGetId($user);
            DB::table('group_user')->insert([
                'user_id' => $newUserId,
                'group_id' => Group::$groups['User']
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }

        return response()->json([
            'success' => true,
            'message' => 'basic.user-created'
        ], 201);
    }

    /**
     * Login and creating token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)) {
            return response()->json([
                'original' => [
                    'errors' => [
                        'login' => ['basic.login-failed']
                    ]
                ]
            ], 200);
        }

        $user = $request->user();
        if ($this->isUserBanned($user->banished_until)) {
            return response()->json([
                'original' => [
                    'errors' => [
                        'login' => ['basic.user-banned-until']
                    ],
                    'data' => [
                        'login' => [$user->banished_until]
                    ]
                ]
            ], 200);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'success' => true,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Logout function
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Get current user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }


    private function isUserBanned($date)
    {
        if ($date > date('Y-m-d')) {
            return true;
        }

        return false;
    }
}