<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateProfileController extends Controller
{

     /**
     * @OA\Get(
     *     path="/profile",
     *     summary="Get user details",
     *     tags={"Profile"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $id = Auth::user()->id;
        $user = User::with('getEmployee','getCompany','getRole')->findOrFail($id);

        return response()->json([
            'success'   => true,
            'message'   => 'Data was successfully retrieved data',
            'data'      => $user
        ]);
    }

    /**
 * @OA\Post(
 *     path="/profile-update",
 *     summary="Update profile",
 *     tags={"Profile"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", description="Profile's name"),
 *             @OA\Property(property="address", type="string", description="Profile's address"),
 *             @OA\Property(property="phone", type="string", description="Profile's phone number")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     )
 * )
 */

    public function update_profile(Request $request)
    {
        $id = Auth::user()->id;
        $profile = Employee::where('user_id',$id)->first();
        if (!$profile) {
           return response()->json([
            'success'   => false,
            'message'   => 'employee not found'
           ]);
        }

        $profile->name = $request->name;
        $profile->phone = $request->phone;
        $profile->address = $request->address;

        $profile->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Profile was update successfully',
            'data'      => $profile
        ]);
    }
}
