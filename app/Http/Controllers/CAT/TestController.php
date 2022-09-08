<?php

namespace App\Http\Controllers\CAT;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SIS\CurlRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class TestController extends Controller
{
    public function Test(Request $request)
    {

        $data_request = $this->get_data_request($request);
        $id_project = $data_request['credenciales']['id_project'];

        $dUsr[] = CurlRequest::POST($id_project, env('API_AUTH') . '_Test_Auth', array(
            "data" => array(
                "from" => 'api_integraciones',
                "db" => env('DB_DATABASE'),
                "params" => $data_request
            )
        ));

        return $dUsr;
    }

    public function get_test()
    {
        $arr = array(
            array(
                'id_test' => 1,
                'test' => 'Test 1'
            ),
            array(
                'id_test' => 2,
                'test' => 'Test 2'
            ),
            array(
                'id_test' => 3,
                'test' => 'Test 3'
            ),
            array(
                'id_test' => 4,
                'test' => 'Test 4'
            )
        );

        $fecha = date('Y-m-d H:i:s');

        foreach ($arr as $key => $row) {
            $arr[$key]['fecha'] = $fecha;
            $fecha = date('Y-m-d H:i:s', strtotime('+1 seconds', strtotime($fecha)));
        }

        return $arr;
    }

    public function post_test()
    {
        $arr = array(
            array(
                'id_test' => 1,
                'test' => 'Test 1'
            ),
            array(
                'id_test' => 2,
                'test' => 'Test 2'
            )
        );
        return $arr;
    }

    public function cat_mysql_log()
    {
        $arr = array(
            array(
                'id_test' => 1,
                'test' => 'Test 1'
            ),
            array(
                'id_test' => 2,
                'test' => 'Test 2'
            )
        );
        return $arr;
    }

    public function base64()
    {

        $baseFromJavascript = "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";

        $data = base64_decode($baseFromJavascript);

        $filepath = $this->DOC_ROOT_IMAGE() . "/image/image.png";

        file_put_contents($filepath, $data);

        return array(
            array(
                'success' => true,
                'message' => 'Imagen subida con éxito'
            ),
        );
    }

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

        $URLruta = $URLruta . 'file/';

        return $URLruta;
    }

    public function HTTProtocol()
    {
        return isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
    }

    public function sendEmailReminder($email = null, $name = null)
    {

        $user = (object)array(
            'name' => $name ?? 'Carlos Alberto Medina Arias',
            'email' => $email ?? 'ing_medina16@hotmail.com',
        );

        Mail::send('emails-bienvenido', ['user' => $user->name], function ($m) use ($user) {
            $m->from(env('MAIL_USERNAME'), env('FROM_NAME_SOUPORT'));

            $m->to($user->email, $user->name)->subject('Bienvenido!!');
        });
        dd('Correo electrónico enviado.');
    }
}
