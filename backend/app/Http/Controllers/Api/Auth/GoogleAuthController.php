<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect pengguna ke halaman autentikasi Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle callback dari Google setelah pengguna berhasil/gagal login.
     */
    public function callback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan email, jika tidak ada maka buat baru (updateOrCreate)
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'              => $googleUser->getName(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            // Buat Sanctum API Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'Login berhasil',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Gagal autentikasi Google',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}