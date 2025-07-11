<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\ResetPasswordMail;

/**
 * @OA\Tag(
 *     name="Forgot Password",
 *     description="API endpoints untuk forgot password"
 * )
 */
class ForgotPasswordController extends Controller
{

    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tidak ditemukan'
                ], 404);
            }

            // Generate OTP 6 digit
            $otp = random_int(100000, 999999);
            // Simpan ke DB
            \DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => '',
                    'otp' => $otp,
                    'created_at' => now()
                ]
            );

            // Kirim OTP ke email
            \Mail::to($request->email)->send(new \App\Mail\OtpMail($otp, $user->name));

            return response()->json([
                'status' => 'success',
                'message' => 'Kode verifikasi sudah dikirim ke email Anda'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);
        $record = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        if (!$record || !$record->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak ditemukan'
            ], 404);
        }
        // Cek expired (10 menit)
        if (now()->diffInMinutes($record->created_at) > 10) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP sudah expired'
            ], 400);
        }
        if ($request->otp != $record->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP salah'
            ], 400);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'OTP valid'
        ]);
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan'
            ], 404);
        }
        $record = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        if (!$record || !$record->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak ditemukan'
            ], 404);
        }
        if (now()->diffInMinutes($record->created_at) > 10) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP sudah expired'
            ], 400);
        }
        if ($request->otp != $record->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP salah'
            ], 400);
        }
        // Update password
        $user->update([
            'password' => \Hash::make($request->password)
        ]);
        // Hapus token & otp
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        // Hapus semua token Sanctum user
        $user->tokens()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset password dengan token",
     *     tags={"Forgot Password"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="reset_token_here"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password berhasil direset",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password berhasil direset")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token tidak valid atau expired"
     *     )
     * )
     */
    public function reset(Request $request)
    {
        try {
            Log::info('Reset password attempt', ['email' => $request->email]);

            // Validasi input
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Cek apakah email terdaftar
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                Log::warning('Reset password failed: Email not found', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email tidak ditemukan'
                ], 404);
            }

            // Cek token reset password
            $resetRecord = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                Log::warning('Reset password failed: No reset record found', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak ditemukan'
                ], 400);
            }

            // Cek apakah token valid
            if (!Hash::check($request->token, $resetRecord->token)) {
                Log::warning('Reset password failed: Invalid token', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak valid'
                ], 400);
            }

            // Cek apakah token sudah expired (60 menit)
            $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                Log::warning('Reset password failed: Token expired', ['email' => $request->email]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password sudah expired'
                ], 400);
            }

            // Update password user
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Hapus token reset password
            \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Hapus semua token Sanctum user (force logout dari semua device)
            $user->tokens()->delete();

            Log::info('Reset password successful', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil direset'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Reset password validation failed', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Reset password error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/verify-reset-token",
     *     summary="Verifikasi token reset password",
     *     tags={"Forgot Password"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="reset_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Token valid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token tidak valid atau expired"
     *     )
     * )
     */
    public function verifyToken(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);

            // Cek token reset password
            $resetRecord = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak ditemukan'
                ], 400);
            }

            // Cek apakah token valid
            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password tidak valid'
                ], 400);
            }

            // Cek apakah token sudah expired (60 menit)
            $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token reset password sudah expired'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Token valid'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Verify token error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat verifikasi token: ' . $e->getMessage()
            ], 500);
        }
    }
} 