<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"User"},
    *     security={{"bearerAuth": {}}},
    *     @OA\Parameter(
    *         name="search",
    *         in="query",
    *         description="Search keyword to filter users.",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="sortBy",
    *         in="query",
    *         description="Column to sort users by (default: name).",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="orderDirection",
    *         in="query",
    *         description="Sort direction (asc or desc, default: asc).",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="List of users",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="success", type="boolean"),
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="data", type="object")
    *         )
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Access denied",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="success", type="boolean"),
    *             @OA\Property(property="message", type="string")
    *         )
    *     )
    * )
    */

     /**
     * @OA\Get(
     *     path="/user-employee",
     *     summary="Get all users",
     *     tags={"User"},
    *     security={{"bearerAuth": {}}},
    *     @OA\Parameter(
    *         name="search",
    *         in="query",
    *         description="Search keyword to filter users.",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="sortBy",
    *         in="query",
    *         description="Column to sort users by (default: name).",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="orderDirection",
    *         in="query",
    *         description="Sort direction (asc or desc, default: asc).",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="List of users",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="success", type="boolean"),
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="data", type="object")
    *         )
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Access denied",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="success", type="boolean"),
    *             @OA\Property(property="message", type="string")
    *         )
    *     )
    * )
    */
    public function index()
    {
        $user = Auth::user();
        $roleName = $user->getRole->name ?? null;

        $allowedRoles = match ($roleName) {
            'superadmin' => ['employee', 'manager'],
            'manager'    => ['manager', 'employee'],
            'employee'   => ['employee'],
            default      => [],
        };

        if (empty($allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $datas = User::whereHas('getRole', function ($query) use ($allowedRoles) {
            $query->whereIn('name', $allowedRoles);
        });

        if ($roleName !== 'superadmin') {
            $datas->whereHas('getCompany', function($query) use ($user) {
                $query->where('company_id', $user->getCompany->id);
            });
        }

        if ($search = request()->search) {
            $datas->bySearch($search);
        }

        $sortBy = request()->get('sortBy', 'name');
        $orderDirection = request()->get('orderDirection', 'asc');


        $datas = $datas->join('employees', 'users.id', '=', 'employees.user_id')
                   ->orderBy('employees.' . $sortBy, $orderDirection)
                   ->select('users.*', 'employees.name as employee_name')
                   ->paginate(2);

        return response()->json([
            'success' => true,
            'message' => 'Data successfully retrieved',
            'data' => $datas,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/users",
     *     summary="Create a new user",
     *     tags={"User"},
    *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "phone", "address", "role_id", "company_id"},
     *             @OA\Property(property="name", type="string", description="User's name"),
     *             @OA\Property(property="email", type="string", description="Unique email for the user"),
     *             @OA\Property(property="password", type="string", description="Password for the user"),
     *             @OA\Property(property="phone", type="string", description="Phone number (10-16 digits)"),
     *             @OA\Property(property="address", type="string", description="User's address"),
     *             @OA\Property(property="role_id", type="integer", description="Role ID for the user"),
     *             @OA\Property(property="company_id", type="integer", description="Company ID for the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             example={
     *                 "success": false,
     *                 "message": "Validation errors occurred.",
     *                 "errors": {
     *                     "name": "The name field is required.",
     *                     "email": "The email field is required and must be a valid email address.",
     *                     "phone": "The phone number must be between 10 and 16 digits.",
     *                     "password": "The password must be at least 8 characters long.",
     *                     "role_id": "The role field is required.",
     *                     "company_id": "The company_id field is required.",
     *                 }
     *             }
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:employees',
            'email' => 'required|unique:users',
            'password'  => 'required',
            'phone'     => 'required|numeric|digits_between:10,16',
            'address'   => 'required',
            'role_id'   => 'required',
            'company_id'    => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors()->toArray(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $setUser = User::setData($request);
        $user = User::create($setUser);

        $setEmployee = Employee::setData($request);
        $setEmployee['user_id'] = $user->id;
        $employee   = Employee::create($setEmployee);

        $user->load('getEmployee');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user,
        ]);
    }

        /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get user details",
     *     tags={"User"},
         *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
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

    public function show(string $id)
    {
        $data = User::with('getCompany','getEmployee')->find($id);

        return response()->json([
            'success'   => true,
            'message'   => 'Data successfully retrieved',
            'data'      => $data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update user details",
     *     tags={"User"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone"},
     *             @OA\Property(property="name", type="string", description="User's name"),
     *             @OA\Property(property="email", type="string", description="User's email"),
     *             @OA\Property(property="phone", type="string", description="User's phone number"),
     *             @OA\Property(property="password", type="string", description="User's password (optional, min 8 characters)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             example={
     *                 "success": false,
     *                 "message": "Validation errors occurred.",
     *                 "errors": {
     *                     "name": "The name field is required.",
     *                     "email": "The email field is required and must be a valid email address.",
     *                     "phone": "The phone number must be between 10 and 16 digits.",
     *                     "password": "The password must be at least 8 characters long."
     *                 }
     *             }
     *         )
     *     ),
     * )
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(),[
            'name'  => 'required|unique:employees,name,' . $user->getEmployee->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password'=> 'nullable|min_digits:8',
            'phone'  => 'required|digits_between:10,16'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors()->toArray(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = User::setData($request);
        $data['role_id'] = $request->role_id ?? $user->role_id;
        $user->update($data);

        $employee = $user->getEmployee;
        $employeeData = Employee::setData($request);
        $employee->update($employeeData);

        $user->load('getEmployee','getRole');

        return response()->json([
            'success'   => true,
            'message'   => 'Data successfully update',
            'data'      => $user
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"User"},
         *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success'   => true,
            'message'   => 'Data successfully delete',
        ]);
    }
}
