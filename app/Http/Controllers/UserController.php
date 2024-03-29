<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use App\Mail\verification;
use Illuminate\Support\Facades\Mail;
use DB;

class UserController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string|unique:users,phone_number',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $message = 'Failed to register user';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => $message,'status' => $statusCode,'errors' => $validator->errors()], 400);
        }

        try {
            // Hash the password
            $passwordHash = Hash::make($request->password);

            // Create a new user instance
            $user = User::create([
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'password' => $passwordHash,
            ]);

            $this->sendVerificationCode($user);

            $message = 'User Created Successfully';
            $statusCode = Response::HTTP_OK;
            // Return a response indicating success
            return response()->json(['message' => $message,'status' => $statusCode,'user' => $user], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return response()->json(['message' => 'Failed to register user','status' => $statusCode, 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        // Assuming you have a 'email' field in your user table
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at == null) {
            $this->sendVerificationCode($request);
            $message = 'أنت بحاجه لتفعيل الإيميل الخاص بك اولاً لقد قمنا بإرسال كود التفعيل';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('user_api')->attempt($credentials)) {
            $user = Auth::guard('user_api')->user();
            $token = auth('user_api')->login($user);

            $message = 'User Loged In Successfully';
            $statusCode = Response::HTTP_OK;

            return response()->json(['status' => $statusCode,'message' => $message, 'user' => $user,'token' => $token]);
        }

        $message = 'كلمة السر غير صحيحة';
        $statusCode = Response::HTTP_BAD_REQUEST;
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
        ], 400);
    }

    public function logout(Request $request)
    {
        Auth::guard('user_api')->logout();

        return response()->json(['message' => 'Successfully logged out']);

    }

    public function refresh()
    {
        // Implement token refresh logic
    }

    public function allUsers()
    {
        $users = User::latest('updated_at')->get();

        return response()->json(['user' => $users]);
    }

    public function searchByDate(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'date' => 'required|date', // Validate record_date
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert record_date to Carbon instance
            $recordDate = Carbon::parse($request->date);

            // Query transactions where record_date is on or after the provided record_date
            $users = User::where('updated_at', '>=', $recordDate->toDateTimeString())->get();

            return response()->json(['users' => $users], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search transactions by record date', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUser(Request $request)
    {
        
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $message = 'User Fetched Successfully';
            $statusCode = Response::HTTP_OK;

            return response()->json(['status' => $statusCode,'message' => $message,'user' => $user], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search transactions by record date', 'error' => $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            $message = 'Failed to change user password';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => $message,'status' => $statusCode,'errors' => $validator->errors()], 400);
        }

        // Find the user by token
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            $message = 'Invalid token';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        // Verify the old password
        if (!Hash::check($request->old_password, $user->password)) {
            $message = 'Old password is incorrect';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        // Update the password
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $message = 'Password changed successfully';
        $statusCode = Response::HTTP_OK;

        return response()->json(['status' => $statusCode ,'message' => $message], 200);
    }


    public function generateVerificationCode()
    {
        // Generate a random 6-digit verification code
        $verificationCode = random_int(100000, 999999);
        return $verificationCode;
    }

    public function sendVerificationCode($user)
    {
        // Assuming you have a 'email' field in your user table
        $user = User::where('email', $user->email)->first();

        if (!$user) {
            $message = 'User not found';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        $verificationCode = $this->generateVerificationCode();

        // Store the hashed verification code in the user record
        $user->verification_code = Hash::make($verificationCode);
        $user->save();

        $email = $user->email;
        $token = $verificationCode;

        Mail::to($email)->send(new verification($token, $email));

        $message = 'Verification code sent successfully';
        $statusCode = Response::HTTP_OK;
        
        return response()->json(['status' => $statusCode,'message' => $message], 200);
    }

    public function verify(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $message = 'User not found';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        if(!$user->email_verified_at == null)
        {
            $message = 'account already verified';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }

        // Check if the provided verification code matches the stored value
        if (Hash::check($request->verification_code, $user->verification_code)) {
            // Verification successful
            $user->verification_code = null; // Clear the verification code after successful verification
            $user->email_verified_at = Carbon::now(); 
            $user->save();

            $message = 'Verification successful';
            $statusCode = Response::HTTP_OK;
            return response()->json(['status' => $statusCode,'message' => $message], 200);
        } else {
            // Verification failed
            $message = 'Invalid verification code';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json(['status' => $statusCode,'message' => $message], 400);
        }
    }

    public function changeForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required|integer',
            'password' => 'required|confirmed|string|min:6',
        ]);

        if ($validator->fails()) {
            $message = 'هنالك خطئ بالإيميل او كلمة السر';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json([
                'status' => $statusCode,
                'message' => $message,
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $verifyCode = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$verifyCode) {
            $message = 'هذا المستخدم غير موجود';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json([
                'status' => $statusCode,
                'message' => $message,
            ], 400);
        }

        if (!$user) {
            $message = 'هذا المستخدم غير موجود';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json([
                'status' => $statusCode,
                'message' => $message,
            ], 400);
        }

        if ($verifyCode->token !== $request->verification_code) {
            $message = 'الكود غير صحيح';
            $statusCode = Response::HTTP_BAD_REQUEST;
            return response()->json([
                'status' => $statusCode,
                'message' => $message
            ], 400);
        }

        // Verification successful
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $user->password = Hash::make($request->password);
        $user->save();

        $message = 'تم تغيير كلمة السر بنجاح';
        $statusCode = Response::HTTP_OK;
        return response()->json(['status' => $statusCode, 'message' => $message], 200);
    }

}
