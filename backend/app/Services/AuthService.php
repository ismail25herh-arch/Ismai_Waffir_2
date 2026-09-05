<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function requestOtp($identifier, $purpose = 'login')
    {
        $code = (string) random_int(100000, 999999);
        Otp::where('identifier', $identifier)->where('purpose', $purpose)
            ->whereNull('consumed_at')->update(['consumed_at' => now()]);
        Otp::create(['identifier' => $identifier, 'purpose' => $purpose,
            'code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(5)]);
        return $code;
    }

    public function verifyOtp($identifier, $code, $name = null, $purpose = 'login')
    {
        $otp = Otp::where('identifier', $identifier)->where('purpose', $purpose)
            ->whereNull('consumed_at')->where('expires_at', '>', now())->latest()->first();
        if (!$otp || $otp->attempts >= 5 || !Hash::check($code, $otp->code_hash)) {
            if ($otp) $otp->increment('attempts');
            throw ValidationException::withMessages(['otp' => ['رمز التحقق غير صحيح أو منتهي الصلاحية.']]);
        }
        $otp->update(['consumed_at' => now()]);
        $user = User::where('phone_number', $identifier)->orWhere('email', $identifier)->first();
        if (!$user) {
            $user = User::create(['phone_number' => $identifier,
                'email' => null,
                'name' => $name ?: 'مستخدم وفّر', 'password' => Hash::make(Str::random(40)),
                'role' => 0, 'is_active' => true]);
        }
        if (!$user->is_active) {
            throw ValidationException::withMessages(['phone_number' => ['حساب المستخدم غير مفعل.']]);
        }
        return [$user, $this->issueTokens($user)];
    }

    public function passwordLogin($email, $password)
    {
        $user = User::where('email', $email)->orWhere('phone_number', $email)->first();
        if (!$user || !$user->is_active || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['بيانات الدخول غير صحيحة.']]);
        }
        return [$user, $this->issueTokens($user)];
    }

    public function issueTokens(User $user)
    {
        $access = $user->createToken('flutter-access', ['access']);
        $refresh = $user->createToken('flutter-refresh', ['refresh']);
        $minutes = (int) config('sanctum.expiration', 43200);
        return ['access_token' => $access->plainTextToken, 'refresh_token' => $refresh->plainTextToken,
            'token_type' => 'Bearer', 'expires_in' => $minutes * 60];
    }

    public function revokeCurrent(User $user)
    {
        if ($user->currentAccessToken()) $user->currentAccessToken()->delete();
    }
}
