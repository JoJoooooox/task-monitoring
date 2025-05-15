<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use LDAP\Result;
use App\Mail\PHPMailerService;
use Illuminate\Support\Facades\Session;
use App\Providers\RouteServiceProvider;
use App\Services\PhilSMSService;
use App\Models\User;

use Illuminate\Http\Request;

class UserController extends Controller
{

    public function UserLogin(){
        if (Auth::check()) {
            return redirect('/observer/dashboard'); // Redirect if logged in
        }
        return view('user.user_login');
    }

    public function UserForgot(){
        if (Auth::check()) {
            return redirect('/observer/dashboard'); // Redirect if logged in
        }

        return view('user.user_forgot');
    }

    public function UserSendOtpEmail(Request $request){
        if(isset($request->otp)){
            $otp = $request->otp;
            $user_id = $request->user_id_email;

            $compare = Otp::where('user_id', $user_id)->first();
            if(intval($compare->otp_code) === intval($otp)){
                Otp::where('user_id', $user_id)->delete();
                return response()->json([
                    'status' => 'success',
                    'user_id' => $user_id
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect OTP code please try again'
                ]);
            }
        }
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if(!$user){
            return response()->json([
                'status' => 'error',
                'message' => 'This '.$email.' is not existing in the database please try again'
            ]);
        }

        $otpCode = rand(100000, 999999);
        Otp::where('user_id', $user->id)->delete();

        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode
        ]);
        $subject = "Your OTP for Password Reset";
        $body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Secure OTP Verification | Tribo Corporation</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

                body {
                    font-family: 'Poppins', Arial, sans-serif;
                    background-color: #f8f9fa;
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                    color: #444;
                }
                .email-wrapper {
                    max-width: 640px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                .email-header {
                    background: #4C52E4;
                    padding: 30px 20px;
                    text-align: center;
                    border-bottom: 4px solid #3a40c9;
                }
                .email-header h1 {
                    color: #fff;
                    font-size: 24px;
                    font-weight: 600;
                    margin: 0;
                    letter-spacing: 0.5px;
                }
                .email-content {
                    padding: 40px;
                }
                .otp-container {
                    background: #f5f6ff;
                    border-radius: 8px;
                    padding: 25px;
                    margin: 30px 0;
                    text-align: center;
                    border: 1px dashed #4C52E4;
                }
                .otp-code {
                    font-size: 42px;
                    font-weight: 700;
                    letter-spacing: 5px;
                    color: #4C52E4;
                    margin: 15px 0;
                }
                .validity-note {
                    font-size: 14px;
                    color: #666;
                    margin-top: 10px;
                }
                .cta-button {
                    display: inline-block;
                    background: #4C52E4;
                    color: #fff !important;
                    text-decoration: none;
                    padding: 12px 30px;
                    border-radius: 6px;
                    font-weight: 500;
                    margin: 20px 0;
                }
                .security-note {
                    background: #fff8f8;
                    border-left: 4px solid #ff4d4d;
                    padding: 15px;
                    margin: 25px 0;
                    font-size: 14px;
                    border-radius: 4px;
                }
                .footer {
                    background: #4C52E4;
                    padding: 20px;
                    text-align: center;
                    color: rgba(255,255,255,0.9);
                    font-size: 12px;
                }
                .company-info {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #666;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='email-header'>
                    <h1>SECURE PASSWORD RESET</h1>
                </div>

                <div class='email-content'>
                    <p>Dear {$user->name},</p>

                    <p>We've received a request to reset your Tribo Corporation account password. For your security, please use the following One-Time Password (OTP) to verify your identity:</p>

                    <div class='otp-container'>
                        <div style='font-size: 14px; color: #4C52E4; margin-bottom: 10px;'>YOUR VERIFICATION CODE</div>
                        <div class='otp-code'>{$otpCode}</div>
                        <div class='validity-note'>Valid for 5 minutes only</div>
                    </div>

                    <div class='security-note'>
                        <strong>Security Alert:</strong> Never share this code with anyone. Tribo Corporation will never ask you for your OTP or password.
                    </div>

                    <p>If you didn't request this password reset, please secure your account by changing your password immediately or contact our support team.</p>

                    <div class='company-info'>
                        <strong>Tribo Corporation</strong><br>
                        Email: tribo.corp@tribo.uno<br>
                        Support Hours: Mon-Fri, 9AM-6PM
                    </div>
                </div>

                <div class='footer'>
                    © ".date('Y')." Tribo Corporation. All rights reserved.<br>
                    This is an automated message - please do not reply directly
                </div>
            </div>
        </body>
        </html>
        ";

        // Use the PHPMailerService to send the OTP email
        $mailer = new PHPMailerService();
        $sendResult = $mailer->sendMail($user->email, $user->name, $subject, $body);

        if ($sendResult == 'Message has been sent successfully') {
            return response()->json([
                'status' => 'success',
                'user_id' => $user->id
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'There was an error sending the OTP. Please try again later. message:  '.$sendResult
            ]);
        }
    }

    public function UserSendOtpPhone(Request $request){
        if(isset($request->otp)){
            $otp = $request->otp;
            $user_id = $request->user_id_phone;

            $compare = Otp::where('user_id', $user_id)->first();

            if (!$compare) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP not found'
                ]);
            }

            // Check if OTP is older than 5 minutes
            if ($compare->created_at->diffInMinutes(now()) > 5) {
                // Delete expired OTP
                $compare->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP has expired. Please request a new one.'
                ]);
            }

            // Compare OTP codes
            if (intval($compare->otp_code) === intval($otp)) {
                $compare->delete();

                return response()->json([
                    'status' => 'success',
                    'user_id' => $user_id
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incorrect OTP code. Please try again.'
                ]);
            }
        }
        $phone = $request->phone;
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'This ' . $phone . ' is not existing in the database. Please try again.'
            ]);
        }

        $otpCode = mt_rand(100000, 999999); // 6-digit OTP

        $message = "Tribo Corp: Your one-time password (OTP) is {$otpCode}. It is valid for 5 minutes. Never share this code with anyone, including Tribo Corp staff.";
        Otp::where('user_id', $user->id)->delete();
        // Store OTP in the database
        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode
        ]);

        // Send SMS
        $smsService = new PhilSMSService();
        $response = $smsService->sendSMS($user->phone, $message);

        // Check if the response indicates success or failure
        if ($response['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'user_id' => $user->id,
                'message' => 'OTP sent successfully'
            ]);
        } else {
            // Log or return error message from the API
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $response['message']
            ]);
        }
    }

    public function UserSubmitNewPass(Request $request){
        if(isset($request->redirect)){
            $user = User::where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->orWhere('phone', $request->login)
                    ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // If user exists and password is correct, log the user in
                Auth::login($user);

                // Mark user as online
                $user->is_online = true;
                $user->save();

                // Regenerate session to prevent session fixation
                $request->session()->regenerate();

                // Store last activity time
                Session::put('last_activity', now());

                return response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Authentication failed
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ]);
            }
        }

        if(isset($request->set)){
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found']);
            }

            // Update the user's password
            $user->password = Hash::make($request->new_pass);
            $user->save();

            $login = $user->email ?? $user->phone ?? $user->username ?? '';

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully',
                'login' => $login,
                'password' => $request->new_pass
            ]);
        }

    }
}
