<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\forgetPassword;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;
use App\Models\User;

class ForgetPasswordController extends Controller
{
    public function sendEmail(Request $request)
    {
        if (!$this->validateEmail($request->email)) {  // this is validate to fail send mail or true
            return $this->failedResponse();
        }
        $this->send($request->email);  //this is a function to send mail 
        return $this->successRespopnse();
    }

    public function send($email)
    {
        $token = $this->createToken($email);
        Mail::to($email)->send(new forgetPassword($token, $email));  // token is important in send mail 
    }

    public function createToken($email)
    {
        $oldToken = DB::table('password_reset_tokens')->where('email', $email)->first();

        if($oldToken)
        {
            return $oldToken->token;
        }

        $token = random_int(100000, 999999);
        $this->saveToken($token, $email);
        return $token;
    }

    public function saveToken($token, $email)
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function validateEmail($email)
    {
        return !!User::where('email', $email)->first();
    }

    public function failedResponse()
    {
        $message = 'هذا الإيميل غير موجود';
        $statusCode = Response::HTTP_BAD_REQUEST;
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    public function successRespopnse()
    {
        $statusCode = Response::HTTP_OK;
        $message = 'تم إرسال الكود بنجاح';
        return response()->json([
            'status' => $statusCode,
            'message' => $message
        ], 200);
    }
}
