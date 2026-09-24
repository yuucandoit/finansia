<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan informasi profil lengkap pengguna yang sedang login.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalBalance = (float) $user->savings()->sum('current_balance');
        $savingsCount = $user->savings()->count();
        $transactionsCount = $user->transactions()->count();

        return $this->successResponse([
            'id'                      => $user->id,
            'name'                    => $user->name,
            'email'                   => $user->email,
            'role'                    => $user->role,
            'status'                  => $user->status,
            'is_admin'                => $user->isMasterMind(),
            'total_balance'           => $totalBalance,
            'total_balance_formatted' => 'Rp ' . number_format($totalBalance, 0, ',', '.'),
            'total_savings'           => $savingsCount,
            'total_transactions'      => $transactionsCount,
            'created_at'              => $user->created_at?->translatedFormat('d F Y'),
        ], 'Data profil berhasil diambil.');
    }

    /**
     * Memperbarui nama dan email pengguna.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return $this->successResponse([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ], 'Profil berhasil diperbarui.');
    }

    /**
     * Memperbarui kata sandi akun pengguna.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->successResponse(null, 'Kata sandi berhasil diperbarui.');
    }
}
