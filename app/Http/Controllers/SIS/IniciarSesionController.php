<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use App\Http\Dao\Implement\IDAOConfiguracion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class IniciarSesionController extends Controller
{

    protected $IDAOConfiguracion = null;

    public function __construct(IDAOConfiguracion $IDAOConfiguracion)
    {
        $this->IDAOConfiguracion = $IDAOConfiguracion;
    }

    public function VerificarTokenAccess(Request $request)
    {
        $data_request = $this->get_data_request($request);
        $Usr = null;
        $errors = null;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $this->IDAOConfiguracion->setIdUsuario($Usr->id_usuario);
            $configuracion = $this->IDAOConfiguracion->show();


            $Cfg = array(
                'timeout' => 300000,
                'tiempo_toast' => $configuracion->tiempo_toast,
                'tipo_menu' => $configuracion->tipo_menu,
                'paginacion_numero_registro' => 10,
                'paginacion_rangos' => [5, 10, 25, 50, 100],
                'archivo_maximo_megas' => 10.5,
                'diseno_paginacion' => 1,
            );

            $response = [
                'success' => true,
                'status' => 200,
                'message' => 'Acceso valido',
                'data' => array(
                    'Usr' => $Usr,
                    'Cfg' => $Cfg,
                )
            ];

        } else {
            $response = array(
                'success' => false,
                'status' => 400,
                'message' => 'Acceso denegado, token de acceso no válido',
                'errors' => $errors
            );
        }

        return $response;
    }

    public function IniciarSesion(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'username' => 'required',
            'password' => 'required'
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $id_project = $data_request['credenciales']['id_project'] ?? 2;
                $player_id = $data_request['data']["player_id"];

                $Usr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_LogIn', array(
                    "data" => array(
                        "username" => $data_request['data']["username"],
                        "password" => $data_request['data']["password"]
                    )
                ));

                $id_user = null;
                $username = null;
                $password = null;
                $token = null;


                if ($Usr) {
                    if ($Usr->success) {
                        if ($Usr->data) {
                            if ($Usr->data->id_user) {
                                $id_user = $Usr->data->id_user;
                                $username = $Usr->data->username;
                                $password = $Usr->data->password;
                                $token = $Usr->data->token;
                                $token_expire = $Usr->data->token_expire;
                                $user_token_time = $Usr->data->user_token_time;
                            }
                        }
                    } else {
                        return (array)$Usr;
                    }
                }

                if ($id_user) {

                    $row = DB::table('usuario')
                        ->select('usuario.*', 'cat_tipo_usuario.tipo_usuario')
                        ->where('usuario.id_user', $id_user)
                        ->join('cat_tipo_usuario', 'cat_tipo_usuario.id_cat_tipo_usuario', '=', 'usuario.id_cat_tipo_usuario')
                        ->first();

                    if ($row) {

                        if ($row->registro_verificacion_status !== 1) {

                            $row->username = $username;
                            $row->password = $password;
                            $row->token = $token;
                            $row->token_expire = $token_expire;
                            $row->user_token_time = $user_token_time;

                            $row->nombre_completo = trim($row->nombre . " " . $row->apellido_paterno . " " . $row->apellido_materno);

                            if ($player_id) {
                                DB::table('usuario')
                                    ->where('usuario.player_id', $player_id)
                                    ->update([
                                        'player_id' => NULL
                                    ]);
                            }

                            if ($player_id) {
                                $update_data = DB::table('usuario')
                                    ->where('usuario.id_usuario', $row->id_usuario)
                                    ->update([
                                        'ultima_sesion' => $this->DATETIME(),
                                        'player_id' => $player_id
                                    ]);
                            } else {
                                $update_data = DB::table('usuario')
                                    ->where('usuario.id_usuario', $row->id_usuario)
                                    ->update([
                                        'ultima_sesion' => $this->DATETIME()
                                    ]);
                            }

                            $menu = array();

                            $menu_data = DB::table('menu')
                                ->join('acceso_menu', 'acceso_menu.id_menu', '=', 'menu.id_menu')
                                ->where('acceso_menu.id_cat_tipo_usuario', '=', $row->id_cat_tipo_usuario)
                                ->where('acceso_menu.acceso_menu', '=', 1)
                                ->orderBy('menu.orden')
                                ->get();

                            foreach ($menu_data as $k => $m) {

                                $sub_menu_data = DB::table('sub_menu')
                                    ->join('acceso_sub_menu', 'acceso_sub_menu.id_sub_menu', '=', 'sub_menu.id_sub_menu')
                                    ->where('acceso_sub_menu.id_cat_tipo_usuario', '=', $row->id_cat_tipo_usuario)
                                    ->where('acceso_sub_menu.acceso_sub_menu', '=', 1)
                                    ->where('sub_menu.id_menu', '=', $m->id_menu)
                                    ->orderBy('sub_menu.orden')
                                    ->get();

                                if (count($sub_menu_data) > 0) {

                                    if (count($sub_menu_data) === 1) {
                                        $menu_data[$k]->sub_menu_status = false;
                                        $menu_data[$k]->sub_menu = $sub_menu_data[0];
                                    } else {
                                        $menu_data[$k]->sub_menu_status = true;
                                        $menu_data[$k]->sub_menu = $sub_menu_data;
                                    }

                                    $menu[] = $menu_data[$k];

                                }

                            }

                            $row->menu = $menu;

                            if ($update_data > 0) {
                                $flag_request = true;
                                $status = 200;
                                $message = "Inicio de sesión correcto.";
                                $data = $row;
                                DB::commit();
                            } else {
                                $flag_request = false;
                                $status = 400;
                                $message = "Error al iniciar sesión.";
                                $data = array();
                                DB::rollback();
                            }

                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "No has completado tu registro, click en \"completar registro\", los datos solicitados saran necesarios.";
                            $data = array();
                            DB::rollback();
                        }

                    } else {

                        $flag_request = false;
                        $status = 400;
                        $message = "Credenciales invalidas.";
                        $data = array();
                        DB::rollback();

                    }

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "No se autentifica el usuario.";
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

        return $response;
    }

    public function EnviarCodigoRecuperacion(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'correo_electronico' => 'required|email'
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            try {

                DB::beginTransaction();

                $id_project = $data_request['credenciales']['id_project'];

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Verify_Id', array(
                    "data" => array(
                        "username" => $data_request['data']["correo_electronico"]
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

                if ($id_user > 0) {

                    $row = DB::table('usuario')
                        ->where('usuario.id_user', $id_user)
                        ->select('*')
                        ->first();

                    if ($row) {

                        $olvido_verificacion_codigo = $this->GENERARCODIGO();

                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $row->id_usuario)
                            ->update([
                                "olvido_verificacion_status" => 1,
                                "olvido_verificacion_codigo" => $olvido_verificacion_codigo
                            ]);

                        if ($update_data > 0) {

                            $name = $row->nombre . ' ' . $row->apellido_paterno . ' ' . $row->apellido_materno;
                            $email = $data_request['data']["correo_electronico"];
                            $codigo = $olvido_verificacion_codigo;
                            $link = env('URL_REGISTER');

                            $user = (object)array(
                                'name' => $name,
                                'email' => $email,
                                'codigo' => $codigo,
                                'link' => $link,
                            );

                            Mail::send('emails-recovery-password', ['user' => $user->name, 'email' => $user->email, 'codigo' => $user->codigo, 'link' => $user->link], function ($m) use ($user) {
                                $m->from(env('MAIL_USERNAME'), env('FROM_NAME_SOUPORT'));

                                $m->to($user->email, $user->name)->subject('Recuperación de contraseña!');
                            });

                            $flag_request = true;
                            $status = 200;
                            $message = "Fue enviado un código a tu correo electrónico para validar tus datos.";
                            $data = array();
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al generar el código de verificación.";
                            $data = array();
                            DB::rollback();
                        }

                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El correo electrónico no se encuentra registrado.";
                        $data = array();
                        DB::rollback();
                    }

                } else {
                    $flag_request = false;
                    $status = 400;
                    $message = "No se identifica el usuario.";
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

        return $response;
    }

    public function VerificarCodigoRecuperacion(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'correo_electronico' => 'required|email',
            'codigo' => 'required'
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            try {

                $id_project = $data_request['credenciales']['id_project'];

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Verify_Id', array(
                    "data" => array(
                        "username" => $data_request['data']["correo_electronico"]
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

                    $row = DB::table('usuario')
                        ->where('usuario.olvido_verificacion_status', 1)
                        ->where('usuario.olvido_verificacion_codigo', $data_request['data']["codigo"])
                        ->select('*')
                        ->first();

                    if ($row) {
                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $row->id_usuario)
                            ->update([
                                "olvido_verificacion_codigo" => NULL
                            ]);

                        if ($update_data > 0) {
                            $flag_request = true;
                            $status = 200;
                            $message = "El código de verificación es correcto.";
                            $data = array();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al verificar el código de recuperación de contraseña.";
                            $data = array();
                        }
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El código de verificación no es correcto.";
                        $data = array();
                    }

                } else {
                    $flag_request = false;
                    $status = 400;
                    $message = "No se encontró el usuario.";
                    $data = array();
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

        return $response;
    }

    public function CambiarPassword(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'correo_electronico' => 'required|email',
            'password' => 'required'
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            try {

                DB::beginTransaction();

                $id_project = $data_request['credenciales']['id_project'];
                $username = $data_request['data']["correo_electronico"];
                $password = $data_request['data']["password"];

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Verify_Id', array(
                    "data" => array(
                        "username" => $username
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

                if ($id_user > 0) {

                    $row = DB::table('usuario')
                        ->where('usuario.id_user', $id_user)
                        ->where('usuario.olvido_verificacion_status', 1)
                        ->select('*')
                        ->first();

                    if ($row) {

                        $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Change_Password', array(
                            "data" => array(
                                "id_user" => $id_user,
                                "username" => $username,
                                "password" => $password
                            )
                        ));

                        if ($dUsr->success) {

                            $update_data = DB::table('usuario')
                                ->where('usuario.id_usuario', $row->id_usuario)
                                ->update([
                                    "olvido_verificacion_status" => 0,
                                    "olvido_verificacion_codigo" => NULL
                                ]);

                            if ($update_data > 0) {
                                $flag_request = true;
                                $status = 200;
                                $message = "La contraseña fue actualizado con éxito.";
                                $data = array();
                                DB::commit();
                            } else {
                                $flag_request = false;
                                $status = 400;
                                $message = "Error al actualizar la contraseña del usuario.";
                                $data = array();
                                DB::rollback();
                            }

                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = $dUsr->message ?? "Error al actualizar la contraseña";
                            $data = array();
                            DB::rollback();
                        }

                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "No se encontró el usuario, o la contraseña ya fue actualizado, inicia sesión para comprobar los datos.";
                        $data = array();
                        DB::rollback();
                    }

                } else {
                    $flag_request = false;
                    $status = 400;
                    $message = "No se encontró el usuario.";
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

        return $response;
    }

}
