<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 登入並生成 API Token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['提供的憑證不正確。'],
            ]);
        }

        // 生成 token
        $token = $user->createToken($request->device_name ?? 'default-device')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * 登出並撤銷當前 Token
     */
    public function logout(Request $request)
    {
        // 撤銷當前用戶的當前 token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => '登出成功',
        ]);
    }

    /**
     * 獲取當前登入用戶資訊
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * 撤銷所有 Token（登出所有裝置）
     */
    public function logoutAll(Request $request)
    {
        // 撤銷當前用戶的所有 token
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => '已從所有裝置登出',
        ]);
    }

    /**
     * 修改密碼（已登入用戶）
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // 驗證當前密碼
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['當前密碼不正確。'],
            ]);
        }

        // 更新密碼
        $user->password = Hash::make($request->new_password);
        $user->save();

        // 撤銷所有舊的 token（強制重新登入）
        $user->tokens()->delete();

        return response()->json([
            'message' => '密碼已成功修改，請重新登入。',
        ]);
    }

    /**
     * 忘記密碼 - 請求重設密碼
     * 注意：完整實作需要配置郵件服務來發送重設連結
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // 為了安全考量，不透露用戶是否存在
            return response()->json([
                'message' => '如果該電子郵件存在，將會收到密碼重設說明。',
            ]);
        }

        // TODO: 在實際專案中，這裡應該：
        // 1. 生成重設 token 並儲存到 password_reset_tokens 表
        // 2. 發送包含 token 的郵件給用戶
        // 目前先返回成功訊息

        return response()->json([
            'message' => '如果該電子郵件存在，將會收到密碼重設說明。',
        ]);
    }

    /**
     * 重設密碼
     * 注意：完整實作需要驗證重設 token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ]);

        // TODO: 在實際專案中，這裡應該：
        // 1. 驗證 token 是否有效且未過期
        // 2. 驗證 token 是否屬於該 email
        // 3. 重設密碼後刪除 token

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['找不到該電子郵件的用戶。'],
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
