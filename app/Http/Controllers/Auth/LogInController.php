<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LogInController extends Controller
{

    public function UserLogIn(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $data_request = $this->get_data_request($request);

        $validator = Validator::make($data_request['data'], [
            'username' => 'required',
            'password' => 'required',
        ]);

        $flag_request = false;

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $id_project = $data_request["id_project"] ?? 2;
                $username = $data_request['data']["username"];
                $password = $this->GetUserNamePassword($data_request['data']["username"], $data_request['data']["password"]);

                if ($id_project > 0) {

                    /*
                     * Verifica que exista el usuario
                     * */

                    $row = DB::table('user')
                        ->where('user.username', $username)
                        ->select('user.*')
                        ->first();


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
                         * Verifica que el usuario tenga un password con el id_proyecto, si no lo crea
                         * y se le asigna un password inicial par tal proyecto
                         * */

                        $pw = DB::table('password')
                            ->where('password.id_user', $row->id_user)
                            ->where('password.id_project', $id_project)
                            ->select('password.*')
                            ->first();

                        if (!$pw) {

                            $PWS = $this->GetUserNamePasswordRegister($data_request['data']["username"]);

                            DB::table('password')->insertGetId([
                                "id_user" => $row->id_user,
                                "id_project" => $id_project,
                                "password" => $PWS,
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                        }

                        /*
                         * Realiza la búsqueda de usuarios para el inicio de sesión
                         * */

                        $row = DB::table('user')
                            ->join('password', function ($join) use ($id_project, $password) {
                                $join->on('password.id_user', '=', 'user.id_user')
                                    ->where('password.id_project', '=', $id_project)
                                    ->where('password.password', '=', $password);
                            })
                            ->where('user.username', $username)
                            ->select(
                                'user.id_user',
                                'user.username',
                                'password.id_password',
                                'password.password',
                                'password.id_project'
                            )
                            ->first();

                        if ($row) {

                            $tk = DB::table('token')
                                ->where('token.id_user', $row->id_user)
                                ->where('token.id_project', $id_project)
                                ->select('token.*')
                                ->first();

                            $row->token = md5($row->id_user) . md5(date('Y-m-d H:i:s'));
                            $row->token_expire = date('Y-m-d H:i:s', strtotime('+' . $TIME_TOKEN . ' minutes'));

                            $update_tk = NULL;
                            $insert_tk = NULL;

                            if ($tk) {
                                $update_tk = DB::table('token')
                                    ->where('token.id_user', $row->id_user)
                                    ->where('token.id_project', $id_project)
                                    ->update([
                                        'token' => $row->token,
                                        'token_expire' => $row->token_expire,
                                        "updated_at" => $this->DATETIME()
                                    ]);
                            } else {
                                $insert_tk = DB::table('token')
                                    ->insertGetId([
                                        "id_user" => $row->id_user,
                                        "id_project" => $id_project,
                                        "token" => $row->token,
                                        "token_expire" => $row->token_expire,
                                        "created_at" => $this->DATETIME(),
                                        "updated_at" => $this->DATETIME()
                                    ]);
                            }

                            $row->user_token_time = $utt;

                            if ($update_tk > 0 || $insert_tk > 0) {
                                $flag_request = true;
                                $status = 200;
                                $message = "Inicio de sesión correcto.";
                                $data = $row;
                                DB::commit();
                            } else {
                                $flag_request = false;
                                $status = 400;
                                $message = "Error al iniciar sesión.";
                                $data = array();
                                DB::commit();
                            }

                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Credenciales invalidas.";
                            $data = array();
                            DB::commit();
                        }

                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El usuario no se encuentra registrado.";
                        $data = array();
                        DB::commit();
                    }

                } else {
                    $flag_request = false;
                    $status = 400;
                    $message = "Required field id_project";
                    $data = array();
                    DB::commit();
                }

                $response = [
                    "success" => $flag_request,
                    "status" => $status,
                    "message" => $message,
                    "data" => $data
                ];

            } catch (\Exception $e) {
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

        return $response;
    }

}
