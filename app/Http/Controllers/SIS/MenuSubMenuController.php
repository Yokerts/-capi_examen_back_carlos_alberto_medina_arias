<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuSubMenuController extends Controller
{
    public function listar_menus(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $data_db = DB::table('menu')
                    ->select('*')
                    ->orderBy('menu.orden', 'ASC')
                    ->get();

                if ($data_db) {
                    foreach ($data_db as $key => $row) {
                        $sub_menu = DB::table('sub_menu')
                            ->where('sub_menu.id_menu', '=', $row->id_menu)
                            ->select('*')
                            ->get();
                        $data_db[$key]->sub_menu = $sub_menu ?? array();
                    }
                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados.";
                    $data = $data_db;
                    DB::commit();
                } else {
                    $flag_request = true;
                    $status = 400;
                    $message = "No se encontraron datos.";
                    $data = array();
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

    public function agregar_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_menu' => '',
                'icono' => 'required',
                'menu' => 'required',
                'orden' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('menu')
                        ->where('menu.menu', $data_request['data']["menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro == null) {
                        $insert_data = DB::table('menu')
                            ->insertGetId([
                                "icono" => $data_request['data']['icono'],
                                "menu" => $data_request['data']['menu'],
                                "orden" => $data_request['data']['orden'],
                                "activo" => $data_request['data']['activo'],
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                        if ($insert_data > 0) {
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
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El menu ya existe.";
                        $data = array();
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

    public function modificar_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_menu' => 'required',
                'icono' => 'required',
                'menu' => 'required',
                'orden' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('menu')
                        ->where('menu.id_menu', $data_request['data']["id_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $update_data = DB::table('menu')
                            ->where('menu.id_menu', $data_request['data']["id_menu"])
                            ->update([
                                "icono" => $data_request['data']['icono'],
                                "menu" => $data_request['data']['menu'],
                                "orden" => $data_request['data']['orden'],
                                "activo" => $data_request['data']['activo'],
                                "updated_at" => $this->DATETIME()
                            ]);
                        if ($update_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos actualizados con éxito.";
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al actualizar los datos.";
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe este registro.";
                        $data = array();
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

    public function eliminar_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_menu' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('menu')
                        ->where('menu.id_menu', $data_request['data']["id_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $delete_data = DB::table('menu')
                            ->where('menu.id_menu', $data_request['data']["id_menu"])
                            ->delete();
                        if ($delete_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos eliminados con éxito.";
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al eliminar los datos.";
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe el registro que intentas eliminar.";
                        $data = array();
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

    public function status_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_menu' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('menu')
                        ->where('menu.id_menu', $data_request['data']["id_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $update_data = DB::table('menu')
                            ->where('menu.id_menu', $data_request['data']["id_menu"])
                            ->update([
                                "activo" => $data_request['data']['activo']
                            ]);
                        if ($update_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            if ($data_request['data']['activo'] == 1) {
                                $message = "El menu se a activado con éxito.";
                            } else {
                                $message = "El menu se a desactivado con éxito.";
                            }
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            if ($existe_registro->activo == $data_request['data']['activo']) {
                                if ($data_request['data']['activo'] == 1) {
                                    $message = "El menu se encuentra activo.";
                                } else {
                                    $message = "El menu se encuentra inactivo.";
                                }
                            } else {
                                if ($data_request['data']['activo'] == 1) {
                                    $message = "Error al activar el menu.";
                                } else {
                                    $message = "Error al desactivar le menu.";
                                }
                            }
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe este registro.";
                        $data = array();
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

    public function agregar_sub_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_sub_menu' => '',
                'id_menu' => 'required',
                'icono' => 'required',
                'sub_menu' => 'required',
                'ruta' => 'required',
                'orden' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('sub_menu')
                        ->where('sub_menu.id_menu', $data_request['data']["id_menu"])
                        ->where('sub_menu.sub_menu', $data_request['data']["sub_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro == null) {
                        $insert_data = DB::table('sub_menu')
                            ->insertGetId([
                                "id_menu" => $data_request['data']['id_menu'],
                                "icono" => $data_request['data']['icono'],
                                "sub_menu" => $data_request['data']['sub_menu'],
                                "ruta" => $data_request['data']['ruta'],
                                "orden" => $data_request['data']['orden'],
                                "activo" => $data_request['data']['activo'],
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                        if ($insert_data > 0) {
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
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El menu ya existe.";
                        $data = array();
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

    public function modificar_sub_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_sub_menu' => 'required',
                'id_menu' => 'required',
                'icono' => 'required',
                'sub_menu' => 'required',
                'ruta' => 'required',
                'orden' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('sub_menu')
                        ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $update_data = DB::table('sub_menu')
                            ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                            ->update([
                                "id_menu" => $data_request['data']['id_menu'],
                                "icono" => $data_request['data']['icono'],
                                "sub_menu" => $data_request['data']['sub_menu'],
                                "ruta" => $data_request['data']['ruta'],
                                "orden" => $data_request['data']['orden'],
                                "activo" => $data_request['data']['activo'],
                                "updated_at" => $this->DATETIME()
                            ]);
                        if ($update_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos actualizados con éxito.";
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al actualizar los datos.";
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe este registro.";
                        $data = array();
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

    public function eliminar_sub_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_sub_menu' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('sub_menu')
                        ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $delete_data = DB::table('sub_menu')
                            ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                            ->delete();
                        if ($delete_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            $message = "Datos eliminados con éxito.";
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al eliminar los datos.";
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe el registro que intentas eliminar.";
                        $data = array();
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

    public function status_sub_menus(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_sub_menu' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('sub_menu')
                        ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $update_data = DB::table('sub_menu')
                            ->where('sub_menu.id_sub_menu', $data_request['data']["id_sub_menu"])
                            ->update([
                                "activo" => $data_request['data']['activo']
                            ]);
                        if ($update_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            if ($data_request['data']['activo'] == 1) {
                                $message = "El sub menu se a activado con éxito.";
                            } else {
                                $message = "El sub menu se a desactivado con éxito.";
                            }
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            if ($existe_registro->activo == $data_request['data']['activo']) {
                                if ($data_request['data']['activo'] == 1) {
                                    $message = "El sub menu se encuentra activo.";
                                } else {
                                    $message = "El sub menu se encuentra inactivo.";
                                }
                            } else {
                                if ($data_request['data']['activo'] == 1) {
                                    $message = "Error al activar el sub_menu.";
                                } else {
                                    $message = "Error al desactivar le sub_menu.";
                                }
                            }
                            $data = array();
                            DB::rollback();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No existe este registro.";
                        $data = array();
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
