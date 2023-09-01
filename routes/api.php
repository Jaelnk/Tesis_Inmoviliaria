<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\UserController;

Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login']);



Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::put('/perfil', [AuthController::class, 'perfil']);
    Route::put('/updatePassword', [AuthController::class, 'updatePassword']);
    Route::post('logout', [AuthController::class, 'logout']);

//role administrador
    Route::get('admin', [AdminController::class, 'adminProfile']);
    Route::post('admin/registerEmployee', [AdminController::class, 'registerEmployee']);

    //Route::post('test', [AdminController::class, 'uploadImage']);

    //pedidos
    Route::get('pedidos', [AdminController::class, 'showPedidos']);
    Route::get('pedido/{id}', [AdminController::class, 'showPedido']);
    Route::put('pedidos/{id}', [AdminController::class, 'putState']);
    Route::put('pedido/{id}', [AdminController::class, 'comentarioAdmin']);

    //CRUD CATEGORIAS
    Route::get('admin/categories', [AdminController::class, 'indexCat']);
    Route::post('admin/categories', [AdminController::class, 'storeCat']);
    Route::put('admin/categories/{id}', [AdminController::class, 'updateCat']);
    Route::delete('admin/categories/{id}', [AdminController::class, 'destroyCat']);

    //CRUD SERVICIOS
    Route::get('admin/categories/{id}/services', [AdminController::class, 'showCat']);
    Route::post('admin/categories/{id}/newServ', [AdminController::class, 'storeServ']);
    Route::get('admin/service/{id}', [AdminController::class, 'showServ']);
    Route::put('admin/service/{id}', [AdminController::class, 'updateServ']);
    Route::delete('admin/service/{id}', [AdminController::class, 'destroyServ']);

//role empleado
    Route::get('employee', [EmployeeController::class, 'employeeProfile']);
    Route::get('emp/pedidos', [EmployeeController::class, 'pedidos']);
    Route::put('emp/pedido/{id}', [EmployeeController::class, 'finalizarPedido']);

//role cliente
    Route::get('profile', [UserController::class, 'profile']);
    Route::get('cli/categories', [UserController::class, 'indexCat']);
    Route::get('cli/categories/{id}/services', [UserController::class, 'showCat']);
    Route::post('newPedido', [UserController::class, 'newPedido']);
    Route::get('cli/pedidos', [UserController::class, 'showPedido']);
    Route::put('cli/pedido/{id}', [UserController::class, 'comentarPedido']);
    Route::post('cotizarpedido', [UserController::class, 'cotizar']);




});



/* Route::middleware(['auth', 'role:1'])->group(function () {
    Route::get('admin', [AdminController::class, 'adminProfile']);
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user', [UserController::class, 'index']);
});
 */


/* Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::get('user-profile', [AuthController::class, 'userProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'auth'], function () {
    // Rutas de tu CRUD aquí
    Route::get('admin-profile', [AdminController::class, 'adminProfile']);
    Route::post('logout', [AdminController::class, 'logout']);
}); */
