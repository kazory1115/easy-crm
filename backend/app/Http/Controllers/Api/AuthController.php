<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function buildAuthPayload(User $user): array
    {
        return [
            'user' => $user,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }

    public function login(Request $request)
    {
        Log::debug('--- [Login Attempt] ---');
        Log::debug('Request data:', $request->all());

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Authentication failed for email: ' . $request->email);

            throw ValidationException::withMessages([
                'email' => ['帳號或密碼錯誤。'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'default-device')->plainTextToken;

        return response()->json(array_merge(
            $this->buildAuthPayload($user),
            ['token' => $token]
        ));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => '已登出',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($this->buildAuthPayload($request->user()));
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => '已登出所有裝置',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['目前密碼錯誤。'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            'message' => '密碼更新成功，請重新登入。',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::broker()->sendResetLink([
            'email' => $request->email,
        ]);

        if (!in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)) {
            return response()->json([
                'message' => '重設密碼信寄送失敗',
                'error' => __($status),
            ], 500);
        }

        return response()->json([
            'message' => '如果該 Email 存在，系統已寄出重設密碼信。',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required|string',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [$this->mapResetPasswordStatus($status)],
            ]);
        }

        return response()->json([
            'message' => '密碼已重設成功，請使用新密碼登入。',
        ]);
    }

    private function mapResetPasswordStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => '重設連結無效或已過期。',
            Password::INVALID_USER => '找不到對應的使用者。',
            Password::RESET_THROTTLED => '請稍後再重新申請重設密碼信。',
            default => __($status),
        };
    }
}
