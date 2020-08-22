<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use App\User;

class JwtAuth
{
    public function __construct()
    {
        $this->key = "clave_secreta-1234";
    }

    public function signup($email, $password, $getToken = null)
    {
        //Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();


        //Comprobar si el objeto es correcto
        $signup = false;

        if (is_object($user)) {
            $signup = true;
        }

        // Generar el token con los datos del user identificado
        if ($signup) {
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Devuelve los datos descodificados del token
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }

        } else {
            $data = [
                'status' => 'error',
                'message' => 'Login incorrecto'
            ];
        }


        return $data;
    }


    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"','', $jwt); // Limpia " "
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }


        return $auth;
    }

}

