<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan.',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $otp = rand(100000, 999999); // Generate 6-digit OTP

        // Save OTP to password_reset_tokens table
        // We use updateOrInsert to handle re-requests
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $otp, // Storing OTP directly as token for simplicity in mobile flow
                'created_at' => Carbon::now()
            ]
        );

        // Send Email (Using Raw Mail for simplicity, or queue a Mailable)
        try {
            Mail::raw("Kode OTP Reset Password BBI HUB Anda adalah: $otp\n\nKode ini berlaku selama 15 menit.", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Kode OTP Reset Password - BBI HUB');
            });

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi nanti.',
                'error_debug' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP (Optional step before strict reset).
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid.'], 422);
        }

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || $record->token != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau tidak ditemukan.'
            ], 400);
        }

        // Check expiration (optional, e.g. 15 mins)
        // $createdAt = Carbon::parse($record->created_at);
        // if ($createdAt->addMinutes(15)->isPast()) { ... }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP valid.',
        ]);
    }

    /**
     * Reset Password using OTP.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|numeric',
            'password' => 'required|min:6|confirmed', // expects password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate OTP
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || $record->token != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau kadaluarsa.'
            ], 400);
        }

        // Reset Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete OTP
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah! Silakan login dengan password baru.',
        ]);
    }
}
