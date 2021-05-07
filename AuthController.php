<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use DB;
use Hash;
use Carbon\Carbon;

class AuthController extends Controller {

    /**
     * POST METHOD
     * to call laravel blade 
     * @param Request $request
     * @return type
     */
    public function Home(){
        return view('home');
    }

    /**
     * POST METHOD
     * User Sign Up 
     * @param Request $request
     * @return type
     */
    public function Register(Request $request) {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'device_token' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
            } else {
                //check email 
                if(User::where("email", $request->email)->exists()){
                    return response()->json(array('error' => true, 'message' => 'Email already exist', 'data' => []), 200);
                }else{
                    $userDetails = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'device_token' => $request->device_token,
                    ]);
                    if(isset($userDetails)){
                        return response()->json(array('error' => false, 'message' => 'Signup Successfully', 'data' => $userDetails), 202);
                    }
                }
            }
            
        } catch (\Exception $e) {
            return response()->json(array('error' => true, 'message' => $e->getMessage()), 200);
        }
    }
    
    /**
     * POST METHOD
     * Sign in using email and password.
     * @param Request $request
     * @return type
     */
    public function Login(Request $request) {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                    'email' => 'required',
                    'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
            } else {
                $userDetails = User::where('email', $request->email)->first();
                if (!empty($userDetails)) {
                    //match password
                    if (!Hash::check($request->password, $userDetails->password)) {
                        return response()->json(array('error' => true, 'message' => 'Incorrect Password'), 200);
                    }else{
                        return response()->json(array('error' => false, 'message' => 'Login Successfully', 'data' => $userDetails), 202); 
                    } 
                } else {
                    return response()->json(array('error' => true, 'message' => 'Invalid Email'), 200);
                }
            }
        } catch (\Exception $e) {
            return response()->json(array('error' => true, 'message' => $e->getMessage()), 200);
        } 
    }

    /**
     * POST METHOD
     * Sign in using email and password with JWT authentication.
     * @param Request $request
     * @return type
     */
    public function LoginJWT(Request $request) {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                    'email' => 'required',
                    'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
            } else {
                $credentials = $request->only(['email', 'password']);
                    if (!$token = JWTAuth::attempt($credentials)) {
                        return response()->json(['message' => 'Invalid Credentials', 'error' => true], 200);
                    }
                $userDetails = User::where('email', '=', $request->input('email'))->first();
                $userDetails->jwt_token = $token;
                return response()->json(array('error' => false, 'message' => 'Login Successfully', 'data' => $userDetails), 202); 

            }
            
        } catch (\Exception $e) {
            return response()->json(array('error' => true, 'message' => $e->getMessage()), 200);
        } 
    }
    
    /**
     * Logout
     * @param Request $request
     * @return type
     */
    public function Logout(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
                    'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
        } else {
            User::where("id", $request->user_id)->update(["device_token" => null]);
            return response()->json(array('error' => false, 'message' => 'Logout Successfully', 'data' => ''), 202);
        }
    }

}
