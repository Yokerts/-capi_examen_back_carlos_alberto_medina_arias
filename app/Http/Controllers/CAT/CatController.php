<?php

namespace App\Http\Controllers\CAT;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CatController extends Controller
{

    public static function cat_config()
    {
        $response = [
            "como_te_enteraste" => true,
            "factura_cotizacion_nota_sencilla_interno" => "nota_sencilla"
        ];

        return $response;
    }

    public static function cat_periodo_pago()
    {
        $data_db = DB::table('cat_periodo_pago')
            ->select('*')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_ficha_tecnica_status_cliente_potencial()
    {
        $data_db = DB::table('cat_ficha_tecnica_status')
            ->whereIn('id_cat_ficha_tecnica_status', [1, 2, 3, 4, 5, 6])
            ->select('*')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_ficha_tecnica_status_cliente_cliente()
    {
        $data_db = DB::table('cat_ficha_tecnica_status')
            ->whereIn('id_cat_ficha_tecnica_status', [7, 8, 9, 10, 11, 12])
            ->select('*')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_ficha_tecnica_status()
    {
        $data_db = DB::table('cat_ficha_tecnica_status')->select('*')->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_sexo()
    {
        $data_db = DB::table('cat_sexo')->select('*')->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_tipo_usuario($aqui = false, $id_cat_tipo_usuario = false)
    {
        if ($id_cat_tipo_usuario === 1) {
            $data_db = DB::table('cat_tipo_usuario')
                ->where('activo', '=', 1)
                ->select('*')->get();
        } else {
            $data_db = DB::table('cat_tipo_usuario')
                ->where('id_cat_tipo_usuario', '!=', 1)
                ->where('activo', '=', 1)
                ->select('*')->get();
        }

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        if ($aqui) {
            return $data;
        } else {
            return $response;
        }
    }

    public static function cat_giro_cliente()
    {
        $data_db = DB::table('cat_giro_cliente')
            ->where('id_cat_giro_cliente', '!=', 1)
            ->where('activo', '=', 1)
            ->select('*')
            ->orderBy('giro_cliente')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_estado()
    {
        $data_db = DB::table('cat_estado')
            ->select('*')
            ->orderBy('estado')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_municipio($id_estado = null)
    {
        if (!isset($id_estado)) {
            $data_db = DB::table('cat_municipio')
                ->select('*')->orderBy('municipio')->get();
        } else {
            $data_db = DB::table('cat_municipio')
                ->where('id_cat_estado', '=', $id_estado)
                ->select('*')->orderBy('municipio')->get();
        }

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_usuario_ejecutivo()
    {
        $data_db = DB::table('usuario')
            ->select('*')
            ->where('id_cat_tipo_usuario', '<>', 1)
            ->where('id_cat_tipo_usuario', '<>', 2)
            ->where('id_cat_tipo_usuario', '<>', 3)
            ->orderBy('nombre')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_usuario_promotor()
    {
        $data_db = DB::table('usuario')
            ->select('usuario.*', DB::raw("CONCAT(IFNULL(usuario.nombre, ''), ' ', IFNULL(usuario.apellido_paterno, ''), ' ', IFNULL(usuario.apellido_materno, '')) AS nombre_completo"))
            ->where('id_cat_tipo_usuario', '=', 3)
            ->where('activo', '=', 1)
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_cliente_interna_plaza_direccion()
    {
        $data_db = DB::table('cliente_interna_plaza_direccion')
            ->select(
                'cliente_interna_plaza_direccion.*',
                'cliente_interna_plaza.*',
                'cliente_interna.*'
            )
            ->leftJoin('cliente_interna_plaza', 'cliente_interna_plaza.id_cliente_interna_plaza', '=', 'cliente_interna_plaza_direccion.id_cliente_interna_plaza')
            ->leftJoin('cliente_interna', 'cliente_interna.id_cliente_interna', '=', 'cliente_interna_plaza.id_cliente_interna')
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }

    public static function cat_tipo_sangre()
    {
        $data_db = DB::table('cat_tipo_sangre')
            ->select('cat_tipo_sangre.*')
            ->where('activo', '=', 1)
            ->get();

        if ($data_db == null) {
            $flag_request = true;
            $status = 400;
            $message = "No se encontraron datos.";
            $data = array();
        } else {
            $flag_request = true;
            $status = 200;
            $message = "Datos encontrados.";
            $data = $data_db;
        }

        $response = [
            "success" => $flag_request,
            "status" => $status,
            "message" => $message,
            "data" => $data
        ];

        return $response;
    }


}
