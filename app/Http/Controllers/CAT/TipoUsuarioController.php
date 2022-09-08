<?php

namespace App\Http\Controllers\CAT;

use App\Http\Controllers\Controller;
use App\Http\Dao\Implement\IDAOTipoUsuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TipoUsuarioController extends Controller
{
    protected $IDAOTipoUsuario = null;

    /**
     * TipoUsuarioController constructor.
     * @param null $IDAOTipoUsuario
     */
    public function __construct(IDAOTipoUsuario $IDAOTipoUsuario)
    {
        $this->IDAOTipoUsuario = $IDAOTipoUsuario;
    }

    public function listar_tipo_usuario(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $data_db = $this->IDAOTipoUsuario->all();

                if ($data_db == null) {
                    $flag_request = true;
                    $status = 400;
                    $message = "No se encontraron datos.";
                    $data = array();
                    DB::rollback();
                } else {
                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados.";
                    $data = $data_db;
                    DB::commit();
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

    public function agregar_tipo_usuario(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_tipo_usuario' => '',
                'tipo_usuario' => 'required',
                'descripcion' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $this->IDAOTipoUsuario->setTipoUsuario($data_request['data']["tipo_usuario"]);
                    $existe_registro = $this->IDAOTipoUsuario->showForName();

                    if ($existe_registro == null) {

                        $this->IDAOTipoUsuario->setTipoUsuario($data_request['data']["tipo_usuario"]);
                        $this->IDAOTipoUsuario->setDescripcion($data_request['data']["descripcion"]);
                        $this->IDAOTipoUsuario->setActivo($data_request['data']["activo"]);

                        $insert_data = $this->IDAOTipoUsuario->create();

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
                        $message = "Ya existe este registro.";
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

    public function modificar_tipo_usuario(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_tipo_usuario' => 'required',
                'tipo_usuario' => 'required',
                'descripcion' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $this->IDAOTipoUsuario->setIdCatTipoUsuario($data_request['data']["id_cat_tipo_usuario"]);
                    $existe_registro = $this->IDAOTipoUsuario->show();

                    if ($existe_registro) {

                        $this->IDAOTipoUsuario->setIdCatTipoUsuario($data_request['data']["id_cat_tipo_usuario"]);
                        $this->IDAOTipoUsuario->setTipoUsuario($data_request['data']["tipo_usuario"]);
                        $this->IDAOTipoUsuario->setDescripcion($data_request['data']["descripcion"]);
                        $this->IDAOTipoUsuario->setActivo($data_request['data']["activo"]);

                        $update_data = $this->IDAOTipoUsuario->update();

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

    public function eliminar_tipo_usuario(Request $request)
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

                    $this->IDAOTipoUsuario->setIdCatTipoUsuario($data_request['data']["id_cat_tipo_usuario"]);
                    $existe_registro = $this->IDAOTipoUsuario->show();

                    if ($existe_registro) {

                        $this->IDAOTipoUsuario->setIdCatTipoUsuario($data_request['data']["id_cat_tipo_usuario"]);
                        $delete_data = $this->IDAOTipoUsuario->delete();

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


    public function showForTypeUser()
    {
        $this->IDAOTipoUsuario->setIdCatTipoUsuario($data_request['data']["id_cat_tipo_usuario"]);
        $row = $this->IDAOTipoUsuario->show();

        return $row;
    }
}
