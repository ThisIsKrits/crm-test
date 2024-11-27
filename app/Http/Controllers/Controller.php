<?php

namespace App\Http\Controllers;

 /**
        * @OA\Info(
        *       version="1.0.0", title="CRM Test Documentation", description="Docmentation for CRM Test",
        *      @OA\Contact( email="gilaarief@gmail.com")
        * )
        *
        * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="CRM Test Server")
        *     @OA\SecurityScheme(
        *           securityScheme="bearerAuth", type="http",  name="Authorization",
        *           scheme="bearer", bearerFormat="jwt", in="header",
        *     ),
    */
abstract class Controller
{

}
