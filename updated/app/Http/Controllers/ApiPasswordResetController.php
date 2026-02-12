<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ApiPasswordResetController extends Controller
{
    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.api-reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Handle password reset for web interface.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Log password reset
                \Log::info('Password reset successful via web interface', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                    'timestamp' => now(),
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password has been reset successfully! You can now login with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
