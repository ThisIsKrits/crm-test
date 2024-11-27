<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/companies",
     *     summary="Get all companies",
     *     tags={"Company"},
    *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keyword to filter companies.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="Column to sort companies by (default: name).",
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
     *         description="List of companies",
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
        $orderDirection = request()->get('orderDirection', 'asc');
        $datas = Company::bySearch(request()->search)
                ->orderBy('name', $orderDirection)
                ->paginate(2);

        return response()->json([
            'success'   => true,
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
     *     path="/api/company",
     *     summary="Create a new company",
     *     tags={"Company"},
    *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone"},
     *             @OA\Property(property="name", type="string", description="Company's name"),
     *             @OA\Property(property="email", type="string", description="Unique email for the user"),
     *             @OA\Property(property="phone", type="string", description="Phone number (10-16 digits)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Company created successfully",
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
     *                 }
     *             }
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:companies',
            'email' => 'required|unique:companies',
            'phone'     => 'required|numeric|digits_between:10,16',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors()->toArray(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $setData = Company::setData($request);
        $company = Company::create($setData);

        $companySlug = str_replace(' ', '', strtolower($request->name));

        $role = Role::where('name', '=', 'manager')->first();

        $setUser = User::setData($request);
        $setUser['email'] = 'manager@' . $companySlug.'.com';
        $setUser['password'] = bcrypt('manager123');
        $setUser['role_id'] = $role->id;
        $setUser['company_id'] = $company->id;
        $user    = User::create($setUser);

        $setEmployee = Employee::setData($request);
        $setEmployee['name'] = 'manager '. $request->name;
        $setEmployee['user_id'] = $user->id;
        $employee = Employee::create($setEmployee);

        $data = $company->load('getUser','getUser.getEmployee');

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully.',
            'data' => $data,
        ]);
    }

       /**
     * @OA\Get(
     *     path="/api/company/{id}",
     *     summary="Get Company details",
     *     tags={"Company"},
         *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Company",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company details retrieved successfully",
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
        $data = Company::with('getUser','getUser.getEmployee')->findOrFail($id);

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
     *     path="/company/{id}",
     *     summary="Update Company details",
     *     tags={"Company"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Company to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone"},
     *             @OA\Property(property="name", type="string", description="Company's name"),
     *             @OA\Property(property="email", type="string", description="Company's email"),
     *             @OA\Property(property="phone", type="string", description="Company's phone number"),
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
     *                 }
     *             }
     *         )
     *     ),
     * )
     */
    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:companies,email,' . $company->id,
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'phone'     => 'required|numeric|digits_between:10,16',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors()->toArray(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = Company::setData($request);
        $company->update($data);
        $updatedData = $company->getAttributes();

        return response()->json([
            'success'   => true,
            'message'   => 'Data successfully update',
            'data'      => $updatedData
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/company/{id}",
     *     summary="Delete a Company",
     *     tags={"Company"},
         *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Company to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'success'   => true,
            'message'   => 'Data successfully delete',
        ]);
    }
}
