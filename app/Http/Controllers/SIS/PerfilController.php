<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use App\Http\Dao\Implement\IDAOConfiguracion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    protected $IDAOConfiguracion = null;

    public function __construct(IDAOConfiguracion $IDAOConfiguracion)
    {
        $this->IDAOConfiguracion = $IDAOConfiguracion;
    }

    public function perfil_usuarios_datos(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $this->IDAOConfiguracion->setIdUsuario($Usr->id_usuario);
                $configuracion = $this->IDAOConfiguracion->show();

                $usuario_direccion = DB::table('usuario_direccion')
                    ->select(
                        "usuario_direccion.*",
                        "cat_estado.estado",
                        "cat_municipio.municipio"
                    )
                    ->leftJoin("cat_estado", "cat_estado.id_cat_estado", "=", "usuario_direccion.id_cat_estado")
                    ->leftJoin("cat_municipio", "cat_municipio.id_cat_municipio", "=", "usuario_direccion.id_cat_municipio")
                    ->where("id_usuario", "=", $Usr->id_usuario)
                    ->first();

                $Usr->sexo = DB::table('cat_sexo')
                    ->select("sexo")
                    ->where("id_cat_sexo", "=", $Usr->id_cat_sexo)
                    ->value("sexo");

                $cat = CurlRequest::POST($Usr->id_project, env('API_AUTH') . '_Auth_Cat_List', array(
                    "token" => $Usr->token,
                    "credenciales" => array(
                        "id_user" => $Usr->id_user,
                        "username" => $Usr->username
                    ),
                    "data" => array(
                        "id_user" => $Usr->id_user
                    )
                ));

                $cat_auth = (object)array();

                if ($cat->success) {
                    $cat_auth = $cat->data ?? (object)array();
                }

                if ($usuario_direccion) {
                    $data = array(
                        "cat_auth" => $cat_auth,
                        "usuario" => $Usr,
                        "usuario_direccion" => $usuario_direccion,
                        "domicilio" => $this->CadenaDomiilio((array)$usuario_direccion),
                        "configuracion" => $configuracion,
                    );
                    DB::commit();
                } else {
                    $data = array(
                        "cat_auth" => $cat_auth,
                        "usuario" => $Usr,
                        "usuario_direccion" => (object)array(),
                        "domicilio" => "",
                        "configuracion" => $configuracion,
                    );
                    DB::rollback();
                }

                $response = [
                    "success" => true,
                    "status" => 200,
                    "message" => "Datos encontrados.",
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

    public function perfil_usuarios_solicitar_cambio_contrasena(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                if (!empty($Usr->correo_electronico)) {

                    $cambiar_verificacion_codigo = $this->GENERARCODIGO();

                    $update_data = DB::table('usuario')
                        ->where('usuario.id_usuario', $Usr->id_usuario)
                        ->update([
                            "cambiar_verificacion_codigo" => $cambiar_verificacion_codigo
                        ]);

                    if ($update_data > 0) {

                        $name = $Usr->nombre . ' ' . $Usr->apellido_paterno . ' ' . $Usr->apellido_materno;
                        $email = $Usr->correo_electronico;
                        $codigo = $cambiar_verificacion_codigo;

                        $user = (object)array(
                            'name' => $name,
                            'email' => $email,
                            'codigo' => $codigo
                        );

                        Mail::send('emails-cambiar-contrasena', ['user' => $user->name, 'codigo' => $user->codigo], function ($m) use ($user) {
                            $m->from(env('MAIL_USERNAME'), env('FROM_NAME_SOUPORT'));

                            $m->to($user->email, $user->name)->subject('Cambio de contraseña!');
                        });

                        $flag_request = true;
                        $status = 200;
                        $message = "Fue enviado un código a tu correo electrónico para realizar el cambio de contraseña.";
                        $data = array();
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "Error al generar el código de verificación para el cambio de contraseña.";
                        $data = array();
                        DB::rollback();
                    }

                } else {
                    $flag_request = false;
                    $status = 400;
                    $message = "Es necesario tener un correo electrónico para continuar con el cambios de contraseña.";
                    $data = array();

                    DB::rollBack();
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

    public function perfil_usuarios_cambiar_contrasena(Request $request)
    {

        $data_request = $this->get_data_request($request);


        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'codigo_verificacion_password' => 'required',
                'password' => 'required',
                'password_confirm' => 'required',
            ], [
                'codigo_verificacion_password.required' => 'Verifica qu el código de confirmación sea el mismo que se te envío por correo electrónico',
                'password.required' => 'Es requerido el campo contraseña',
                'password_confirm.required' => 'Vuelve a escribir tu contraseña',
            ]);

            if (!$validator->fails()) {

                $username = $data_request['credenciales']['username'];
                $codigo_verificacion_password = $data_request['data']['codigo_verificacion_password'];
                $password = $data_request['data']['password'];
                $password_confirm = $data_request['data']['password_confirm'];

                DB::beginTransaction();

                try {

                    if ($password === $password_confirm) {

                        if ($codigo_verificacion_password === $Usr->cambiar_verificacion_codigo) {

                            $dUsr = CurlRequest::POST($Usr->id_project, env('API_AUTH') . '_Auth_User_Change_Password', array(
                                "data" => array(
                                    "id_user" => $Usr->id_user,
                                    "username" => $username,
                                    "password" => $password
                                )
                            ));

                            if ($dUsr->success) {

                                $update_data = DB::table('usuario')
                                    ->where('usuario.id_usuario', $Usr->id_usuario)
                                    ->update([
                                        "cambiar_verificacion_codigo" => NULL
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
                            $message = "El código de confirmación no es correcto.";
                            $data = array();

                            DB::rollBack();
                        }

                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "La contraseña no coincide.";
                        $data = array();

                        DB::rollBack();
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

    public function perfil_usuarios_cambiar_foto(Request $request)
    {

        $data_request = $this->get_data_request($request);


        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'foto' => 'required',
                'formato' => 'required',
            ], [
                'foto.required' => 'Selecciona la imagen que usaras como foto de perfil',
                'formato.required' => 'No se obtuvo el formato del archivo',
            ]);

            if (!$validator->fails()) {

                $foto = $data_request['data']['foto'];
                $formato = $data_request['data']['formato'];

                DB::beginTransaction();

                try {

                    $archivo = array(
                        "success" => false,
                        "ruta" => NULL
                    );

                    if (isset($foto) && isset($formato)) {
                        if (!empty($foto) && !empty($formato)) {
                            $nombre_archivo = 'foto_perfil_' . md5($Usr->id_usuario) . $this->DATETIMEUNIX();
                            $archivo = $this->Base64ToFile($foto, env('URL_ARCHIVO_PERFIL'), $nombre_archivo, $formato);
                        }
                    }

                    if ($archivo['success'] === true) {

                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $Usr->id_usuario)
                            ->update([

                                "foto" => $this->input($archivo['ruta']),

                                "updated_at" => $this->DATETIME()

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
                        $message = "Error al crear el archivo.";
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

    public function perfil_usuarios_cambiar_portada(Request $request)
    {

        $data_request = $this->get_data_request($request);


        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'portada' => 'required',
                'formato' => 'required',
            ], [
                'portada.required' => 'Selecciona la imagen que usaras como foto de portada',
                'formato.required' => 'No se obtuvo el formato del archivo',
            ]);

            if (!$validator->fails()) {

                $portada = $data_request['data']['portada'];
                $formato = $data_request['data']['formato'];

                DB::beginTransaction();

                try {

                    $archivo = array(
                        "success" => false,
                        "ruta" => NULL
                    );

                    if (isset($portada) && isset($formato)) {
                        if (!empty($portada) && !empty($formato)) {
                            $nombre_archivo = 'foto_portada_' . md5($Usr->id_usuario) . $this->DATETIMEUNIX();
                            $archivo = $this->Base64ToFile($portada, env('URL_ARCHIVO_PORTADA'), $nombre_archivo, $formato);
                        }
                    }

                    if ($archivo['success'] === true) {

                        $update_data = DB::table('usuario')
                            ->where('usuario.id_usuario', $Usr->id_usuario)
                            ->update([

                                "portada" => $this->input($archivo['ruta']),

                                "updated_at" => $this->DATETIME()

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
                        $message = "Error al crear el archivo.";
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

    public function perfil_usuarios_actualizar_informacion(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'nombre' => 'required',
                'apellido_paterno' => 'required',
                'apellido_materno' => 'required',
                'id_cat_sexo' => 'required',
                'fecha_nacimiento' => 'required',
                'id_cat_estado_nacimiento' => '',
                'id_cat_municipio_nacimiento' => '',
            ], [
                'nombre.required' => 'Campo requerido: Nombre',
                'apellido_paterno.required' => 'Campo requerido: Apellido paterno',
                'apellido_materno.required' => 'Campo requerido: Apellido materno',
                'id_cat_sexo.required' => 'Campo requerido: Sexo',
                'fecha_nacimiento.required' => 'Campo requerido: fecha de nacimiento',
                'id_cat_estado_nacimiento.required' => 'Campo requerido: Estado de nacimiento',
                'id_cat_municipio_nacimiento.required' => 'Campo requerido: Municipio de nacimiento',
            ]);

            if (!$validator->fails()) {

                $nombre = $data_request['data']['nombre'];
                $apellido_paterno = $data_request['data']['apellido_paterno'];
                $apellido_materno = $data_request['data']['apellido_materno'];
                $id_cat_sexo = $data_request['data']['id_cat_sexo'];
                $fecha_nacimiento = $data_request['data']['fecha_nacimiento'];
                $id_cat_estado_nacimiento = $data_request['data']['id_cat_estado_nacimiento'];
                $id_cat_municipio_nacimiento = $data_request['data']['id_cat_municipio_nacimiento'];

                DB::beginTransaction();

                try {

                    $update_data = DB::table('usuario')
                        ->where('usuario.id_usuario', $Usr->id_usuario)
                        ->update([

                            "nombre" => $this->input($nombre),
                            "apellido_paterno" => $this->input($apellido_paterno),
                            "apellido_materno" => $this->input($apellido_materno),
                            "id_cat_sexo" => $this->input($id_cat_sexo),
                            "fecha_nacimiento" => $this->input($fecha_nacimiento),
                            "id_cat_estado_nacimiento" => $this->input($id_cat_estado_nacimiento),
                            "id_cat_municipio_nacimiento" => $this->input($id_cat_municipio_nacimiento),

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

    public function perfil_usuarios_actualizar_correos_telefonos(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'celular' => 'required',
                'telefono' => '',
                'correo_electronico' => 'required',
                'correo_electronico_personal' => '',
            ], [
                'celular.required' => 'Campo requerido: Celular',
                'telefono.required' => 'Campo requerido: Teléfono',
                'correo_electronico.required' => 'Campo requerido: Correo electrónico',
                'correo_electronico_personal.required' => 'Campo requerido: Correo electrónico personal',
            ]);

            if (!$validator->fails()) {

                $celular = $data_request['data']['celular'];
                $telefono = $data_request['data']['telefono'];
                $correo_electronico = $data_request['data']['correo_electronico'];
                $correo_electronico_personal = $data_request['data']['correo_electronico_personal'];

                DB::beginTransaction();

                try {

                    $update_data = DB::table('usuario')
                        ->where('usuario.id_usuario', $Usr->id_usuario)
                        ->update([

                            "celular" => $this->input($celular),
                            "telefono" => $this->input($telefono),
                            "correo_electronico" => $this->input($correo_electronico),
                            "correo_electronico_personal" => $this->input($correo_electronico_personal),

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

    public function perfil_usuarios_actualizar_domicilio(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'calle' => 'required',
                'numero_exterior' => 'required',
                'numero_interior' => '',
                'codigo_postal' => 'required',
                'colonia' => 'required',
                'id_cat_municipio' => 'required',
                'id_cat_estado' => 'required',
            ], [
                'calle.required' => 'Campo requerido: Calle',
                'numero_exterior.required' => 'Campo requerido: Número exterior',
                'numero_interior.required' => 'Campo requerido: Número interior',
                'codigo_postal.required' => 'Campo requerido: Código postal',
                'colonia.required' => 'Campo requerido: Colonia',
                'id_cat_municipio.required' => 'Campo requerido: Municipio',
                'id_cat_estado.required' => 'Campo requerido: Estado',
            ]);

            if (!$validator->fails()) {

                $calle = $data_request['data']['calle'];
                $numero_exterior = $data_request['data']['numero_exterior'];
                $numero_interior = $data_request['data']['numero_interior'];
                $codigo_postal = $data_request['data']['codigo_postal'];
                $colonia = $data_request['data']['colonia'];
                $id_cat_municipio = $data_request['data']['id_cat_municipio'];
                $id_cat_estado = $data_request['data']['id_cat_estado'];

                DB::beginTransaction();

                try {

                    $usuario_direccion = DB::table('usuario_direccion')
                        ->where('usuario_direccion.id_usuario', $Usr->id_usuario)
                        ->first();

                    if ($usuario_direccion) {

                        $update_data = DB::table('usuario_direccion')
                            ->where('usuario_direccion.id_usuario', $Usr->id_usuario)
                            ->update([

                                "calle" => $this->input($calle),
                                "numero_exterior" => $this->input($numero_exterior),
                                "numero_interior" => $this->input($numero_interior),
                                "codigo_postal" => $this->input($codigo_postal),
                                "colonia" => $this->input($colonia),
                                "id_cat_estado" => $this->input($id_cat_estado),
                                "id_cat_municipio" => $this->input($id_cat_municipio),

                                "updated_at" => $this->DATETIME()
                            ]);

                    } else {

                        $insert_data = DB::table('usuario_direccion')
                            ->insertGetId([

                                "id_usuario" => $Usr->id_usuario,

                                "calle" => $this->input($calle),
                                "numero_exterior" => $this->input($numero_exterior),
                                "numero_interior" => $this->input($numero_interior),
                                "codigo_postal" => $this->input($codigo_postal),
                                "colonia" => $this->input($colonia),
                                "id_cat_estado" => $this->input($id_cat_estado),
                                "id_cat_municipio" => $this->input($id_cat_municipio),

                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                    }

                    if ($update_data ?? $insert_data) {
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

    public function perfil_usuarios_player_id_guardar(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'player_id' => 'required',
            ], [
                'player_id.required' => 'No se encontró el ID del servicio de notificaciones OneSignal para asociarlo a tu usuario, intenta suscribirte nuevamente',
            ]);

            if (!$validator->fails()) {

                $player_id = $data_request['data']['player_id'];

                DB::beginTransaction();

                try {

                    $update_data = DB::table('usuario')
                        ->where('usuario.id_usuario', $Usr->id_usuario)
                        ->update([
                            "player_id" => $this->input($player_id),
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

    public function perfil_usuarios_player_id_prueba(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $data_request['data']['player_id'] = $Usr->player_id;

            $validator = Validator::make($data_request['data'], [
                'player_id' => 'required',
            ], [
                'player_id.required' => 'LA prueba no se puede realizar, no se encontró el ID del servicio de notificaciones OneSignal para asociarlo a tu usuario, intenta suscribirte nuevamente',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {


                    $OneSignal = new OneSignal();
                    $message = "Si estas viendo esta notificación, quiere decir que todo salio bien, tu usuario fue asociado con este navegador para qe reciba notificaciones de este tipo";
                    $OneSignal->setContent($message);
                    $OneSignal->setPlayerIds(array(
                        $Usr->player_id
                    ));

                    $result = $OneSignal->OneSignalSendMessage();

                    $flag = true;

                    if (isset($result->data->errors)) {
                        if ($result->data->errors) {
                            if (isset($result->data->errors->invalid_player_ids)) {
                                if ($result->data->errors->invalid_player_ids) {
                                    if (count($result->data->errors->invalid_player_ids)) {
                                        $flag = false;
                                    }
                                }
                            }
                        }
                    }

                    if ($flag) {
                        $flag_request = true;
                        $status = 200;
                        $message = "Prueba realizado con éxito.";
                        $data = $result;
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "Error al realizar la prueba de notificaciones, verifica que te hayas suscrito.";
                        $data = $result;
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
