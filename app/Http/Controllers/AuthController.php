<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      title="My Laravel API",
 *      version="1.0.0",
 *      description="API documentation for my application"
 * )
 *
 * @OA\Server(
 *      url="http://localhost",
 *      description="API Server"
 * )
 */

class AuthController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/login",
 *     summary="User Login",
 *     description="Authenticate user and return access token.",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged in.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Successfully logged in."),
 *             @OA\Property(property="access_token", type="string", example="2|zgdcWwi46xw4e8CGnUPAo03mmeYnZqakBnrSlMDja2616e1c"),
 *             @OA\Property(property="token_type", type="string", example="Bearer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid credentials")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}, "password": {"The password field is required."}})
 *         )
 *     )
 * )
 */
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


/**
 * @OA\Post(
 *     path="/api/passwordreset",
 *     summary="Reset User Password",
 *     description="Allows an authenticated user to reset their password.",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"password", "password_confirmation"},
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Successfully updated the password!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Successfully updated the password!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized user",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized user.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object", example={
 *                 "password": {"The password field is required."}, 
 *                 "password_confirmation": {"The password confirmation does not match."}
 *             })
 *         )
 *     )
 * )
 */

    public function passwordreset(Request $request){

        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
           'password' => 'required|string|min:6|confirmed', 
           'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        $user = User::find($userId);

        $user->password = Hash::make($request->password);
        $confirmpassword = $request->password_confirmation;

        if($request->password != $confirmpassword){
            return response()->json(['message' => 'Password mismatch!'],401);
        }

        $user->save();

        return response()->json(['message' => 'Successfully updated the password!'],201);

    }

    /**
 * @OA\Post(
 *     path="/api/signup",
 *     summary="User Registration",
 *     description="Registers a new user and returns a success message.",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"first_name", "last_name", "email", "password", "password_confirmation", "phone", "address"},
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="securePass123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="securePass123"),
 *             @OA\Property(property="phone", type="string", example="1234567890"),
 *             @OA\Property(property="address", type="string", example="123 Main Street, City, Country")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User registered successfully!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Password mismatch!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password mismatch!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object", example={
 *                 "email": {"The email field is required."}, 
 *                 "password": {"The password must be at least 6 characters long."}
 *             })
 *         )
 *     )
 * )
 */

    public function signup(Request $request){
        $user = new User();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed', 
            'password_confirmation' => 'required|string|same:password',
            'phone' => 'required|digits_between:10,15', 
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $confirmpassword = $request->password_confirmation;

        if($request->password != $confirmpassword){
            return response()->json(['message' => 'Password mismatch!'],401);
        }

        $user->phone = $request->phone;
        $user->address = $request->address;
    
        $user->save();

        return response()->json(['message' => 'User registered successfully!'],201);

    }

    /**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="User Logout",
 *     description="Logs out the authenticated user by revoking all tokens.",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Successfully logged out")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out'],200);
    }
}
