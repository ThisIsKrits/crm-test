<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
        * @OA\Post(
        *     path="/login",
        *     summary="login for user",
        *     tags={"Auth"},
        *     @OA\RequestBody(
        *         required=true,
        *         @OA\MediaType(
        *              mediaType = "application/x-www-form-urlencoded",
        *              @OA\Schema(
        *                  required = {"email","password"},
        *                  @OA\Property(property="email", type="string", example="user@example.com", description="email user"),
        *                  @OA\Property(property="password", type="string",example="password", description="password user"),
        *              )
        *         )
        *     ),
        *     @OA\Response(
        *         response=201,
        *         description="login successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="token", type="string", example="2|dNw9zFaXsAvBPGqHcoqE1V5kuXln1yPv"),
        *             @OA\Property(property="user", type="object",
        *                 @OA\Property(property="id", type="uuid", example="9d0733ea-cb7a-4d5b-b380-2187f70128d2"),
        *                 @OA\Property(property="email", type="string", example="user@example.com"),
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=422,
        *         description="Validation error",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="The email field is required."),
        *             @OA\Property(property="errors", type="object",
        *                  @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
        *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=400,
        *         description="Bad Request",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Bad Request: The request cannot be fulfilled due to bad syntax.")
        *         )
        *     ),
        *     @OA\Response(
        *         response=500,
        *         description="Server Error",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Internal Server Error: Something went wrong.")
        *         )
        *     ),
        * )
    */
    public function login(Request $request)
    {
        $validations    = Validator::make($request->all(), [
            'password'  =>  'required',
        ],[
            'password.required' => 'Password tidak boleh kosong!',
        ]);

        if ($validations->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validations->errors()->toArray(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $email      = $request->email;
        $password   = $request->password;

        $user   = User::where('email',$email)->first();

        if(!Auth::attempt(['email' => $email, 'password' => $password]))
        {
            return response()->json([
                'success'   => false,
                'message'   => 'Username atau Password salah!',
            ]);
        }

        $token  = JWTAuth::attempt(['email' => $email, 'password' => $password]);

        return response()->json([
            'success'   => true,
            'message'   => 'Login successfully',
            'token'     => $token,
            'type'      => 'Bearer Token'
        ]);
    }

    /**
         * @OA\Post(
         *     path="/change-password",
         *     summary="Change User Password",
         *     tags={"Profile"},
         *     security={{"bearerAuth": {}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *              mediaType="application/x-www-form-urlencoded",
         *              @OA\Schema(
         *                  required={"old_password", "password", "password_confirmation"},
         *                  @OA\Property(property="old_password", type="string", example="", description="User's current password"),
         *                  @OA\Property(property="password", type="string", example="", description="New password for the user"),
         *                  @OA\Property(property="password_confirmation", type="string", example="", description="Confirm the new password"),
         *              )
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Password successfully updated",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Password successfully updated")
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Old password does not match",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=false),
         *             @OA\Property(property="message", type="string", example="Old password doesn't match")
         *         )
         *     ),
         *     @OA\Response(
         *         response=422,
         *         description="Validation error",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=false),
         *             @OA\Property(property="message", type="object",
         *                 @OA\Property(property="old_password", type="array", @OA\Items(type="string", example="The old password cannot be empty!")),
         *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="The new password must have at least 8 characters.")),
         *                 @OA\Property(property="password_confirmation", type="array", @OA\Items(type="string", example="New password and confirmation don't match."))
         *             )
         *         )
         *     ),
         * )
    */
    public function change_password(Request $request)
    {
        $validations    = Validator::make($request->all(), [
            'old_password'      => 'required',
            'password'          => 'required',
        ],[
            'old_password.required' => 'Password lama tidak boleh kosong!',
            'password.required'     => 'Password baru tidak boleh kosong!',
        ]);

        if($validations->fails())
        {
            return response()->json([
                'success'   => false,
                'message'   => $validations->errors()->toArray(),
            ], 422);
        }

        if(!Hash::check($request->old_password, Auth::user()->password))
        {
            return response()->json([
                'success'   => false,
                'message'   => 'Old password not match!'
            ]);
        }

        $password   = Auth::user();

        $password->update([
            'password'  => bcrypt($request->password)
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Password was successfully updated'
        ]);
    }

    /**
         * @OA\Post(
         *     path="/logout",
         *     summary="Logout User",
         *     tags={"Auth"},
         *     @OA\Response(
         *         response=200,
         *         description="User successfully logged out",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Logged out")
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Unauthorized, user not logged in",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Unauthorized")
         *         )
         *     ),
         * )
    */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success'   => true,
            'message' => 'Logged out'
        ]);
    }
}
