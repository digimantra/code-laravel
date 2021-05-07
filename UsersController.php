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

class UsersController extends Controller {

    /**
     * Get user information by  using unique id
     * @param Request $request
     * @return type
     */
    public function GetUser(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
                    'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
        } else {
            //Get user
            $user_data = User::where("id", $request->user_id)->first();
            return response()->json(array('error' => false, 'message' => 'Account Get Successfully', 'data' => $user_data), 202);
        }
    }

    /**
     * Update user informations
     * @param Request $request
     * @return type
     */
    public function UpdateUser(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
                    'user_id' => 'required|exists:users,id',
                    'name' => 'required',
                    'email' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
        } else {
            //check email is exist or not
            if(User::where("email", $request->email)->where("id", '!=', $request->user_id)->exists()){
                return response()->json(array('error' => true, 'message' => 'Email already exist', 'data' => []), 200);
            }else{
                User::where("id", $request->user_id)->update([
                    "name" => $request->name,
                    "email" => $request->email,
                ]);
                //get updated user info
                $user_data = User::where("id", $request->user_id)->first();
                return response()->json(array('error' => false, 'message' => 'User Updated Successfully', 'data' => $user_data), 202);
            }
        }
    }

    /**
     * Delete user account
     * @param Request $request
     * @return type
     */
    public function DeleteAccount(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
                    'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(array('error' => true, 'message' => $validator->errors()->first()), 200);
        } else {
            //Delete user
            User::where("id", $request->user_id)->delete();
            return response()->json(array('error' => false, 'message' => 'Account Deleted Successfully', 'data' => ''), 202);
        }
    }
}
