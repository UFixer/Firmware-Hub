<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Maximum registration attempts per IP
     */
    protected $maxAttempts = 3;
    
    /**
     * Decay time for rate limiting (minutes)
     */
    protected $decayMinutes = 60;
    
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        if ($this->isAuthenticated()) {
            return redirect()->route('account.dashboard');
        }
        
        return view('auth.register');
    }
    
    /**
     * Handle a registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Check rate limiting
        if ($this->hasTooManyRegistrationAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }
        
        // Validate the registration data
        $validated = $this->validateRegistration($request);
        
        // Check for duplicate email (additional check)
        if ($this->emailExists($validated['email'])) {
            return back()->withErrors(['email' => 'This email is already registered.'])
                        ->withInput($request->except('password'));
        }
        
        // Begin transaction for data integrity
        DB::beginTransaction();
        
        try {
            // Create the user
            $user = $this->createUser($validated);
            
            // Generate verification token
            $this->generateVerificationToken($user);
            
            // Send verification email (queue for performance)
            $this->sendVerificationEmail($user);
            
            // Create initial user preferences
            $this->createUserPreferences($user);
            
            // Check for referral code
            if ($request->has('referral_code')) {
                $this->processReferral($user, $request->input('referral_code'));
            }
            
            DB::commit();
            
            // Clear rate limiting
            $this->clearRegistrationAttempts($request);
            
            // Flash success message
            Session::flash('success', 'Registration successful! Please check your email to verify your account.');
            
            return redirect()->route('login');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error for debugging
            error_log('Registration error: ' . $e->getMessage());
            
            return back()->withErrors(['email' => 'Registration failed. Please try again.'])
                        ->withInput($request->except('password'));
        }
    }
    
    /**
     * Validate the registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function validateRegistration(Request $request)
    {
        return $request->validate([
            'first_name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'last_name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'phone' => 'nullable|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
            'country_code' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
            'newsletter' => 'boolean',
            'terms' => 'required|accepted',
            'referral_code' => 'nullable|string|max:20'
        ], [
            'first_name.regex' => 'First name can only contain letters and spaces.',
            'last_name.regex' => 'Last name can only contain letters and spaces.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.uncompromised' => 'This password has been compromised in a data breach. Please choose a different password.',
            'terms.accepted' => 'You must accept the terms and conditions.'
        ]);
    }
    
    /**
     * Create a new user instance.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function createUser(array $data)
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'country_code' => $data['country_code'] ?? '+1',
            'role' => 'customer',
            'status' => 'active',
            'is_verified' => false,
            'newsletter_subscribed' => $data['newsletter'] ?? false,
            'timezone' => $data['timezone'] ?? 'UTC',
            'language' => 'en',
            'currency' => 'USD',
            'customer_type' => 'regular',
            'referral_code' => $this->generateReferralCode()
        ]);
        
        return $user;
    }
    
    /**
     * Generate a unique referral code.
     *
     * @return string
     */
    protected function generateReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
    
    /**
     * Generate email verification token.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function generateVerificationToken($user)
    {
        $token = bin2hex(random_bytes(32));
        
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires' => now()->addHours(24)
        ]);
        
        // Store in cache for quick lookup
        Cache::put('email_verify_' . $token, $user->id, 86400); // 24 hours
    }
    
    /**
     * Send verification email to user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function sendVerificationEmail($user)
    {
        // For shared hosting, use simple mail queue table
        DB::table('email_queue')->insert([
            'to' => $user->email,
            'subject' => 'Verify Your Email - FirmwareHub',
            'template' => 'emails.verify',
            'data' => json_encode([
                'user_name' => $user->first_name,
                'verification_url' => route('verification.verify', [
                    'token' => $user->email_verification_token
                ])
            ]),
            'priority' => 'high',
            'created_at' => now()
        ]);
    }
    
    /**
     * Create initial user preferences.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function createUserPreferences($user)
    {
        // Set default preferences
        $preferences = [
            'theme' => 'light',
            'notifications' => [
                'email' => true,
                'sms' => false,
                'browser' => true
            ],
            'privacy' => [
                'profile_visible' => true,
                'show_email' => false,
                'show_phone' => false
            ],
            'download_preferences' => [
                'auto_start' => false,
                'notification_on_complete' => true
            ]
        ];
        
        $user->update(['preferences' => $preferences]);
        
        // Create initial wishlist and cart sessions
        Cache::put('cart_' . $user->id, [], 7200); // 2 hours
        Cache::put('wishlist_' . $user->id, [], 7200);
    }
    
    /**
     * Process referral code if provided.
     *
     * @param \App\Models\User $user
     * @param string $referralCode
     * @return void
     */
    protected function processReferral($user, $referralCode)
    {
        $referrer = User::where('referral_code', strtoupper($referralCode))
                       ->where('status', 'active')
                       ->first();
        
        if ($referrer && $referrer->id !== $user->id) {
            // Update new user
            $user->update(['referred_by' => $referrer->id]);
            
            // Update referrer stats
            $referrer->increment('referral_count');
            
            // Add referral bonus (if applicable)
            $referrer->increment('loyalty_points', 100);
            
            // Log referral
            DB::table('referral_logs')->insert([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'status' => 'pending',
                'bonus_points' => 100,
                'created_at' => now()
            ]);
        }
    }
    
    /**
     * Check if email already exists.
     *
     * @param string $email
     * @return bool
     */
    protected function emailExists($email)
    {
        // Use cache to reduce database queries
        $cacheKey = 'email_exists_' . md5(strtolower($email));
        
        return Cache::remember($cacheKey, 60, function () use ($email) {
            return User::where('email', strtolower($email))->exists();
        });
    }
    
    /**
     * Determine if user has too many registration attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function hasTooManyRegistrationAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request),
            $this->maxAttempts
        );
    }
    
    /**
     * Clear the registration attempts.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function clearRegistrationAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }
    
    /**
     * Send lockout response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = ceil($seconds / 60);
        
        return back()->withErrors([
            'email' => "Too many registration attempts. Please try again in {$minutes} minutes."
        ])->withInput($request->except('password'));
    }
    
    /**
     * Get the throttle key for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return 'register_' . $request->ip();
    }
    
    /**
     * Check if user is already authenticated.
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return Session::has('user_id');
    }
}