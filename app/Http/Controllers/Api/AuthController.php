<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login user dan hasilkan Sanctum Bearer Token untuk mobile apps.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Email atau kata sandi yang Anda masukkan salah.', 401);
        }

        // Cek status persetujuan akun dari Master Mind
        if ($user->isPending()) {
            return $this->errorResponse(
                'Akun Anda sedang menunggu persetujuan (approval) dari Master Mind (wahyusnjy). Silakan tunggu konfirmasi.',
                403
            );
        }

        if ($user->isRejected()) {
            return $this->errorResponse(
                'Pendaftaran akun Anda telah ditolak oleh Master Mind. Hubungi admin untuk informasi lebih lanjut.',
                403
            );
        }

        if (!$user->isApproved()) {
            return $this->errorResponse(
                'Akun Anda belum memiliki izin untuk masuk ke sistem.',
                403
            );
        }

        // Buatkan token Sanctum
        $deviceName = $validated['device_name'] ?? 'Mobile Device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'status'     => $user->status,
                'is_admin'   => $user->isMasterMind(),
                'created_at' => $user->created_at?->toIso8601String(),
            ]
        ], 'Login berhasil!');
    }

    /**
     * Mendaftarkan pengguna baru dengan status pending approval.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
            'status'   => 'pending',
        ]);

        // Buatkan dompet default CASH
        $user->ensureDefaultCashSaving();

        event(new Registered($user));

        return $this->successResponse([
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'status' => $user->status,
        ], 'Pendaftaran akun berhasil! Akun Anda sedang menunggu persetujuan dari Master Mind (wahyusnjy).', 201);
    }

    /**
     * Logout dan cabut token perangkat saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil. Token perangkat telah dicabut.');
    }
}
