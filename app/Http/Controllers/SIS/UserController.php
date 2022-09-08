<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends Controller
{
    public function listar_usuarios(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $flag_request = false;

            DB::beginTransaction();

            try {

                $data_db = DB::table('user_domicilio')
                    ->select('user_domicilio.*',
                        DB::raw('CONCAT(user_domicilio.domicilio, " ", user_domicilio.numero_exterior, " ", user_domicilio.colonia, " ", user_domicilio.cp, " ", user_domicilio.ciudad  ) AS domicilio_completo')
                    )
                    ->get();

                foreach ($data_db as $row) {
                    $row->edad = Carbon::parse($row->fecha_nacimento)->age;
                }

                if ($data_db) {
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

        return $response;
    }

}
