<?php

namespace App\Http\Controllers\SIS;

use App\Http\Controllers\Controller;

class CurlRequest extends Controller
{

    public static function POST($id_project, $url, $data)
    {
        $data['id_project'] = $id_project;

        $request = new \Curl\Curl();
        $request->post($url, $data);
        $request->close();
        $result = (string)$request->response;
        $data = json_decode($result);
        if (!(json_last_error() == JSON_ERROR_NONE)) {
            $data = $result;
        }

        return $data;

//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json; charset=utf-8'
//        ));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($ch, CURLOPT_HEADER, FALSE);
//        curl_setopt($ch, CURLOPT_POST, TRUE);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//
//        $response = curl_exec($ch);
//
//        curl_close($ch);
//
//        $result = (string)$response;
//        $data = json_decode($result);
//        if (!(json_last_error() == JSON_ERROR_NONE)) {
//            $data = $result;
//        }
//        return $data;
    }

    public static function GET($id_project, $url, $data)
    {
        $data['id_project'] = $id_project;
        $request = new \Curl\Curl();
        $request->get($url, $data);
        $result = (string)$request->response;
        $request->close();
        $data = json_decode($result);
        if (!(json_last_error() == JSON_ERROR_NONE)) {
            $data = $result;
        }
        return $data;
    }


}

?>


