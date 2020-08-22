<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Modelo
use App\User;

class UserController extends Controller
{
    //Test
    public function test(Request $request)
    {
        return "Test UserController";
    }

    public function register(Request $request)
    {
        // Recoge datos usuario
        $json = $request->input('json', null);

        // Convierte a objeto y array
        $params = json_decode($json); // Objeto
        $params_array = json_decode($json, true); // Array

        // Si params está vacío
        if (!empty($params) && !empty($params_array)) {
            // Limpia los datos
            $params_array = array_map('trim', $params_array);

            // Valida datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                // Comprobar si existe el usuario
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);


            if ($validate->fails()) {
                // Si la validación falla
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                ];
            } else {
                // Si la validación es correcta

                // Cifra contraseña
                $pwd = hash('sha256', $params->password);

                // Crea el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                // Guarda el usuario
                $user->save();


                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario ha creado correctamente',
                    'user' => $user
                ];
            }


        } else {
            //Si lso datos se envian incorrectamente
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            ];
        }


        // Devuelve la respuesta por defecto
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth();

        // Recibe datos por método POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);


        // Valida los datos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);


        if ($validate->fails()) {
            // Si la validación falla
            $signup = [
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            ];

        } else {
            // Si la validación es correcta
            // Cifra la contraseña
            $pwd = hash('sha256', $params->password);

            // Devuelve el token o los datos
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        // Devuelve JSON
        return response()->json($signup, 200);
    }


    public function update(Request $request)
    {
        // Comprueba si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        // Recoge los datos del método POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {
            // Actualiza el usuario
            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Valida los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users' . $user->sub
            ]);

            // Quita los campos que no se quieren actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar user en BD
            $user_update = User::where('id', $user->sub)->update($params_array);

            // Devolver el array $data
            $data = [
                'code' => 200,
                'status' => 'access',
                'user' => $user,
                'changes' => $params_array
            ];

        } else {
            // No lo actualiza
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado',
            ];
        }

        return response()->json($data, $data['code']);
    }


    public function upload(Request $request)
    {
        // Recoge los datos de la petición
        $image = $request->file('file0');

        // Valida que sea imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.',
            ];

        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')
                ->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            ];

        }


        return response($data, $data['code']);

    }

    public function getImage($filename)
    {
        $exists = \Storage::disk('users')
            ->exists($filename);

        if ($exists) {
            $file = \Storage::disk('users')
                ->get($filename);
            return new Response($file, 200);

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            ];

            return response()->json($data, $data['code']);
        }

    }

    public function getUserDetail($id)
    {
        $user = User::find($id);

        if(is_object($user)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user,
            ];

        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'user' => 'El usuario no existe.',
            ];
        }

        return response()->json($data, $data['code']);


    }

}

