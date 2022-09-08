<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function VerifyUserToken(Request $request)
    {

        $data_request = $this->get_data_request($request);

        $Usr = null;
        $errors = null;
        if ($this->ACCESSTOKENAUTH($data_request['id_project'], $data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $response = array(
                'success' => true,
                'status' => 200,
                'message' => 'Acceso permitido.',
                "data" => $Usr
            );

        } else {

            $response = array(
                'success' => false,
                'status' => 400,
                'message' => 'Acceso denegado, token auth no válido',
                "errors" => $errors
            );

        }

        return $response;
    }

    public function VerifyUserNew(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKENAUTH($data_request['id_project'], $data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_user' => '',
                'username' => 'required',
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $row = DB::table('user')
                        ->where('user.username', $data_request['data']["username"])
                        ->select('*')
                        ->first();

                    if ($row == null) {
                        $id_user = DB::table('user')
                            ->insertGetId([
                                "username" => $data_request['data']['username'],
                                "created_at" => $this->DATETIME(),
                                "updated_at" => $this->DATETIME()
                            ]);
                        if ($id_user > 0) {

                            $this->VerifyUserPassword($data_request["id_project"], $id_user, $data_request['data']["username"]);

                            $flag_request = true;
                            $status = 200;
                            $message = "Datos guardados con éxito.";
                            $data = array(
                                "id_user" => $id_user
                            );
                            DB::commit();
                        } else {
                            $flag_request = false;
                            $status = 400;
                            $message = "Error al guardar los datos.";
                            $data = array();
                            DB::rollback();
                        }
                    } else {

                        $this->VerifyUserPassword($data_request["id_project"], $row->id_user, $data_request['data']["username"]);

                        $flag_request = true;
                        $status = 200;
                        $message = "Datos encontrados con éxito.";
                        $data = array(
                            "id_user" => $row->id_user
                        );
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

        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token auth no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

    public function VerifyUserPassword($id_project, $id_user, $username)
    {
        if ($id_project > 0) {

            /*
             * Verifica que el usuario tenga un password con el id_proyecto, si no lo crea
             * y se le asigna un password inicial par tal proyecto
             * */

            $pw = DB::table('password')
                ->where('password.id_user', $id_user)
                ->where('password.id_project', $id_project)
                ->select('password.*')
                ->first();

            if (!$pw) {

                $PWS = $this->GetUserNamePasswordRegister($username);

                DB::table('password')->insertGetId([
                    "id_user" => $id_user,
                    "id_project" => $id_project,
                    "password" => $PWS,
                    "created_at" => $this->DATETIME(),
                    "updated_at" => $this->DATETIME()
                ]);
            }

        }
    }

    public function VerifyUserId(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        $validator = Validator::make($data_request['data'], [
            'username' => 'required',
        ]);

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $row = DB::table('user')
                    ->where('user.username', $data_request['data']["username"])
                    ->select('*')
                    ->first();

                if ($row) {

                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados con éxito.";
                    $data = array(
                        "id_user" => $row->id_user,
                        "username" => $row->username
                    );
                    DB::commit();

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "El usuario no existe.";
                    $data = array();
                    DB::rollback();

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

    public function UserNew(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        $validator = Validator::make($data_request['data'], [
            'id_user' => '',
            'username' => 'required',
        ]);

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $row = DB::table('user')
                    ->where('user.username', $data_request['data']["username"])
                    ->select('*')
                    ->first();

                if ($row == null) {
                    $id_user = DB::table('user')
                        ->insertGetId([
                            "username" => $data_request['data']['username'],
                            "created_at" => $this->DATETIME(),
                            "updated_at" => $this->DATETIME()
                        ]);
                    if ($id_user > 0) {

                        $this->VerifyUserPassword($data_request["id_project"], $id_user, $data_request['data']["username"]);

                        $flag_request = true;
                        $status = 200;
                        $message = "Datos guardados con éxito.";
                        $data = array(
                            "id_user" => $id_user,
                            "nuevo_registro" => 1
                        );
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "Error al guardar los datos.";
                        $data = array();
                        DB::rollback();
                    }
                } else {

                    $this->VerifyUserPassword($data_request["id_project"], $row->id_user, $data_request['data']["username"]);

                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados con éxito.";
                    $data = array(
                        "id_user" => $row->id_user,
                        "nuevo_registro" => 0
                    );
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

    public function UserChangePassword(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        $validator = Validator::make($data_request['data'], [
            'id_user' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $id_user = $data_request['data']['id_user'];
                $id_project = $data_request['id_project'];
                $username = $data_request['data']['username'];
                $password = $this->GetUserNamePassword($username, $data_request['data']["password"]);

                $row = DB::table('user')
                    ->where('user.username', $username)
                    ->select('*')
                    ->first();

                if ($row) {

                    $update_data = DB::table('password')
                        ->where('password.id_user', $id_user)
                        ->where('password.id_project', $id_project)
                        ->update([
                            "password" => $password
                        ]);

                    if ($update_data) {

                        $flag_request = true;
                        $status = 200;
                        $message = "Contraseña actualizado con éxito.";
                        $data = array(
                            "id_user" => $id_user,
                            "id_project" => $id_project,
                            "username" => $username,
                            "password" => $password
                        );
                        DB::commit();

                    } else {

                        $flag_request = false;
                        $status = 400;
                        $message = "La contraseña no fue actualizado o esta contraseña ya fue usada anteriormente, intenta nuevamente con otra.";
                        $data = array();
                        DB::rollback();

                    }

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "El usuario no existe.";
                    $data = array();
                    DB::rollback();

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

    public function UserChangeTimeToken(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        $validator = Validator::make($data_request['data'], [
            'id_user' => 'required',
            'id_cat_time_token' => 'required',
        ]);

        if (!$validator->fails()) {

            DB::beginTransaction();

            try {

                $id_user = $data_request['data']['id_user'];
                $id_project = $data_request['id_project'];
                $id_cat_time_token = $data_request['data']['id_cat_time_token'];

                $row = DB::table('user')
                    ->where('user.id_user', $id_user)
                    ->select('*')
                    ->first();

                if ($row) {

                    $update_data = DB::table('user_token_time')
                        ->where('user_token_time.id_user', $id_user)
                        ->where('user_token_time.id_project', $id_project)
                        ->update([
                            "id_cat_time_token" => $id_cat_time_token
                        ]);

                    if ($update_data) {
                        $flag_request = true;
                        $status = 200;
                        $message = "El tiempo para expirar el token fue actualizado con éxito.";
                        $data = array(
                            "id_user" => $id_user,
                            "id_project" => $id_project,
                            "id_cat_time_token" => $id_cat_time_token,
                        );
                        DB::commit();
                    } else {
                        $flag_request = false;
                        $status = 400;
                        $message = "El tiempo para expirar el token no fue actualizado o ya se encuentra seleccionado esta opción, intenta nuevamente.";
                        $data = array();
                        DB::rollback();
                    }

                } else {

                    $flag_request = false;
                    $status = 400;
                    $message = "El usuario no existe.";
                    $data = array();
                    DB::rollback();

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

    public function UserList(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKENAUTH($data_request['id_project'], $data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_user' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $id_project = $data_request['id_project'];

                    $result = DB::table('user')
                        ->leftJoin('token', function ($join) use ($id_project) {
                            $join->on('token.id_user', '=', 'user.id_user')
                                ->where('token.id_project', '=', $id_project);
                        })
                        ->leftJoin('password', function ($join) use ($id_project) {
                            $join->on('password.id_user', '=', 'user.id_user')
                                ->where('password.id_project', '=', $id_project);
                        })
                        ->whereNotIn('user.id_user', [1, 2])
                        ->select(
                            'user.id_user',
                            'password.id_password',
                            'token.id_token',
                            'user.username',
                            'password.password',
                            'token.token',
                            'token.token_expire'
                        )
                        ->get();

                    if (count($result) > 0) {

                        $flag_request = true;
                        $status = 200;
                        $message = "Datos encontrados.";
                        $data = $result;
                        DB::commit();

                    } else {

                        $flag_request = false;
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

        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token auth no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

    public function CatList(Request $request)
    {
        $data_request = $this->get_data_request($request);

        $flag_request = false;

        if ($this->ACCESSTOKENAUTH($data_request['id_project'], $data_request['token'], $data_request['credenciales'], $Usr, $errors)) {

            $validator = Validator::make($data_request['data'], [
                'id_user' => 'required'
            ]);

            if (!$validator->fails()) {

                DB::beginTransaction();

                try {

                    $id_user = $data_request['data']['id_user'];
                    $id_project = $data_request['id_project'];

                    $result = (object)array(
                        'id_user' => $id_user,
                        'id_project' => $id_project,
                    );

                    $result->user_token_time = DB::table('user_token_time')
                        ->select(
                            'user_token_time.id_user_token_time',
                            'user_token_time.id_project',
                            'user_token_time.id_user',
                            'user_token_time.id_cat_time_token',
                            'cat_time_token.time_token',
                            'cat_time_token.time_token_update',
                            'cat_time_token.time_token_click'
                        )
                        ->leftJoin('cat_time_token', 'user_token_time.id_cat_time_token', '=', 'cat_time_token.id_cat_time_token')
                        ->where('user_token_time.id_user', '=', $id_user)
                        ->where('user_token_time.id_project', '=', $id_project)
                        ->first();

                    $result->cat_time_token = DB::table('cat_time_token')->select('cat_time_token.*')->get();

                    $flag_request = true;
                    $status = 200;
                    $message = "Datos encontrados.";
                    $data = $result;
                    DB::commit();

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

        } else {
            $response = array(
                'success' => $flag_request,
                'status' => 400,
                'message' => 'Acceso denegado, token auth no válido',
                "errors" => $errors
            );
        }

        return $response;
    }

}
