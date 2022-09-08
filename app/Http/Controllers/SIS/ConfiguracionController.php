<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use App\Http\Dao\Implement\IDAOConfiguracion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    protected $IDAOConfiguracion = null;

    public function __construct(IDAOConfiguracion $IDAOConfiguracion)
    {
        $this->IDAOConfiguracion = $IDAOConfiguracion;
    }

    public function cambiar_tiempo_expira_token(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_time_token' => 'required'
            ], [
                'id_cat_time_token.required' => 'Es necesario seleccionar el tiempo en que expirara el token de acceso',
            ]);

            $flag_request = false;

            if (!$validator->fails()) {

                try {

                    $id_user = $Usr->id_user;
                    $id_cat_time_token = $data_request['data']['id_cat_time_token'];

                    DB::beginTransaction();

                    $dUsr = CurlRequest::POST($Usr->id_project, env('API_AUTH') . '_Auth_User_Change_Time_Token', array(
                        "data" => array(
                            "id_user" => $id_user,
                            "id_cat_time_token" => $id_cat_time_token,
                        )
                    ));

                    if ($dUsr->success) {
                        $flag_request = true;
                        $status = 200;
                        $message = "El tiempo para expirar el token fue actualizado con éxito, vuelve a iniciar sesión para obtener estos cambios.";
                        $data = array();
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = $dUsr->message ?? "Error al actualizar el tiempo de expiración para el token";
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

    public function cambiar_tiempo_toast(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'tiempo_toast' => 'required'
            ], [
                'tiempo_toast.required' => 'Es necesario seleccionar el tiempo en que expirara el token de acceso',
            ]);

            if (!$validator->fails()) {

                $tiempo_toast = $data_request['data']['tiempo_toast'];

                DB::beginTransaction();

                try {

                    $this->IDAOConfiguracion->setIdUsuario($Usr->id_usuario);
                    $this->IDAOConfiguracion->setTiempoToast($tiempo_toast);
                    $update = $this->IDAOConfiguracion->update();

                    if ($update) {
                        $flag_request = true;
                        $status = 200;
                        $message = "Datos guardados con éxito.";
                        $data = array();

                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "Error al actualizar los datos.";
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

    public function cambiar_tipo_menu(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'tipo_menu' => 'required'
            ], [
                'tipo_menu.required' => 'Es necesario seleccionar el tiempo en que expirara el token de acceso',
            ]);

            if (!$validator->fails()) {

                $tipo_menu = $data_request['data']['tipo_menu'];

                DB::beginTransaction();

                try {

                    $this->IDAOConfiguracion->setIdUsuario($Usr->id_usuario);
                    $this->IDAOConfiguracion->setTipoMenu($tipo_menu);
                    $update = $this->IDAOConfiguracion->update();

                    if ($update) {
                        $flag_request = true;
                        $status = 200;
                        $message = "Datos guardados con éxito.";
                        $data = array();

                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "Error al actualizar los datos.";
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
}
