<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SendMailController extends Controller
{

    public function SendEmail(Request $request)
    {

        $flag_request = false;

        $data = $this->get_json_post($request);

        $validator = Validator::make((array)$data, [
            'from' => 'required|email',
            'from_name' => 'required',
            'to' => 'required|email',
            'to_name' => 'required',
            'subject' => 'required',
            'message' => 'required'
        ]);

        if (!$validator->fails()) {

            try {

                $from = $data->from;
                $from_name = $data->from_name;
                $to = $data->to;
                $to_name = $data->to_name;
                $subject = $data->subject;
                $_message = $data->message;

                Mail::send('emails-change', [
                    '_from' => $from,
                    '_from_name' => $from_name,
                    '_to' => $to,
                    '_to_name' => $to_name,
                    '_subject' => $subject,
                    '_message' => $_message
                ], function ($m) use ($from, $from_name, $to, $to_name, $subject, $_message) {
                    $m->from($from ?? env('MAIL_USERNAME'), $from_name ?? env('FROM_NAME_SOUPORT'));
                    $m->to($to, $to_name)->subject($subject ?? '');
                });

                $response = [
                    "success" => true,
                    "status" => 200,
                    "message" => "Hemos recibido sus datos con éxito, nos contactaremos contigo a la mayor brevedad posible.",
                    "errors" => array(),
                ];

            } catch (\Exception $e) {
                $log = $this->ErrorTransaction($e, $request);
                return $log;
            }
        } else {
            $response = [
                "success" => $flag_request,
                "status" => 400,
                "message" => "Comprueba que los datos sean correctos.",
                "errors" => $validator->errors()->messages()
            ];
        }

        return $response;

    }
}

