<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->save();

        return response()->json([
            'message' => 'Successfully logged in.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ],200);
    }

    public function passwordreset(Request $request){

        $userId = auth()->id();
    
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        $user = User::find($userId);

        $user->password = Hash::make($request->password);
        $confirmpassword = $request->confirmpassword;

        if($request->password != $confirmpassword){
            return response()->json(['message' => 'Password mismatch!'],401);
        }

        $user->save();

        return response()->json(['message' => 'Successfully updated the password!'],201);

    }
    

    public function signup(Request $request){
        $user = new User();

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $confirmpassword = $request->confirmpassword;

        if($request->password != $confirmpassword){
            return response()->json(['message' => 'Password mismatch!'],401);
        }

        $user->phone = $request->phone;
        $user->address = $request->address;
    
        $user->save();

        return response()->json(['message' => 'User registered successfully!'],201);

    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out'],200);
    }
}
