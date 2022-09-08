<?php

namespace App\Http\Controllers\CAT;

use App\Http\Controllers\Controller;
use App\Http\Dao\Implement\IDAOCATSexo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CATSexoController extends Controller
{
    protected $IDAOCATSexo = null;

    /**
     * CATSexoController constructor.
     * @param null $IDAOCATSexo
     */
    public function __construct(IDAOCATSexo $IDAOCATSexo)
    {
        $this->IDAOCATSexo = $IDAOCATSexo;
    }

    public function listar_sexo(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            DB::beginTransaction();

            try {

                $data_db = $this->IDAOCATSexo->all();

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

    public function agregar_sexo(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_sexo' => '',
                'sexo' => 'required',
                'abreviatura' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $this->IDAOCATSexo->setSexo($data_request['data']["sexo"]);
                    $existe_registro = $this->IDAOCATSexo->showForName();

                    if ($existe_registro == null) {

                        $this->IDAOCATSexo->setSexo($data_request['data']["sexo"]);
                        $this->IDAOCATSexo->setAbreviatura($data_request['data']["abreviatura"]);
                        $this->IDAOCATSexo->setActivo($data_request['data']["activo"]);

                        $insert_data = $this->IDAOCATSexo->create();

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

    public function modificar_sexo(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_sexo' => 'required',
                'sexo' => 'required',
                'abreviatura' => 'required',
                'activo' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $this->IDAOCATSexo->setIdCatSexo($data_request['data']["id_cat_sexo"]);
                    $existe_registro = $this->IDAOCATSexo->show();

                    if ($existe_registro) {

                        $this->IDAOCATSexo->setIdCatSexo($data_request['data']["id_cat_sexo"]);
                        $this->IDAOCATSexo->setSexo($data_request['data']["sexo"]);
                        $this->IDAOCATSexo->setAbreviatura($data_request['data']["abreviatura"]);
                        $this->IDAOCATSexo->setActivo($data_request['data']["activo"]);

                        $update_data = $this->IDAOCATSexo->update();

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

    public function eliminar_sexo(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_cat_sexo' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $this->IDAOCATSexo->setIdCatSexo($data_request['data']["id_cat_sexo"]);
                    $existe_registro = $this->IDAOCATSexo->show();

                    if ($existe_registro) {

                        $this->IDAOCATSexo->setIdCatSexo($data_request['data']["id_cat_sexo"]);
                        $delete_data = $this->IDAOCATSexo->delete();

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

}
