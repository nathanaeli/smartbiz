<?php
namespace App\Http\Controllers;

use App\Mail\WelcomeUserMail;
use App\Models\Duka;
use App\Models\DukaSubscription;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:255',
            'plan_id'       => 'required|exists:plans,id',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create the User (The Owner)
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'tenant', // Assigning the owner role
            ]);

            // 2. Create the Tenant Account
            $tenant = Tenant::create([
                'name'    => $request->business_name,
                'user_id' => $user->id,
                'status'  => 'active',
                'slug'    => \Illuminate\Support\Str::slug($request->business_name),
            ]);

            // Update user with tenant_id for easy access
            $user->update(['tenant_id' => $tenant->id]);

            // 3. Attach the Plan (Start 14-Day Free Trial)
            $plan = Plan::find($request->plan_id);

            DukaSubscription::create([
                'tenant_id'  => $tenant->id,
                'plan_id'    => $plan->id,
                'plan_name'  => $plan->name,
                'amount'     => 0, // 0 for trial
                'start_date' => now(),
                'end_date'   => now()->addDays(14),
                'status'     => 'active', // Trial is technically active
            ]);

            // Send welcome email
            try {
                Mail::to($user->email)->send(new WelcomeUserMail($user, 14));
            } catch (\Exception $e) {
                \Log::warning("Failed to send welcome email", ['error' => $e->getMessage()]);
            }

            auth()->login($user);

            return redirect()->route('login')->with('success', 'Welcome to Smartbiz! Your 14-day trial has started.');
        });
    }
    public function showRegistrationForm($planId = null)
    {
        // 1. Fetch the selected plan, or default to the "Essential" plan if none provided
        $selectedPlan = Plan::find($planId);

        if (! $selectedPlan) {
            // Fallback: Pick the first active plan with the lowest price
            $selectedPlan = Plan::where('is_active', true)
                ->orderBy('price', 'asc')
                ->first();
        }

        // 2. Fetch all active plans in case they want to switch within the form
        $allPlans = Plan::where('is_active', true)->get();

        return view('auth.register', compact('selectedPlan', 'allPlans'));
    }

    public function registerplan(Request $request)
    {
        \Log::info('Registration process started', [
            'ip'             => $request->ip(),
            'user_agent'     => substr($request->userAgent() ?? 'unknown', 0, 200),
            'timestamp'      => now(),
            'request_method' => $request->method(),
            'is_ajax'        => $request->ajax(),
            'all_input'      => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            $request->validate([
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:6|confirmed',
                'duka_name'    => 'required|string|max:255',
                'location'     => 'required|string|max:255',
                'manager_name' => 'required|string|max:255',
                'plan_id'      => 'required|exists:plans,id',
                'duration'     => 'required|in:1,12,36',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input'  => $request->except(['password', 'password_confirmation']),
                'ip'     => $request->ip(),
            ]);

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $e->errors(),
                    'message' => 'Validation failed. Please check your input.',
                ], 422);
            }

            throw $e;
        }

        DB::beginTransaction();

        try {
            $plan   = Plan::findOrFail($request->plan_id);
            $months = (int) $request->duration;

            // discount rules
            $discount = ($months === 12) ? 0.10 : (($months === 36) ? 0.20 : 0.00);
            $amount   = round(($plan->price * $months) * (1 - $discount), 2);

            // Create user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'role'     => 'tenant',
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('tenant');

            // Create Tenant
            $tenant = Tenant::create([
                'name'    => $request->name,
                'slug'    => Str::slug($request->name) . '-' . Str::random(5),
                'email'   => $request->email,
                'phone'   => null,
                'user_id' => $user->id,
                'address' => $request->location,
                'status'  => 'active',
            ]);

            // Update user with tenant_id
            $user->update(['tenant_id' => $tenant->id]);

            // Create Duka
            $duka = Duka::create([
                'tenant_id'    => $tenant->id,
                'name'         => $request->duka_name,
                'location'     => $request->location,
                'manager_name' => $request->manager_name,
                'status'       => 'active',
            ]);

            // Subscription dates
            $startDate = now();
            $endDate   = now()->addMonths($months);

            // Create subscription (FIXED)
            $subscription = DukaSubscription::create([
                'tenant_id'  => $tenant->id,
                'duka_id'    => $duka->id,
                'plan_id'    => $plan->id,
                'amount'     => $amount, // fixed: no number_format()
                'plan_name'  => $plan->name,
                'start_date' => $startDate, // fixed: pass Carbon instance
                'end_date'   => $endDate,   // fixed: pass Carbon instance
                'status'     => 'pending',
            ]);

            // Email but don't stop if fails
            try {
                Mail::to($user->email)->send(new WelcomeUserMail($user, 14));
            } catch (\Exception $e) {
                \Log::warning("Failed to send welcome email", ['error' => $e->getMessage()]);
            }

            DB::commit();

            Auth::login($user);

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Registration successful!',
                    'redirect' => route('payment.checkout', [
                        'tenant'       => Crypt::encrypt($tenant->id),
                        'subscription' => Crypt::encrypt($subscription->id),
                    ]),
                ]);
            }

            notify()->success('Registration successful! Proceed to payment.');

            return redirect()->route('payment.checkout', [
                'tenant'       => Crypt::encrypt($tenant->id),
                'subscription' => Crypt::encrypt($subscription->id),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('Registration process failed', [
                'error_message' => $e->getMessage(),
                'error_line'    => $e->getLine(),
                'error_file'    => $e->getFile(),
                'input_data'    => $request->except(['password', 'password_confirmation']),
                'timestamp'     => now(),
            ]);

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed. Please try again.',
                    'error'   => $e->getMessage(), // For debugging, remove in production
                ], 500);
            }

            notify()->error('Registration failed. Please try again.');
            return back()->withInput();
        }
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|min:6',
            'remember_me' => 'nullable|boolean',
        ]);

        $maxAttempts  = 5;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            notify()->error("Too many attempts. Try again in $seconds seconds.");
            return back();
        }

        $credentials = $request->only('email', 'password');
        $remember    = $request->remember_me ? true : false;

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey($request), $decayMinutes);
            notify()->error('Incorrect email or password.');
            return back()->withInput();
        }

        RateLimiter::clear($this->throttleKey($request));
        $user = Auth::user();
        if (isset($user->status) && $user->status === 'inactive') {
            Auth::logout();
            notify()->warning('Your account has been deactivated.');
            return back();
        }

        $request->session()->regenerate();

        if (Hash::needsRehash($user->password)) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        $ip        = $request->ip();
        $userAgent = $request->userAgent();
        dispatch(function () use ($user, $ip, $userAgent) {
            app(AuthController::class)->processLoginSession($user, $ip, $userAgent);
        });

        if ($user->role == "superadmin") {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->role == "officer") {
            return redirect()->route('officer.dashboard');
        }

        if ($user->role == "tenant") {
            return redirect()->route('tenant.dashboard');
        }

        return redirect()->route('officer.dashboard');
    }

    public function logout(Request $request)
    {
        $user      = Auth::user();
        $sessionId = session()->getId();

        // Mark this session as inactive
        if ($user) {
            UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->update(['is_active' => false]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        notify()->success('You have been logged out.');
        return redirect()->route('login');
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->email) . '|' . $request->ip();
    }

    protected function handleLoginSession(User $user, Request $request)
    {
        $this->processLoginSession($user, $request->ip(), $request->userAgent());
    }

    protected function processLoginSession(User $user, $ip, $userAgent)
    {
        $sessionId = session()->getId();

        // Generate device fingerprint
        $fingerprint = UserSession::generateFingerprint($userAgent, $ip);

        // Check if this is a new device
        $isNewDevice = UserSession::isNewDevice($user->id, $fingerprint);

        // Create or update session record
        UserSession::updateOrCreate(
            [
                'user_id'    => $user->id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address'         => $ip,
                'user_agent'         => $userAgent,
                'device_fingerprint' => $fingerprint,
                'last_activity'      => now(),
                'is_active'          => true,
            ]
        );

        // Send login notification email
        try {
            // Mail::to($user->email)->send(new LoginNotification($user, $ip, $userAgent, $isNewDevice));
        } catch (\Exception $e) {
            // Log email sending failure but don't block login
            \Log::warning('Failed to send login notification email', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // Clean up old inactive sessions (older than 30 days)
        UserSession::where('user_id', $user->id)
            ->where('last_activity', '<', now()->subDays(30))
            ->update(['is_active' => false]);
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed|different:current_password',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log password change
        \Log::info('Password changed', [
            'user_id'   => $user->id,
            'email'     => $user->email,
            'ip'        => $request->ip(),
            'timestamp' => now(),
        ]);

        notify()->success('Password changed successfully!');
        return redirect()->back();
    }

    // Password Reset Methods
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            notify()->success('Password reset link sent to your email!');
            return back();
        }

        notify()->error('Unable to send reset link. Please try again.');
        return back();
    }

    public function showResetPasswordForm(Request $request)
    {
        $token = $request->route('token');
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
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
                \Log::info('Password reset successful', [
                    'user_id'   => $user->id,
                    'email'     => $user->email,
                    'ip'        => request()->ip(),
                    'timestamp' => now(),
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            notify()->success('Password reset successfully! You can now login with your new password.');
            return redirect()->route('login');
        }

        notify()->error('Password reset failed. Please try again.');
        return back();
    }

    public function apiLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $maxAttempts  = 5;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in $seconds seconds.",
            ], 429);
        }

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($request), $decayMinutes);
            return response()->json([
                'success' => false,
                'message' => 'Incorrect email or password.',
            ], 401);
        }

        RateLimiter::clear($this->throttleKey($request));
        $user = Auth::user();

        if (isset($user->status) && $user->status === 'inactive') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Create token
        $token = $user->createToken('API Token')->plainTextToken;

        // Load user relationships based on role
        $userData = $this->getDetailedUserData($user);

        // Log login
        \Log::info('API Login successful', [
            'user_id'   => $user->id,
            'email'     => $user->email,
            'ip'        => $request->ip(),
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data'    => [
                'user'  => $userData,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Send password reset link via API
     */
    public function apiForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Rate limiting for forgot password
        $throttleKey  = 'forgot-password:' . $request->ip() . ':' . $request->email;
        $maxAttempts  = 3;
        $decayMinutes = 5;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many requests. Try again in $seconds seconds.",
            ], 429);
        }

        try {
            // Get the user
            $user = User::where('email', $request->email)->first();

            // Generate a password reset token
            $token = app('auth.password.broker')->createToken($user);

            // Send API-based password reset notification
            $user->sendPasswordResetNotification($token, true);

            // Log the password reset request
            \Log::info('Password reset link sent via API', [
                'email'     => $request->email,
                'ip'        => $request->ip(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been sent to your email address.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Password reset API error', [
                'email'     => $request->email,
                'error'     => $e->getMessage(),
                'ip'        => $request->ip(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    /**
     * Reset password via API
     */
    public function apiResetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token'    => 'required|string',
        ]);

        // Rate limiting for password reset
        $throttleKey  = 'reset-password:' . $request->ip() . ':' . $request->email;
        $maxAttempts  = 5;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many attempts. Try again in $seconds seconds.",
            ], 429);
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));

                    // Log password reset
                    \Log::info('Password reset successful via API', [
                        'user_id'   => $user->id,
                        'email'     => $user->email,
                        'ip'        => request()->ip(),
                        'timestamp' => now(),
                    ]);
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password has been reset successfully.',
                ]);
            }

            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            $errorMessage = match ($status) {
                Password::INVALID_TOKEN    => 'Invalid or expired password reset token.',
                Password::INVALID_USER     => 'No user found with this email address.',
                Password::INVALID_PASSWORD => 'The password does not meet the required criteria.',
                default                    => 'Password reset failed. Please try again.',
            };

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Password reset API error', [
                'email'     => $request->email,
                'error'     => $e->getMessage(),
                'ip'        => $request->ip(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    protected function getDetailedUserData(User $user)
    {
        // Load basic user data
        $userData = [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => $user->role,
            'profile_picture'   => $user->profile_picture,
            'status'            => $user->status,
            'email_verified_at' => $user->email_verified_at,
            'created_at'        => $user->created_at,
            'updated_at'        => $user->updated_at,
        ];

        // Load role-specific data
        switch ($user->role) {
            case 'superadmin':
                $userData['permissions']   = ['all'];
                $userData['is_superadmin'] = true;
                break;

            case 'tenant':
                $tenant = $user->tenant;
                if ($tenant) {
                    $userData['tenant'] = [
                        'id'         => $tenant->id,
                        'name'       => $tenant->name,
                        'slug'       => $tenant->slug,
                        'email'      => $tenant->email,
                        'phone'      => $tenant->phone,
                        'address'    => $tenant->address,
                        'status'     => $tenant->status,
                        'created_at' => $tenant->created_at,
                        'updated_at' => $tenant->updated_at,
                    ];

                    // Load tenant account information
                    $tenantAccount = $tenant->tenantAccount;
                    if ($tenantAccount) {
                        $userData['tenant_account'] = [
                            'id'           => $tenantAccount->id,
                            'company_name' => $tenantAccount->company_name,
                            'logo'         => $tenantAccount->logo,
                            'logo_url'     => $tenantAccount->logo_url,
                            'phone'        => $tenantAccount->phone,
                            'email'        => $tenantAccount->email,
                            'address'      => $tenantAccount->address,
                            'currency'     => $tenantAccount->currency,
                            'timezone'     => $tenantAccount->timezone,
                            'website'      => $tenantAccount->website,
                            'description'  => $tenantAccount->description,
                            'created_at'   => $tenantAccount->created_at,
                            'updated_at'   => $tenantAccount->updated_at,
                        ];
                    }

                    // Load duka information (tenants can have multiple dukas)
                    $dukas = $tenant->dukas;
                    if ($dukas->isNotEmpty()) {
                        $userData['dukas'] = $dukas->map(function ($duka) {
                            return [
                                'id'           => $duka->id,
                                'name'         => $duka->name,
                                'location'     => $duka->location,
                                'manager_name' => $duka->manager_name,
                                'status'       => $duka->status,
                                'created_at'   => $duka->created_at,
                                'updated_at'   => $duka->updated_at,
                            ];
                        });
                    }

                    // Load subscription information
                    $subscription = $tenant->dukaSubscriptions()->latest()->first();
                    if ($subscription) {
                        $userData['subscription'] = [
                            'id'         => $subscription->id,
                            'plan_name'  => $subscription->plan_name,
                            'amount'     => $subscription->amount,
                            'start_date' => $subscription->start_date,
                            'end_date'   => $subscription->end_date,
                            'status'     => $subscription->status,
                        ];
                    }
                }
                $userData['permissions'] = ['all'];
                break;

            case 'officer':
                // Load officer assignments
                $assignments             = $user->officerAssignments()->with('tenant')->get();
                $userData['assignments'] = $assignments->map(function ($assignment) {
                    return [
                        'id'        => $assignment->id,
                        'tenant_id' => $assignment->tenant_id,
                        'status'    => $assignment->status,
                        'tenant'    => $assignment->tenant ? [
                            'id'      => $assignment->tenant->id,
                            'name'    => $assignment->tenant->name,
                            'slug'    => $assignment->tenant->slug,
                            'email'   => $assignment->tenant->email,
                            'address' => $assignment->tenant->address,
                        ] : null,
                    ];
                });

                // Get permissions for active assignments
                $permissions             = $user->getPermissions();
                $userData['permissions'] = $permissions->toArray();

                // Get tenant IDs for this officer
                $tenantIds = $assignments->pluck('tenant_id')->unique()->toArray();

                // Load categories for assigned tenants
                $categories = \App\Models\ProductCategory::whereIn('tenant_id', $tenantIds)
                    ->where('status', 'active')
                    ->with(['tenant', 'parent'])
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id'          => $category->id,
                            'tenant_id'   => $category->tenant_id,
                            'name'        => $category->name,
                            'parent_id'   => $category->parent_id,
                            'description' => $category->description,
                            'status'      => $category->status,
                            'parent'      => $category->parent ? [
                                'id'   => $category->parent->id,
                                'name' => $category->parent->name,
                            ] : null,
                            'tenant'      => [
                                'id'   => $category->tenant->id,
                                'name' => $category->tenant->name,
                            ],
                            'created_at'  => $category->created_at,
                            'updated_at'  => $category->updated_at,
                        ];
                    });
                $userData['categories'] = $categories;

                // Load products for assigned tenants
                $products = \App\Models\Product::whereIn('tenant_id', $tenantIds)
                    ->with(['category', 'duka'])
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id'            => $product->id,
                            'tenant_id'     => $product->tenant_id,
                            'duka_id'       => $product->duka_id,
                            'category_id'   => $product->category_id,
                            'sku'           => $product->sku,
                            'name'          => $product->name,
                            'description'   => $product->description,
                            'unit'          => $product->unit,
                            'base_price'    => $product->base_price,
                            'selling_price' => $product->selling_price,
                            'is_active'     => $product->is_active,
                            'image'         => $product->image,
                            'barcode'       => $product->barcode,
                            'current_stock' => $product->current_stock,
                            'image_url'     => $product->image_url,
                            'category'      => $product->category ? [
                                'id'   => $product->category->id,
                                'name' => $product->category->name,
                            ] : null,
                            'duka'          => $product->duka ? [
                                'id'       => $product->duka->id,
                                'name'     => $product->duka->name,
                                'location' => $product->duka->location,
                            ] : null,
                            'created_at'    => $product->created_at,
                            'updated_at'    => $product->updated_at,
                        ];
                    });
                $userData['products'] = $products;

                // Load all team members (other officers) for assigned tenants
                $teamMembers = \App\Models\TenantOfficer::whereIn('tenant_id', $tenantIds)
                    ->where('officer_id', '!=', $user->id)
                    ->with(['officer', 'tenant'])
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id'         => $assignment->id,
                            'tenant_id'  => $assignment->tenant_id,
                            'officer_id' => $assignment->officer_id,
                            'role'       => $assignment->role,
                            'status'     => $assignment->status,
                            'officer'    => $assignment->officer ? [
                                'id'     => $assignment->officer->id,
                                'name'   => $assignment->officer->name,
                                'email'  => $assignment->officer->email,
                                'role'   => $assignment->officer->role,
                                'status' => $assignment->officer->status,
                            ] : null,
                            'tenant'     => $assignment->tenant ? [
                                'id'   => $assignment->tenant->id,
                                'name' => $assignment->tenant->name,
                            ] : null,
                        ];
                    });
                $userData['team_members'] = $teamMembers;
                break;

            default:
                $userData['permissions'] = [];
                break;
        }

        return $userData;
    }
}
