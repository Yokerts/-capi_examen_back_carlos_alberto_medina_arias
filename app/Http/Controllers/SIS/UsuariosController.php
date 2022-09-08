<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UsuariosController extends Controller
{
    public function listar_usuarios(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $result = CurlRequest::POST($Usr->id_project, env('API_AUTH') . '_Auth_User_List', array(
                    "token" => $Usr->token,
                    "credenciales" => array(
                        "id_user" => $Usr->id_user,
                        "username" => $Usr->username
                    ),
                    "data" => array(
                        "id_user" => $Usr->id_user
                    )
                ));

                $datos = array();

                if ($result->success) {
                    if (count($result->data) > 0) {
                        $datos = (array)$result->data;
                    }
                }

                $data_db = DB::table('usuario')
                    ->join('cat_tipo_usuario', 'cat_tipo_usuario.id_cat_tipo_usuario', 'usuario.id_cat_tipo_usuario')
                    ->whereNotIn('usuario.id_usuario', [1, 2])
                    ->select(
                        'usuario.*',
                        'cat_tipo_usuario.tipo_usuario'
                    )
                    ->get();

                if ($data_db) {
                    foreach ($data_db as $key1 => $row1) {
                        foreach ($datos as $key2 => $row2) {
                            if ($row1->id_user == $row2->id_user) {
                                $data_db[$key1]->username = $row2->username;
                                $data_db[$key1]->password = $row2->password;
                                $data_db[$key1]->token = $row2->token;
                                $data_db[$key1]->token_expire = $row2->token_expire;
                            }
                        }
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

            } catch (Exception $e) {
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

    public function agregar_usuarios(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $dUsr = CurlRequest::POST($Usr->id_project, env('API_AUTH') . '_Auth_User_Verify_New', array(
                "token" => $Usr->token,
                "credenciales" => array(
                    "id_user" => $Usr->id_user,
                    "username" => $Usr->username
                ),
                "data" => array(
                    "username" => $data_request['data']["username"]
                )
            ));

            $id_user = null;

            if ($dUsr) {
                if (isset($dUsr->data)) {
                    if ($dUsr->data) {
                        if ($dUsr->data->id_user) {
                            $id_user = $dUsr->data->id_user;
                        }
                    }
                }
            }

            if ($id_user) {

                $data_request['data']['registro_verificacion_status'] = 1;
                $data_request['data']['registro_verificacion_codigo'] = $this->GENERARCODIGO();

                $validator = Validator::make($data_request['data'], [
                    'id_usuario' => '',
                    'nombre' => 'required',
                    'apellido_paterno' => 'required',
                    'apellido_materno' => 'required',
                    'correo_electronico' => 'required|email',
                    'registro_verificacion_status' => 'required',
                    'registro_verificacion_codigo' => 'required',
                    'id_cat_tipo_usuario' => 'required',
                    'activo' => 'required',
                    'sendmail' => 'required',
                ]);

                if (!$validator->fails()) {

                    DB::beginTransaction();

                    try {

                        $existe_registro = DB::table('usuario')
                            ->where('usuario.id_user', $id_user)
                            ->select('*')
                            ->first();

                        if ($existe_registro == null) {
                            $insert_data = DB::table('usuario')
                                ->insertGetId([
                                    "id_user" => $id_user,
                                    "nombre" => $data_request['data']['nombre'],
                                    "apellido_paterno" => $data_request['data']['apellido_paterno'],
                                    "apellido_materno" => $data_request['data']['apellido_materno'],
                                    "correo_electronico" => $data_request['data']['correo_electronico'],
                                    "registro_verificacion_status" => $data_request['data']['registro_verificacion_status'],
                                    "registro_verificacion_codigo" => $data_request['data']['registro_verificacion_codigo'],
                                    "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                    "foto" => env('FOTO_DEFAULT'),
                                    "activo" => $data_request['data']['activo'],
                                    "isjefe" => $data_request['data']['isjefe'],
                                    "sendmail" => $data_request['data']['sendmail'],
                                    "created_at" => $this->DATETIME(),
                                    "updated_at" => $this->DATETIME()
                                ]);

                            if ($insert_data > 0) {

                                $name = $data_request['data']['nombre'] . ' ' . $data_request['data']['apellido_paterno'] . ' ' . $data_request['data']['apellido_materno'];
                                $email = $data_request['data']['correo_electronico'];
                                $rol = DB::table('cat_tipo_usuario')->select('tipo_usuario')->where('id_cat_tipo_usuario', '=', $data_request['data']['id_cat_tipo_usuario'])->value('tipo_usuario');
                                $codigo = $data_request['data']['registro_verificacion_codigo'];
                                $password = env('PASSWORD_REGISTER');
                                $link = env('URL_REGISTER');

                                $user = (object)array(
                                    'name' => $name,
                                    'email' => $email,
                                    'rol' => $rol,
                                    'codigo' => $codigo,
                                    'password' => $password,
                                    'link' => $link,
                                );

                                Mail::send('emails-new-user', ['user' => $user->name, 'email' => $user->email, 'rol' => $user->rol, 'password' => $user->password, 'codigo' => $user->codigo, 'link' => $user->link], function ($m) use ($user) {
                                    $m->from(env('MAIL_USERNAME'), env('FROM_NAME_SOUPORT'));

                                    $m->to($user->email, $user->name)->subject('Te damos la bienvenida a Citas Médicas');
                                });

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
                            $message = "El usuario " . $data_request['data']['username'] . " ya fue registrado.";
                            $data = array();
                            DB::rollback();
                        }

                        $response = [
                            "success" => $flag_request,
                            "status" => $status,
                            "message" => $message,
                            "data" => $data
                        ];

                    } catch (Exception $e) {
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
                $response = [
                    "success" => $flag_request,
                    "status" => 400,
                    "message" => "No se autentifica el usuario.",
                    "errors" => array()
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

    public function modificar_usuarios(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_usuario' => 'required',
                'nombre' => 'required',
                'apellido_paterno' => 'required',
                'apellido_materno' => 'required',
                'correo_electronico' => 'required',
                'id_cat_tipo_usuario' => 'required',
                'activo' => 'required',
                'sendmail' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('usuario')
                        ->where('usuario.id_usuario', $data_request['data']["id_usuario"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $data_request['data']["id_usuario"])
                            ->update([
                                "nombre" => $data_request['data']['nombre'],
                                "apellido_paterno" => $data_request['data']['apellido_paterno'],
                                "apellido_materno" => $data_request['data']['apellido_materno'],
                                "correo_electronico" => $data_request['data']['correo_electronico'],
                                "id_cat_tipo_usuario" => $data_request['data']['id_cat_tipo_usuario'],
                                "isjefe" => $data_request['data']['isjefe'],
                                "activo" => $data_request['data']['activo'],
                                "sendmail" => $data_request['data']['sendmail'],
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

                } catch (Exception $e) {
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

    public function eliminar_usuarios(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_usuario' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $existe_registro = DB::table('usuario')
                        ->where('usuario.id_usuario', $data_request['data']["id_usuario"])
                        ->select('*')
                        ->first();

                    if ($existe_registro) {
                        DB::table('usuario_direccion')
                            ->where('usuario_direccion.id_usuario', $data_request['data']["id_usuario"])
                            ->delete();
                        $delete_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $data_request['data']["id_usuario"])
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

                } catch (Exception $e) {
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

    public function tipo_usuarios(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_tipo_usuario' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {
                    $usuarios = DB::table('usuario')
                        ->where('usuario.id_cat_tipo_usuario', '=', $data_request['data']["id_cat_tipo_usuario"])
                        ->orderBy('usuario.nombre', 'ASC')
                        ->get();

                    if ($usuarios->count() > 0) {
                        for ($x=0;$x < $usuarios->count(); $x++) {
                            $usuarios[$x]->name = $usuarios[$x]->nombre . ' ' . $usuarios[$x]->apellido_paterno . ' ' . $usuarios[$x]->apellido_materno;
                        }
                    }

                    if ($usuarios->count() > 0) {
                        $flag_request = true;
                        $status = 200;
                        $message = "Datos encontrados con éxito.";
                        $data = $usuarios;
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No se encontraron usuarios.";
                        $data = array();
                        DB::rollback();

                    }

                    $response = [
                        "success" => $flag_request,
                        "status" => $status,
                        "message" => $message,
                        "data" => $data
                    ];

                } catch (Exception $e) {
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
