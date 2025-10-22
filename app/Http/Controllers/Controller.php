<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Convenia API",
 *     version="1.0.0",
 *     description="API para gerenciamento de colaboradores",
 *     @OA\Contact(
 *         email="eu@jarbas.dev"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum",
 *     description="Autenticação via token Sanctum"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
