<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 組合登入/驗證回傳資料（含角色/權限）
     */
    private function buildAuthPayload(User $user): array
    {
        return [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }

    /**
     * 登入並取得 API Token
     */
    public function login(Request $request)
    {
        Log::debug('--- [Login Attempt] ---');
        Log::debug('Request data:', $request->all());

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        Log::debug('Validation passed.');

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Authentication failed for email: ' . $request->email);
            throw ValidationException::withMessages([
                'email' => ['提供的帳密不正確。'],
            ]);
        }
        Log::debug('Authentication successful for user: ' . $user->email);

        // 產生 token
        $token = $user->createToken($request->device_name ?? 'default-device')->plainTextToken;
        Log::debug('Token created successfully.');

        $payload = array_merge(
            $this->buildAuthPayload($user),
            ['token' => $token]
        );
        
        Log::debug('--- [Login Success] ---');
        return response()->json($payload);
    }

    /**
     * 登出並撤銷當前 Token
     */
    public function logout(Request $request)
    {
        // 撤銷使用者的當前 token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    /**
     * 取得目前登入使用者資料
     */
    public function me(Request $request)
    {
        return response()->json($this->buildAuthPayload($request->user()));
    }

    /**
     * 撤銷所有 Token（登出全部裝置）
     */
    public function logoutAll(Request $request)
    {
        // 撤銷使用者的所有 token
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => '已登出所有裝置',
        ]);
    }

    /**
     * 修改密碼（已登入使用者）
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // 驗證舊密碼
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['舊密碼不正確。'],
            ]);
        }

        // 更新密碼
        $user->password = Hash::make($request->new_password);
        $user->save();

        // 撤銷所有 token（強制重新登入）
        $user->tokens()->delete();

        return response()->json([
            'message' => '密碼已成功修改，請重新登入。',
        ]);
    }

    /**
     * 忘記密碼 - 請求重設密碼
     * 注意：實作會需要設定郵件服務來發送重設連結
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // 安全考量，不回傳使用者是否存在
            return response()->json([
                'message' => '若該電子郵件存在，將會收到密碼重設說明。',
            ]);
        }

        // TODO: 在真實專案中，這裡應該：
        // 1. 產生重設 token 並存到 password_reset_tokens
        // 2. 發送包含 token 的郵件給使用者
        return response()->json([
            'message' => '若該電子郵件存在，將會收到密碼重設說明。',
        ]);
    }

    /**
     * 重設密碼
     * 注意：真實實作需驗證重設 token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

        // TODO: 在真實專案中，這裡應該：
        // 1. 驗證 token 是否存在且未過期
        // 2. 驗證 token 是否屬於該 email
        // 3. 重設密碼後刪除 token

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['找不到該電子郵件的使用者。'],
            ]);
        }

        // 更新密碼
        $user->password = Hash::make($request->password);
        $user->save();

        // 撤銷所有 token（強制重新登入）
        $user->tokens()->delete();

        return response()->json([
            'message' => '密碼已成功重設，請使用新密碼登入。',
        ]);
    }
}