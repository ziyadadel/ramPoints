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
        return response()->json([
            'error' => 'Email does\'t found on our database'
        ], Response::HTTP_NOT_FOUND);
    }

    public function successRespopnse()
    {
        return response()->json([
            'data' => 'Reset Email is send successfully, please check your inbox'
        ], Response::HTTP_OK);
    }
}
