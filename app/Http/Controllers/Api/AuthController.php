<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Intervention\Image\Facades\Image;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'phone' => 'required|string|max:20|unique:users,phone',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string', // Can be email or phone
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $login = $validated['login'];
        $password = $validated['password'];

        // Determine if login is email or phone
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = null;

        if ($field === 'phone') {
            // Normalize phone: remove all non-digits except leading +
            $normalizedPhone = $this->normalizePhone($login);

            // Try to find user with various phone formats
            $user = $this->findUserByPhone($normalizedPhone);

            if (!$user || !Hash::check($password, $user->password)) {
                return $this->errorResponse('Invalid credentials', 401);
            }
        } else {
            // Email login
            if (!Auth::attempt(['email' => $login, 'password' => $password])) {
                return $this->errorResponse('Invalid credentials', 401);
            }
            $user = User::where('email', $login)->first();
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Your account has been suspended', 403);
        }

        $user->updateLastActive();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out from all devices');
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->updateLastActive();

        return $this->successResponse([
            'user' => $user->load(['listings' => function ($q) {
                $q->latest()->limit(5);
            }]),
            'stats' => [
                'total_listings' => $user->listings()->count(),
                'active_listings' => $user->listings()->active()->count(),
                'total_favorites' => $user->favorites()->count(),
                'unread_messages' => $user->buyerConversations()
                    ->with('messages')
                    ->get()
                    ->sum(fn($c) => $c->unread_count),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notification_preferences' => 'nullable|array',
        ]);

        $user->update($validated);

        return $this->successResponse($user, 'Profile updated successfully');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Compress avatar: 400x400 max, WebP, 80% quality
        $filename = Str::uuid() . '.webp';
        $path = "avatars/{$filename}";

        $avatar = Image::make($request->file('avatar'))
            ->fit(400, 400)
            ->encode('webp', 80);

        Storage::disk('public')->put($path, $avatar);
        $avatar->destroy();

        $user->update(['avatar' => $path]);

        return $this->successResponse([
            'avatar_url' => $user->avatar_url,
        ], 'Avatar updated successfully');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->errorResponse('Current password is incorrect', 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Password reset link sent to your email');
        }

        return $this->errorResponse('Unable to send reset link', 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'Password reset successfully');
        }

        return $this->errorResponse('Unable to reset password', 500);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Invalid verification link', 400);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->successResponse(null, 'Email verified successfully');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified', 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification email sent');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Incorrect password', 422);
        }

        // Soft delete the user
        $user->tokens()->delete();
        $user->delete();

        return $this->successResponse(null, 'Account deleted successfully');
    }

    /**
     * Check if a phone number is already registered
     * Used for the listing creation flow
     */
    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $normalizedPhone = $this->normalizePhone($request->phone);
        $user = $this->findUserByPhone($normalizedPhone);

        return $this->successResponse([
            'exists' => $user !== null,
            'name' => $user ? $user->name : null,
        ]);
    }

    /**
     * Register a new user and return token (used in listing flow)
     * Simplified version without email confirmation
     */
    public function quickRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', PasswordRule::min(8)],
            'phone' => 'required|string|max:20',
        ]);

        // Check if phone already exists
        $normalizedPhone = $this->normalizePhone($validated['phone']);
        $existingUser = $this->findUserByPhone($normalizedPhone);

        if ($existingUser) {
            return $this->errorResponse('This phone number is already registered', 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Account created successfully', 201);
    }

    /**
     * Normalize phone number by removing all non-digit characters except leading +
     */
    private function normalizePhone(string $phone): string
    {
        // Remove all characters except digits and leading +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Ensure + is only at the start
        if (strpos($phone, '+') > 0) {
            $phone = str_replace('+', '', $phone);
        }

        return $phone;
    }

    /**
     * Find user by phone number, trying various formats
     * Supports: 8122116594, +918122116594, 918122116594, 08122116594
     */
    private function findUserByPhone(string $phone): ?User
    {
        // Remove leading + if present
        $phoneWithoutPlus = ltrim($phone, '+');

        // Try exact match first
        $user = User::where('phone', $phone)->first();
        if ($user) return $user;

        // Try without +
        $user = User::where('phone', $phoneWithoutPlus)->first();
        if ($user) return $user;

        // Try with + prefix
        $user = User::where('phone', '+' . $phoneWithoutPlus)->first();
        if ($user) return $user;

        // For Indian numbers: try with/without 91 prefix
        if (strlen($phoneWithoutPlus) === 10) {
            // User entered 10 digits, try with 91 prefix
            $user = User::where('phone', '91' . $phoneWithoutPlus)
                ->orWhere('phone', '+91' . $phoneWithoutPlus)
                ->orWhere('phone', '0' . $phoneWithoutPlus)
                ->first();
            if ($user) return $user;
        } elseif (strlen($phoneWithoutPlus) === 12 && str_starts_with($phoneWithoutPlus, '91')) {
            // User entered with 91 prefix, try without
            $withoutCountryCode = substr($phoneWithoutPlus, 2);
            $user = User::where('phone', $withoutCountryCode)
                ->orWhere('phone', '0' . $withoutCountryCode)
                ->first();
            if ($user) return $user;
        } elseif (strlen($phoneWithoutPlus) === 11 && str_starts_with($phoneWithoutPlus, '0')) {
            // User entered with leading 0
            $withoutZero = substr($phoneWithoutPlus, 1);
            $user = User::where('phone', $withoutZero)
                ->orWhere('phone', '91' . $withoutZero)
                ->orWhere('phone', '+91' . $withoutZero)
                ->first();
            if ($user) return $user;
        }

        return null;
    }
}
