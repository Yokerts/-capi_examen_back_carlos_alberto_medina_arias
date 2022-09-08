<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'cors'], function () {

    /*
     * Webservices catalogos GET
     * ----------------------------------------------------------------------------------------------------------------
     * */

    Route::get('cat/config', 'CAT\CatController@cat_config');
    Route::get('cat/sexo', 'CAT\CatController@cat_sexo');

    /*
     * Webservices del sistema
     * ----------------------------------------------------------------------------------------------------------------
     * */

    Route::post('_Usuarios_Datos', 'SIS\UserController@listar_usuarios');
});
