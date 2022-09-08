<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\CAT\CatController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RolesPermisosController extends Controller
{
    public function lista_menu_sub_menu(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $id_cat_tipo_usuario = $data_request['data']['id_cat_tipo_usuario'];

                $cat_tipo_usuario = CatController::cat_tipo_usuario(true, $Usr->id_cat_tipo_usuario);

                if ($Usr->id_cat_tipo_usuario === 1) {
                    $menu_sub_menu = DB::table('menu')
                        ->select(
                            'menu.*',
                            'acceso_menu.id_acceso_menu',
                            'acceso_menu.acceso_menu'
                        )
                        ->leftJoin('acceso_menu', function ($leftJoin) use ($id_cat_tipo_usuario) {
                            $leftJoin->on('acceso_menu.id_menu', '=', 'menu.id_menu')
                                ->where('acceso_menu.id_cat_tipo_usuario', '=', $id_cat_tipo_usuario);
                        })
                        ->orderBy('id_menu')
                        ->get();
                } else {
                    $menu_sub_menu = DB::table('menu')
                        ->select(
                            'menu.*',
                            'acceso_menu.id_acceso_menu',
                            'acceso_menu.acceso_menu'
                        )
                        ->leftJoin('acceso_menu', function ($leftJoin) use ($id_cat_tipo_usuario) {
                            $leftJoin->on('acceso_menu.id_menu', '=', 'menu.id_menu')
                                ->where('acceso_menu.id_cat_tipo_usuario', '=', $id_cat_tipo_usuario);
                        })
                        ->where('menu.id_menu', '<>', 1)
                        ->get();
                }

                if ($menu_sub_menu) {
                    foreach ($menu_sub_menu as $key => $row) {
                        $sub_menu = DB::table('sub_menu')
                            ->select(
                                'sub_menu.*',
                                'acceso_sub_menu.id_acceso_sub_menu',
                                'acceso_sub_menu.acceso_sub_menu'
                            )
                            ->leftJoin('acceso_sub_menu', function ($leftJoin) use ($id_cat_tipo_usuario) {
                                $leftJoin->on('acceso_sub_menu.id_sub_menu', '=', 'sub_menu.id_sub_menu')
                                    ->where('acceso_sub_menu.id_cat_tipo_usuario', '=', $id_cat_tipo_usuario);
                            })
                            ->where('sub_menu.id_menu', '=', $row->id_menu)
                            ->get();
                        $menu_sub_menu[$key]->sub_menu = $sub_menu ?? array();
                    }
                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados.";
                    $data = array(
                        "menu_sub_menu" => $menu_sub_menu,
                        "cat_tipo_usuario" => $cat_tipo_usuario,
                    );
                    DB::commit();
                } else {
                    $flag_request = true;
                    $status = 400;
                    $message = "No se encontraron datos.";
                    $data = array(
                        "menu_sub_menu" => array(),
                        "cat_tipo_usuario" => $cat_tipo_usuario,
                    );
                    DB::rollback();
                }

                $response = [
                    "success" => $flag_request,
                    "status" => $status,
                    "message" => $message,
                    "data" => $data
                ];

            } catch (\Exception $e) {
                DB::rollback();
                $log = $this->ErrorTransaction($e, $request);
                return $log;
            }
        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token de acceso no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

    public function menu_acceso_menu(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_acceso_menu' => '',
                'id_menu' => 'required',
                'id_cat_tipo_usuario' => 'required',
                'acceso_menu' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $acceso_menu = DB::table('acceso_menu')
                        ->select('acceso_menu.*')
                        ->where('acceso_menu.id_acceso_menu', '=' , $data_request['data']['id_acceso_menu'])
                        ->where('acceso_menu.id_menu', '=' , $data_request['data']['id_menu'])
                        ->first();

                    $insert_data = false;
                    $update_data = false;

                    if($acceso_menu) {
                        $update_data = DB::table('acceso_menu')
                            ->where('acceso_menu.id_acceso_menu', $data_request['data']["id_acceso_menu"])
                            ->update([
                                "id_menu" => $data_request['data']['id_menu'],
                                "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                "acceso_menu" => $data_request['data']['acceso_menu'],
                                "updated_at" => $this->DATETIME()
                            ]);
                    } else {
                        $insert_data = DB::table('acceso_menu')
                            ->insertGetId([
                                "id_menu" => $data_request['data']['id_menu'],
                                "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                "acceso_menu" => $data_request['data']['acceso_menu'],
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                    }

                        if ($insert_data || $update_data) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos guardados con éxito.";
                            $data = $insert_data;
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al guardar los datos.";
                            $data = array();
                            DB::commit();
                        }

                    $response = [
                        "success" => $flag_request,
                        "status" => $status,
                        "message" => $message,
                        "data" => $data
                    ];

                } catch (\Exception $e) {
                    DB::rollback();
                    $log = $this->ErrorTransaction($e, $request);
                    return $log;
                }
            } else {
                $response = [
                    "success" => $flag_request,
                    "status" => 400,
                    "message" => "No se encontraron datos.",
                    "errors" => $validator->errors()->messages()
                ];
            }

        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token de acceso no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

    public function menu_acceso_sub_menu(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_acceso_sub_menu' => '',
                'id_sub_menu' => 'required',
                'id_cat_tipo_usuario' => 'required',
                'acceso_sub_menu' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $acceso_sub_menu = DB::table('acceso_sub_menu')
                        ->select('acceso_sub_menu.*')
                        ->where('acceso_sub_menu.id_acceso_sub_menu', '=' , $data_request['data']['id_acceso_sub_menu'])
                        ->where('acceso_sub_menu.id_sub_menu', '=' , $data_request['data']['id_sub_menu'])
                        ->first();

                    $insert_data = false;
                    $update_data = false;

                    if($acceso_sub_menu) {
                        $update_data = DB::table('acceso_sub_menu')
                            ->where('acceso_sub_menu.id_acceso_sub_menu', '=', $data_request['data']["id_acceso_sub_menu"])
                            ->update([
                                "id_sub_menu" => $data_request['data']['id_sub_menu'],
                                "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                "acceso_sub_menu" => $data_request['data']['acceso_sub_menu'],
                                "updated_at" => $this->DATETIME()
                            ]);
                    } else {
                        $insert_data = DB::table('acceso_sub_menu')
                            ->insertGetId([
                                "id_sub_menu" => $data_request['data']['id_sub_menu'],
                                "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                "acceso_sub_menu" => $data_request['data']['acceso_sub_menu'],
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                    }

                        if ($insert_data || $update_data) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos guardados con éxito.";
                            $data = $insert_data;
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al guardar los datos.";
                            $data = array();
                            DB::commit();
                        }

                    $response = [
                        "success" => $flag_request,
                        "status" => $status,
                        "message" => $message,
                        "data" => $data
                    ];

                } catch (\Exception $e) {
                    DB::rollback();
                    $log = $this->ErrorTransaction($e, $request);
                    return $log;
                }
            } else {
                $response = [
                    "success" => $flag_request,
                    "status" => 400,
                    "message" => "No se encontraron datos.",
                    "errors" => $validator->errors()->messages()
                ];
            }

        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token de acceso no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

}
