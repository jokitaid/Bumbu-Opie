<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Login/Register via Google
     *
     * @OA\Post(
     *     path="/api/auth/google",
     *     summary="Login atau register menggunakan akun Google",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="ya29.a0AfH6SMB..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login Google berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login Google berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token Google tidak valid"
     *     )
     * )
     */
    public function googleSignIn(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
        } catch (\Exception $e) {
            Log::error('Google token invalid', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Token Google tidak valid',
            ], 400);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Jika user belum ada, buat baru
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // random password
                'email_verified_at' => now(),
                'role' => 'pengguna',
                'phone' => null,
                'address' => null,
            ]);
        }

        // Buat token Sanctum
        $token = $user->createToken('google-login')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Google berhasil',
            'data' => [
                'token' => $token,
                'user' => $user,
            ]
        ]);
    }
} 