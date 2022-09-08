<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UsuarioRegistroController extends Controller
{
    public function UsuarioRegistroVerificar(Request $request)
    {

        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'username' => 'required|email',
            'password' => 'required'
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            try {

                $id_project = $data_request['credenciales']['id_project'];

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Verify_Id', array(
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

                    $row = DB::table('usuario')
                        ->where('usuario.id_user', $id_user)
                        ->where('usuario.registro_verificacion_status', 1)
                        ->where('usuario.registro_verificacion_codigo', $data_request['data']["codigo"])
                        ->select(
                            "usuario.id_usuario",
                            "usuario.id_user",
                            "usuario.nombre",
                            "usuario.apellido_paterno",
                            "usuario.apellido_materno",
                            "usuario.fecha_nacimiento",
                            "usuario.id_cat_sexo",
                            "usuario.celular",
                            "usuario.telefono",
                            "usuario.correo_electronico",
                            "usuario.foto",
                            "usuario.id_cat_estado_nacimiento",
                            "usuario.id_cat_municipio_nacimiento",
                            "usuario.fecha_nacimiento",
                            "usuario.curp",
                            "usuario.rfc"
                        )
                        ->first();

                    if ($row) {

                        $flag_request = true;
                        $status = 200;
                        $message = "Datos correctos, completa los datos siguientes, estos datos son necesarios";
                        $data = $row;

                    } else {

                        $flag_request = false;
                        $status = 400;
                        $message = "No se encontró el usuario, asegúrate de haber escrito correctamente los datos, o verifica que el código de invitación sea correcto.";
                        $data = array();

                    }

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "No se encontró el usuario, asegúrate de haber escrito correctamente los datos.";
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

    public function UsuarioRegistroGuardar(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'id_usuario' => 'required',
            'username' => 'required|email',
            'codigo' => 'required',

            "nombre" => 'required',
            "apellido_paterno" => 'required',
            "apellido_materno" => 'required',

            "id_cat_sexo" => 'required',
            "celular" => 'required|max:10',
            "telefono" => '',
            "correo_electronico" => 'required|email',

            "formato" => '',
            "foto" => '',

            "id_cat_estado_nacimiento" => '',
            "id_cat_municipio_nacimiento" => '',
            "fecha_nacimiento" => '',

            "curp" => '',
            "rfc" => '',

            "calle" => '',
            "numero_exterior" => '',
            "numero_interior" => '',
            "codigo_postal" => '',
            "colonia" => '',
            "id_cat_municipio" => '',
            "id_cat_estado" => '',

        ], [
            'formato.required' => 'Selecciona una imagen de perfil',
            'foto.required' => 'Selecciona una imagen de perfil',
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            try {

                DB::beginTransaction();

                $id_project = $data_request['credenciales']['id_project'];

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_User_Verify_Id', array(
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

                if ($id_user > 0) {

                    $row = DB::table('usuario')
                        ->where('usuario.id_user', '=', $id_user)
                        ->where('usuario.registro_verificacion_status', '=', 1)
                        ->where('usuario.registro_verificacion_codigo', '=', $data_request['data']["codigo"])
                        ->select('*')
                        ->first();

                    if ($row) {

                        $foto = array(
                            "ruta" => NULL
                        );

                        if (isset($data_request['data']['foto']) && isset($data_request['data']['formato'])) {
                            if (!empty($data_request['data']['foto']) && !empty($data_request['data']['formato'])) {
                                $nombre_archivo = 'foto_perfil_' . md5($row->id_usuario);
                                $foto = $this->Base64ToFile($data_request['data']['foto'], env('URL_ARCHIVO_PERFIL'), $nombre_archivo, $data_request['data']['formato']);
                            }
                        }


                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $data_request['data']["id_usuario"])
                            ->update([

                                "nombre" => $this->input($data_request['data']['nombre']),
                                "apellido_paterno" => $this->input($data_request['data']['apellido_paterno']),
                                "apellido_materno" => $this->input($data_request['data']['apellido_materno']),

                                "id_cat_sexo" => $this->input($data_request['data']['id_cat_sexo']),
                                "celular" => $this->input($data_request['data']['celular']),
                                "telefono" => $this->input($data_request['data']['telefono']),
                                "correo_electronico" => $this->input($data_request['data']['correo_electronico']),

                                "foto" => $this->input($foto['ruta']),

                                "id_cat_estado_nacimiento" => $this->input($data_request['data']['id_cat_estado_nacimiento']),
                                "id_cat_municipio_nacimiento" => $this->input($data_request['data']['id_cat_municipio_nacimiento']),
                                "fecha_nacimiento" => $this->input($data_request['data']['fecha_nacimiento'] ?? null),

                                "curp" => $this->input(strtoupper($data_request['data']['curp'])),
                                "rfc" => $this->input(strtoupper($data_request['data']['rfc'])),

                                "registro_verificacion_status" => 0,
                                "registro_verificacion_codigo" => NULL,

                                "updated_at" => $this->DATETIME()

                            ]);

                        if ($update_data) {

                            $usuario_direccion = DB::table('usuario_direccion')
                                ->where('usuario_direccion.id_usuario', $row->id_usuario)
                                ->first();

                            if ($usuario_direccion) {

                                $update_data = DB::table('usuario_direccion')
                                    ->where('usuario_direccion.id_usuario', $row->id_usuario)
                                    ->update([

                                        "calle" => $this->input($data_request['data']['calle']),
                                        "numero_exterior" => $this->input($data_request['data']['numero_exterior']),
                                        "numero_interior" => $this->input($data_request['data']['numero_interior']),
                                        "codigo_postal" => $this->input($data_request['data']['codigo_postal']),
                                        "colonia" => $this->input($data_request['data']['colonia']),
                                        "id_cat_estado" => $this->input($data_request['data']['id_cat_estado']),
                                        "id_cat_municipio" => $this->input($data_request['data']['id_cat_municipio']),

                                        "updated_at" => $this->DATETIME()
                                    ]);

                            } else {

                                $insert_data = DB::table('usuario_direccion')
                                    ->insertGetId([

                                        "id_usuario" => $row->id_usuario,

                                        "calle" => $this->input($data_request['data']['calle']),
                                        "numero_exterior" => $this->input($data_request['data']['numero_exterior']),
                                        "numero_interior" => $this->input($data_request['data']['numero_interior']),
                                        "codigo_postal" => $this->input($data_request['data']['codigo_postal']),
                                        "colonia" => $this->input($data_request['data']['colonia']),
                                        "id_cat_estado" => $this->input($data_request['data']['id_cat_estado']),
                                        "id_cat_municipio" => $this->input($data_request['data']['id_cat_municipio']),

                                        "created_at" => $this->DATETIME(),
                                        "updated_at" => $this->DATETIME()
                                    ]);
                            }

                            if ($update_data ?? $insert_data) {

                                $name = $data_request['data']['nombre'] . ' ' . $data_request['data']['apellido_paterno'] . ' ' . $data_request['data']['apellido_materno'];
                                $email = $data_request['data']['correo_electronico'];
                                $password = env('PASSWORD_REGISTER');
                                $link = env('URL_REGISTER');

                                $user = (object)array(
                                    'name' => $name,
                                    'email' => $email,
                                    'password' => $password,
                                    'link' => $link,
                                );

                                Mail::send('emails-welcome-user', ['user' => $user->name, 'email' => $user->email, 'password' => $user->password, 'link' => $user->link], function ($m) use ($user) {
                                    $m->from(env('MAIL_USERNAME'), env('FROM_NAME_SOUPORT'));

                                    $m->to($user->email, $user->name)->subject('Bienvenido!');
                                });

                                $flag_request = true;
                                $status = 200;
                                $message = "Datos correctos, se completo tu registro, ahora puedes iniciar sesión";
                                $data = array();

                                DB::commit();

                            } else {

                                $flag_request = false;
                                $status = 400;
                                $message = "No se guardaron tus datos, intenta nuevamente.";
                                $data = array();

                                DB::rollback();

                            }

                        } else {

                            $flag_request = false;
                            $status = 400;
                            $message = "Tus datos no fueron actualizados, intenta nuevamente.";
                            $data = array();

                            DB::rollback();

                        }

                    } else {

                        $flag_request = false;
                        $status = 400;
                        $message = "No se encontró el usuario, no se podrá continuar con el proceso actual, intenta nuevamente.";
                        $data = array();

                        DB::rollback();

                    }

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "Error al procesar los datos, no se encontró el usuario.";
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
