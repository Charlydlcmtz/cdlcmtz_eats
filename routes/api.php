<?php

use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors']], routes: function (): void {
    //Usuarios
    Route::post('/usuarios/list-usuarios', [App\Http\Controllers\usuario\UsuarioController::class, 'list_usuarios'])->name('list_usuarios');
    Route::post('usuarios/usuario-add', [App\Http\Controllers\usuario\UsuarioController::class, 'usuarios_add'])->name('usuarios_add');
    Route::post('/register', [App\Http\Controllers\usuario\UsuarioController::class, 'register'])->name('register');
    Route::post('/login', [App\Http\Controllers\usuario\UsuarioController::class, 'login'])->name('login');
    Route::post('/check-token', [App\Http\Controllers\usuario\UsuarioController::class, 'check_renew_token'])->name('check_renew_token');
    Route::get('/usuarios/get-usuario/{id_usuario}', [App\Http\Controllers\usuario\UsuarioController::class, 'getUsuarioById'])->name('getUsuarioById');
    Route::get('/usuarios/search-usuario/{nombre_usuario?}', [App\Http\Controllers\usuario\UsuarioController::class, 'getUsuariosearch'])->name('getUsuariosearch');
    Route::post('/usuarios/usuario-update/{id_usuario}', [App\Http\Controllers\usuario\UsuarioController::class, 'update_usuario'])->name('update_usuario');
    Route::delete('/usuarios/usuario-delete/{id_usuario}', [App\Http\Controllers\usuario\UsuarioController::class, 'delete_usuario'])->name('delete_usuario');
    Route::post('/usuarios/change-password', [App\Http\Controllers\usuario\UsuarioController::class, 'change_password'])->name('change_password');

    //Roles
    Route::post('/roles/list-roles', [App\Http\Controllers\usuario\UsuarioController::class, 'list_roles'])->name('list_roles');

    //Empresas
    Route::post('/empresa/list-empresas', [App\Http\Controllers\empresa\EmpresaController::class, 'list_empresas'])->name('list_empresas');
    Route::post('/empresa/empresa-add', [App\Http\Controllers\empresa\EmpresaController::class, 'register_company'])->name('register_company');
    Route::post('/register-company', [App\Http\Controllers\empresa\EmpresaController::class, 'register_company'])->name('register_company');
    Route::get('/empresas/get-empresa/{id_empresa}', [App\Http\Controllers\empresa\EmpresaController::class, 'getEmpresaById'])->name('getEmpresaById');
    Route::get('/empresas/search-empresa/{nombre_empresa?}', [App\Http\Controllers\empresa\EmpresaController::class, 'getEmpresasearch'])->name('getEmpresasearch');
    Route::post('/empresas/empresa-update/{id_empresa}', [App\Http\Controllers\empresa\EmpresaController::class, 'update_empresa'])->name('update_empresa');
    Route::delete('/empresas/empresa-delete/{id_empresa}', [App\Http\Controllers\empresa\EmpresaController::class, 'delete_empresa'])->name('delete_empresa');

    //Menu
    Route::get('/menu/food/{id_comida}', [App\Http\Controllers\comida\ComidaController::class, 'food'])->name('food');
    Route::post('/menu/food-add', [App\Http\Controllers\comida\ComidaController::class, 'add_food'])->name('add_food');
    Route::post('/menu/list-food', [App\Http\Controllers\comida\ComidaController::class, 'list_food'])->name('list_food');
    Route::get('/menu/get-food/{id_comida}', [App\Http\Controllers\comida\ComidaController::class, 'getFoodById'])->name('getFoodById');
    Route::get('/menu/search-food/{nombre_comida?}', [App\Http\Controllers\comida\ComidaController::class, 'getsearch'])->name('getsearch');
    Route::post('/menu/food-update/{id_comida}', [App\Http\Controllers\comida\ComidaController::class, 'update_food'])->name('update_food');
    Route::post('/menu/food-update-movil/{id_comida}', [App\Http\Controllers\comida\ComidaController::class, 'update_food_movil'])->name('update_food_movil');
    Route::post('/menu/food-file', [App\Http\Controllers\comida\ComidaController::class, 'update_food_file'])->name('update_food_file');
    Route::delete('/menu/food-delete/{id_comida}', [App\Http\Controllers\comida\ComidaController::class, 'delete_food'])->name('delete_food');

    //Pedidos
    Route::post('/menu/list-pedidos', [App\Http\Controllers\comida\ComidaController::class, 'list_orders'])->name('list_orders');
    Route::post('/menu/user-pedido', [App\Http\Controllers\comida\ComidaController::class, 'user_order'])->name('user_order');
    Route::post('/menu/user-historial', [App\Http\Controllers\comida\ComidaController::class, 'user_historial'])->name('user_historial');
    Route::post('/menu/create-order', [App\Http\Controllers\comida\ComidaController::class, 'create_order'])->name('create_order');
    Route::patch('/menu/pedido-update/{id_pedido}', [App\Http\Controllers\comida\ComidaController::class, 'update_order'])->name('update_order');
    Route::patch('/menu/pedido-cancel', [App\Http\Controllers\comida\ComidaController::class, 'cancel_order'])->name('cancel_order');


    //Tipo menu
    Route::post('/menu-type/list-menu-type', [App\Http\Controllers\comida\ComidaController::class, 'list_type_menu'])->name('list_type_menu');
    Route::post('/menu-type/menu-add', [App\Http\Controllers\comida\ComidaController::class, 'add_type_menu'])->name('add_type_menu');
    Route::get('/menu/get-menu/{id_menu}', [App\Http\Controllers\comida\ComidaController::class, 'getMenuById'])->name('getMenuById');
    Route::get('/menu/search-menu/{nombre_menu?}', [App\Http\Controllers\comida\ComidaController::class, 'getMenusearch'])->name('getMenusearch');
    Route::post('/menu/menu-type-update/{id_menu}', [App\Http\Controllers\comida\ComidaController::class, 'update_menu'])->name('update_menu');
    Route::delete('/menu/menu-type-delete/{id_menu}', [App\Http\Controllers\comida\ComidaController::class, 'delete_menu'])->name('delete_menu');

    //Codigo verificador
    Route::post('/codigo', [App\Http\Controllers\usuario\UsuarioController::class, 'getCodigoValidador'])->name('getCodigoValidador');
});
