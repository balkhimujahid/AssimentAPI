<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\forgotPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
      // Create USer
    public function CreateUser(Request $req){

        $validator = Validator::make($req->all(),[
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone' => 'required|numeric',
            'password' => 'required|min:6',
        ]);
        if($validator->fails()){
            $result = array('status' => false, 'message' => "validator error accured",
        'error_message' => $validator->errors());
        return response()->json($result, 400);
        }

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'phone' => $req->phone,
            'password' =>bcrypt($req->password),
        ]);

        if($user->id){
            $result = array('status' => true, 'message' => "user Create successfully", $user);
            $responseCode = 200;
        } else{
            $result = array('status' => false, 'message' => "something went wrong");
            $responseCode = 400;
        }
            return response()->json($result, $responseCode);
    }

    // Get User
    public function GetUser(){
        $users = User::all();

        $result = array('status' => true, 'message' => count($users) . " user's Found", 'data' => $users);
            $responseCode = 200;
        return response()->json($result, $responseCode);
    }
    // Get User Detail
    public function GetUserDetail($id){
        $user = User::find($id);

        if(!$user){
            return response()->json(['status' => false, 'message' => "user not found"],404);
        }
        $result = array('status' => true, 'message' =>" user Found", 'data' => $user);
            $responseCode = 200;
        return response()->json($result, $responseCode);
    }

    // user login
    public function login(request $req){
        $validator = Validator::make($req->all(),[
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            $result = array('status' => false, 'message' => "validator error accured",
        'error_message' => $validator->errors());
        return response()->json($result, 400);
        }

        $user = $req->only('email', 'password');

        if(Auth::attempt($user)){
            $user = Auth::user();
            return response()->json(['status' => true, 'message' => "login successfully", 'data' => $user],200);
        }
        return response()->json(['status' => false, 'message' => "Invalid user"],404);
    }

    // Forgot password
    public function forgotPassword(Request $req)
    {
    $validator = Validator::make($req->all(),[
            'email' => 'required|email',
        ]);
        $email = $req->email;

        // Check User's Email Exists or Not
        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json(['status' => false, 'message' => "email not exiest"],404);
        }

        // Generate Token
        $token = Str::random(60);

        // Saving Data to Password Reset Table
        forgotPassword::create([
            'email'=>$email,
            'token'=>$token,
            'created_at'=>Carbon::now()
        ]);

        // Sending EMail with Password Reset View
        Mail::send('forgotpassword', ['token'=>$token], function(Message $message)use($email){
            $message->subject('Reset Your Password');
            $message->to($email);
        });
        return response()->json(['status' => true, 'message' => "Password Reset Email Sent... Check Your Email"],200);
    }


    // reset password
    public function resetPassword(Request $req, $token){
        // Delete Token older than 2 minute
        $formatted = Carbon::now()->subMinutes(2)->toDateTimeString();
        forgotPassword::where('created_at', '<=', $formatted)->delete();

        $req->validate([
            'password' => 'required|confirmed',
        ]);

        $passwordreset = forgotPassword::where('token', $token)->first();

        if(!$passwordreset){
            return response()->json(['status'=>true, 'message'=>'Token is Invalid or Expired'], 404);
        }

        $user = User::where('email', $passwordreset->email)->first();
        $user->password = Hash::make($req->password);
        $user->save();

        // Delete the token after resetting password
        forgotPassword::where('email', $user->email)->delete();

        return response()->json(['status'=>true,'message'=>'Password Reset Success'], 200);
    }
}
