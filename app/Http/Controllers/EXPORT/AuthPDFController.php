<?php

namespace App\Http\Controllers\EXPORT;

use App\Http\Controllers\Controller;

class AuthPDFController extends Controller
{

    public static $KEY_CONST = 'lrd.mx';

    public function AuthToken($value, &$Usr = null, &$errors = null)
    {
        $a = explode(self::keyTxt(), $value);
        $b = $a[count($a) - 1];
        $c = explode(strrev(self::keyTxt()), $b);
        $d = strrev($c[0]);
        $e = base64_decode($d);
        $obj = self::jsonDecode($e);

        if ($obj['success']) {
            $data_request = $obj['request'];
            if(!isset($data_request['data']['auth'])) {
                $data_request['data']['auth'] = true;
            }
            if ($data_request['data']['auth'] === false) {
                $obj['access'] = true;
                $obj['Usr'] = array();
                $obj['errors'] = array();
            } else {
                if ($this->ACCESSTOKEN($data_request['token'], $data_request['credenciales'], $Usr, $errors)) {
                    $obj['access'] = true;
                    $obj['Usr'] = $Usr;
                    $obj['errors'] = $errors;
                } else {
                    $obj['access'] = false;
                    $obj['Usr'] = $Usr;
                    $obj['errors'] = $errors;
                }
            }
        }
        return $obj;
    }

    public static function keyTxt()
    {
        return base64_encode(self::$KEY_CONST);
    }

    public static function jsonDecode($string_json)
    {

        $json = $string_json;

        $obj = json_decode($json, true);
        $error = json_last_error();
        $errorSmg = json_last_error_msg();

        switch ($error) {
            case JSON_ERROR_NONE:
                $obj = array(
                    'success' => true,
                    'message' => 'OK',
                    'jsonError' => null,
                    'request' => $obj
                );
                break;
            case JSON_ERROR_DEPTH:
                $obj = array(
                    'success' => false,
                    'message' => 'Excedido tamaño máximo de la pila',
                    'jsonError' => $errorSmg,
                    'request' => $json
                );
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $obj = array(
                    'success' => false,
                    'message' => 'Desbordamiento de buffer o los modos no coinciden',
                    'jsonError' => $errorSmg,
                    'request' => $json
                );
                break;
            case JSON_ERROR_CTRL_CHAR:
                $obj = array(
                    'success' => false,
                    'message' => 'Encontrado carácter de control no esperado',
                    'jsonError' => $errorSmg,
                    'request' => $json

                );
                break;
            case JSON_ERROR_SYNTAX:
                $obj = array(
                    'success' => false,
                    'message' => 'Error de sintaxis, JSON mal formado',
                    'jsonError' => $errorSmg,
                    'request' => $json
                );
                break;
            case JSON_ERROR_UTF8:
                $obj = array(
                    'success' => false,
                    'message' => 'Caracteres UTF-8 malformados, posiblemente codificados de forma incorrecta',
                    'jsonError' => $errorSmg,
                    'request' => $json
                );
                break;
            default:
                $obj = array(
                    'success' => false,
                    'message' => 'Error desconocido',
                    'jsonError' => $errorSmg,
                    'request' => $json
                );
                break;
        }

        return $obj;
    }
}
