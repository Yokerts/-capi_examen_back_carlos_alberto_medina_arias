<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SIS\CurlRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $flag_error_transaction = true;

    public function get_json_post($request)
    {

        $json = json_decode(file_get_contents('php://input'), true);

        $params = $json == null ? $request->all() : $json;

        if (!$params) {
            $data = (object)array();
        } else {
            $data = (object)$params;
        }

        $data_request = $data;

        return $data_request;

    }

    public function get_data_request($request)
    {

        $json = json_decode(file_get_contents('php://input'), true);

        $params = $json == null ? $request->all() : $json;

        if (!$params) {
            $data = array(
                'token' => NULL,
                'credenciales' => NULL,
                'data' => array()
            );
        } else {
            if (!isset($params['id_project'])) {
                $data['id_project'] = null;
            } else {
                $data['id_project'] = $params['id_project'];
            }
            if (!isset($params['token'])) {
                $data['token'] = null;
            } else {
                $data['token'] = $params['token'];
            }
            if (!isset($params['credenciales'])) {
                $data['credenciales'] = null;
            } else {
                $data['credenciales'] = $params['credenciales'];
            }
            if (!isset($params['data'])) {
                $data['data'] = array();
            } else {
                $data['data'] = $params['data'];
                if (!is_array($data['data'])) {
                    $data['data'] = array();
                }
            }
        }

        $data_request = $data;

        return $data_request;

    }

    public function token($token)
    {
        if ($token === 'token-633d2c523d43600cca8b0d1d8bb795b0') {
            return true;
        } else {
            return false;
        }
    }

    public function ErrorTransaction($e, $request)
    {
        $id_error = null;
        $message_error = $e->getMessage();
        $file_error = $e->getFile();
        $line_error = $e->getLine();
        $code_error = $e->getCode();

        if (isset($e->errorInfo)) {
            $errorInfo = $e->errorInfo;
            $no_error = $errorInfo[1];

            $errorMsg = DB::table('cat_mysql_error')
                ->where('cat_mysql_error.no_error', '=', $no_error)
                ->first();

            if ($errorMsg) {
                $id_error = $errorMsg->id_cat_mysql_error;
                $message = $errorMsg->mensaje ?? $errorMsg->message;
            } else {
                $message = $message_error;
            }
        } else {
            $no_error = 0;
            $message = $message_error;
        }


        $log = array(
            'success' => false,
            'error' => $message,

            'id' => $id_error,
            'number' => $no_error,
            'code' => $code_error,
            'file' => $file_error,
            'line' => $line_error,
            'sqlstate' => $message_error,
            'e' => $e,
        );

        DB::table('cat_mysql_log')->insertGetId([
            "webservice" => '',
            "data" => json_encode($request),
            "response" => json_encode($log),
            "created_at" => $this->DATETIME(),
            "updated_at" => $this->DATETIME()
        ]);

        return $log;
    }

    public function DATETIME()
    {
        date_default_timezone_set('America/Mexico_City');
        return date('Y-m-d H:i:s');
    }

    public function GENERARCODIGO()
    {
        $longitud = 7;
        $key = '';
        $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($pattern) - 1;
        for ($i = 0; $i < $longitud; $i++) $key .= $pattern{mt_rand(0, $max)};
//        return $key;
        return "ABCDEFG";
    }

    public function DATE()
    {
        date_default_timezone_set('America/Mexico_City');
        return date('Y-m-d');
    }

    public function TIME()
    {
        date_default_timezone_set('America/Mexico_City');
        return date('G:i:s');
    }


    public function ACCESSTOKEN($token, $credenciales, &$Usr = null, &$errors = null, $omitir = null)
    {

        $validator = Validator::make($credenciales, [
            'id_usuario' => 'required',
            'username' => 'required'
        ]);

        if (!$validator->fails()) {

            date_default_timezone_set('America/Mexico_City');

            $id_project = $credenciales['id_project'];
            $id_usuario = $credenciales['id_usuario'];
            $username = $credenciales['username'];

            $row = DB::table('usuario')
                ->select(
                    'usuario.*',
                    'cat_tipo_usuario.tipo_usuario',
                    DB::raw("cat_estado.estado as estado_nacimiento"),
                    DB::raw("cat_municipio.municipio as municipio_nacimiento"),
                    DB::raw("CONCAT(IFNULL(usuario.nombre, ''), ' ', IFNULL(usuario.apellido_paterno, ''), ' ', IFNULL(usuario.apellido_materno, '')) AS nombre_completo")
                )
                ->leftJoin("cat_estado", "cat_estado.id_cat_estado", "=", "usuario.id_cat_estado_nacimiento")
                ->leftJoin("cat_municipio", "cat_municipio.id_cat_municipio", "=", "usuario.id_cat_municipio_nacimiento")
                ->leftJoin('cat_tipo_usuario', 'cat_tipo_usuario.id_cat_tipo_usuario', '=', 'usuario.id_cat_tipo_usuario')
                ->where('usuario.id_usuario', $id_usuario)
                ->first();

            if ($row) {

                $dUsr = CurlRequest::POST($id_project, env('API_AUTH') . '_Auth_Verify_User_Token', array(
                    "token" => $token,
                    "credenciales" => array(
                        "id_user" => $row->id_user,
                        "username" => $username,
                        "omitir" => $omitir
                    )
                ));

                $id_user = null;
                $token = null;
                $token_expire = null;
                $user_token_time = (object)array();

                if ($dUsr) {
                    if (isset($dUsr->data)) {
                        if ($dUsr->data) {
                            if ($dUsr->data->id_user) {
                                $id_user = $dUsr->data->id_user;
                                $username = $dUsr->data->username;
                                $token = $dUsr->data->token;
                                $token_expire = $dUsr->data->token_expire;
                                $user_token_time = $dUsr->data->user_token_time;
                            }
                        }
                    }
                }

                if ($id_user) {

                    $flag_request = true;

                    $row->username = $username;
                    $row->token = $token;
                    $row->token_expire = $token_expire;
                    $row->nombre_completo = trim($row->nombre_completo);
                    $row->user_token_time = $user_token_time;
                    $row->id_project = $id_project;

                    $Usr = $row;

                    $Usr->id_cliente = 1;

                } else {
                    $flag_request = false;

                    $errors = $dUsr;
                }

            } else {
                $flag_request = false;
            }

        } else {

            $flag_request = false;

            $errors = $validator->errors()->messages();
        }

        return $flag_request;
    }


    public function ACCESSTOKENAUTH($id_project, $token, $credenciales, &$Usr = null, &$errors = null)
    {

        $validator = Validator::make($credenciales, [
            'id_user' => 'required',
            'username' => 'required',
            'omitir' => '',
        ]);

        if (!$validator->fails()) {

            date_default_timezone_set('America/Mexico_City');

            $id_user = $credenciales['id_user'];
            $omitir = $credenciales['omitir'] ?? null;

            $row = DB::table('user')
                ->join('token', function ($join) use ($id_project, $token, $id_user, $omitir) {
                    $join->on('token.id_user', '=', 'user.id_user');
                    $join->where('token.id_user', '=', $id_user);
                    $join->where('token.id_project', '=', $id_project);
                    if (!$omitir) {
                        $join->where('token.token', '=', $token);
                        $join->where('token.token_expire', '>=', date("Y-m-d H:i:s"));
                    }
                })
                ->join('password', function ($join) use ($id_project, $id_user) {
                    $join->on('password.id_user', '=', 'user.id_user')
                        ->where('password.id_user', '=', $id_user)
                        ->where('password.id_project', '=', $id_project);
                })
                ->where('user.id_user', $id_user)
                ->select(
                    'user.id_user',
                    'user.username',
                    'password.password',
                    'token.id_token',
                    'token.id_project',
                    'token.token',
                    'token.token_expire'
                )->first();

            if ($row) {

                /*
                 * Verifica que el usuario tenga un user_token_time con el id_proyecto, si no lo crea
                 * y se le asigna por default el id_cat_time_token 3 que tiene el valor de 30 minutos
                 * */

                $utt = DB::table('user_token_time')
                    ->select(
                        'user_token_time.*',
                        'cat_time_token.time_token',
                        'cat_time_token.time_token_update',
                        'cat_time_token.time_token_click'
                    )
                    ->leftJoin('cat_time_token', 'cat_time_token.id_cat_time_token', '=', 'user_token_time.id_cat_time_token')
                    ->where('user_token_time.id_user', $row->id_user)
                    ->where('user_token_time.id_project', $id_project)
                    ->first();

                if (!$utt) {
                    $id_user_token_time = DB::table('user_token_time')->insertGetId([
                        "id_user" => $row->id_user,
                        "id_project" => $id_project,
                        "id_cat_time_token" => 3,
                        "created_at" => $this->DATETIME(),
                        "updated_at" => $this->DATETIME()
                    ]);
                    $utt = DB::table('user_token_time')
                        ->select(
                            'user_token_time.*',
                            'cat_time_token.time_token',
                            'cat_time_token.time_token_update',
                            'cat_time_token.time_token_click'
                        )
                        ->leftJoin('cat_time_token', 'cat_time_token.id_cat_time_token', '=', 'user_token_time.id_cat_time_token')
                        ->where('user_token_time.id_user_token_time', $id_user_token_time)
                        ->first();
                }

                $TIME_TOKEN = $utt->time_token ?? env('TIME_TOKEN');
                $TIME_TOKEN_UPDATE = $utt->time_token_update ?? env('TIME_TOKEN_UPDATE');


                /*
                 * Validación para comprobar el token
                 * */

                $minutos = (strtotime($row->token_expire) - strtotime($this->DATETIME())) / 60;

                $minutos = abs($minutos);

                $minutos = floor($minutos);

                if ($minutos <= $TIME_TOKEN_UPDATE || $minutos > $TIME_TOKEN) {

                    $row->token = $token;

                    $row->token_expire = date('Y-m-d H:i:s', strtotime('+' . $TIME_TOKEN . ' minutes'));

                    DB::table('token')
                        ->where('token.id_token', '=', $row->id_token)
                        ->where('token.id_user', '=', $row->id_user)
                        ->where('token.id_project', '=', $row->id_project)
                        ->update([
                            'token_expire' => $row->token_expire
                        ]);
                }

                $row->user_token_time = $utt;

                $flag_request = true;

                $Usr = $row;

            } else {
                $flag_request = false;
            }

        } else {

            $flag_request = false;

            $errors = $validator->errors()->messages();
        }

        return $flag_request;
    }

    function ChangeDateFormat($fecha)
    {

        $sD = explode('-', $fecha);
        $mes = $this->GetMonthByKey(intval($sD[1]));
        $fecha = $sD[2] . '-' . substr($mes, 0, 3) . '-' . $sD[0];

        return $fecha;

    }

    function GetMonthByKey($key)
    {

        $months = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
            'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

        return $months[$key];

    }

    public function GetUserNamePasswordRegister($username)
    {
        return $this->GetUserNamePassword($username, env('PASSWORD_REGISTER'));
    }

    public function GetUserNamePassword($username, $password)
    {
        //return md5(env('KEY_LOGIN') . $username . $password);
        return md5(env('KEY_LOGIN') . $password);
    }

    public function Base64ToFile($b64_archivo, $ruta_archivo, $nombre_archivo, $tipo_archivo)
    {

        $base_file = $this->DOC_ROOT_IMAGE();

        $dirname = $base_file . $ruta_archivo;

        if (!is_dir($dirname)) {
            if (!mkdir($dirname, 0777, true) && !is_dir($dirname)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirname));
            }
            chmod($dirname, 0777);
        }

        $data = base64_decode($b64_archivo);

        $archivo = $nombre_archivo . $this->DATETIMEUNIX() . '.' . $tipo_archivo;

        $ruta = $ruta_archivo . $archivo;

        $filepath = $base_file . $ruta;

        file_put_contents($ruta, $data);

        if (file_exists($filepath)) {
            $result = array(
                'success' => true,
                'ruta' => $ruta,
                'message' => 'Archivo generado con éxito'
            );
        } else {
            $result = array(
                'success' => false,
                'ruta' => NULL,
                'message' => 'No se genero el archivo'
            );
        }

        return $result;
    }

    /*
     * @ Funcionalidad para convertir base 64 a archivos
     * */

    public function DOC_ROOT_IMAGE()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->GetPath();
    }

    public function GetPath()
    {
        // $URLruta = $_SERVER['REQUEST_URI'];
        // $URLruta = $_SERVER['SCRIPT_NAME'];
        $URLruta = $_SERVER['PHP_SELF'];
        $URLruta = str_replace($this->HTTProtocol() . '://' . $_SERVER['HTTP_HOST'], "", $URLruta);
        $URLruta = str_replace('index.php/', "", $URLruta);
        $URLruta = str_replace('index.php', "", $URLruta);

        return $URLruta;
    }

    public function HTTProtocol()
    {
        return isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
    }

    public function DATETIMEUNIX()
    {
        date_default_timezone_set('America/Mexico_City');
        return strtotime(date('Y-m-d H:i:s'));
    }

    public function input($value)
    {
        $v = NULL;
        if ($value === 0 || $value === false) {
            $v = $value;
        } else if (!empty($value)) {
            $v = $value;
        }
        return $v;
    }

    public function GenerarFolio($ultimo, $digits = 8)
    {
        $folio = NULL;
        $n = (int)$ultimo;
        if (is_numeric($n)) {
            if (!is_nan($n)) {
                $folio = str_pad($n + 1, $digits, "0", STR_PAD_LEFT);
            }
        }
        return $folio;
    }

    public function CadenaDomiilio($domicilio)
    {


        $direccion = "";

        if ($domicilio['calle']) {
            $direccion = $direccion . $domicilio['calle'] . " ";
        }

        if ($domicilio['numero_exterior']) {
            $direccion = $direccion . "no. ext. " . $domicilio['numero_exterior'] . ", ";
        }

        if ($domicilio['numero_interior']) {
            $direccion = $direccion . "no. int. " . $domicilio['numero_interior'] . " ";
        }

        if ($domicilio['colonia']) {
            $direccion = $direccion . $domicilio['colonia'] . " ";
        }

        if ($domicilio['codigo_postal']) {
            $direccion = $direccion . "C.P. " . $domicilio['codigo_postal'] . ", ";
        }

        if ($domicilio['municipio']) {
            $direccion = $direccion . $domicilio['municipio'] . ", ";
        }

        if ($domicilio['estado']) {
            $direccion = $direccion . $domicilio['estado'] . ".";
        }

        return $direccion;
    }

    public function FechaTexto($fecha)
    {
        $F = explode('-', $fecha);
        if (count($F) === 3) {
            $A = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            if ((int)$F[0] > (int)$F[2]) {
                return $F[2] . '-' . $A[(int)$F[1]] . '-' . $F[0];
            } else {
                return $F[0] . '-' . $A[(int)$F[1]] . '-' . $F[2];
            }
        } else {
            return $fecha;
        }
    }


    public function Number($monto, $decimal = 6, $sep = '')
    {
        $num = number_format($monto, $decimal, '.', $sep);
        return (double)$num;
    }
}
