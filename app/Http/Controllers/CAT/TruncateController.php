<?php

namespace App\Http\Controllers\CAT;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;


class TruncateController extends Controller
{
    public function ClearFichaTecnica()
    {
        DB::beginTransaction();

        try {

            DB::table('ficha_tecnica_archivo_elaboracion_propuesta')->truncate();
            DB::table('ficha_tecnica_archivo_propuesta_fiscal')->truncate();
            DB::table('ficha_tecnica_historial_accion_usuario')->truncate();
            DB::table('ficha_tecnica_otro_impuesto')->truncate();
            DB::table('ficha_tecnica_prestacion')->truncate();
            DB::table('ficha_tecnica_propuesta_fiscal')->truncate();
            DB::table('plaza_opera_nomina')->truncate();
            DB::table('plaza_pertenece_cliente')->truncate();
            $ok1 = DB::table('ficha_tecnica')->delete();
            $ok2 = DB::table('cliente')->where('id_cliente', '<>', 1)->delete();

            if ($ok1 && $ok2) {
                $response = [
                    "success" => true,
                    "status" => 200,
                    "message" => "Correcto",
                    "data" => array()
                ];
                DB::commit();
            } else {
                $response = [
                    "success" => false,
                    "status" => 400,
                    "message" => "Incorrecto",
                    "data" => array()
                ];
                DB::rollBack();
            }

        } catch (Exception $e) {
            DB::rollback();
            $log = $this->ErrorTransaction($e, $request);
            return $log;
        }

        return $response;
    }
}
