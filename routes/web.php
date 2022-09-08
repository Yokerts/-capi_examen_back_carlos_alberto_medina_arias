<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('excel', 'EXPORT\DemoExportPDFController@excel');
Route::get('pdf', 'EXPORT\DemoExportPDFController@pdfexcel');
Route::get('ticket', 'EXPORT\DemoExportPDFController@ticket');


Route::get('v/{key}', 'EXPORT\ExportarVentasController@ventas');

