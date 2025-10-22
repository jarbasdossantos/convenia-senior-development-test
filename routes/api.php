<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollaboratorController;

Route::post('login', [AuthController::class, 'login']);

Route::get('docs', function () {
    return response()->json([
        'message' => 'Documentação da API',
        'swagger_ui' => url('api/documentation'),
        'openapi_json' => url('api/documentation.json'),
    ]);
});
