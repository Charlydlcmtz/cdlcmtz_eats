<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'cdlcmtz-eats-web-758458934753987493';
    }

    public function signup($correo, $password, $id_rol = 2){
        try {

            $data = null;
            $model = $id_rol === 4 ? Empresa::class : User::class;
            $entity = $model::with('role:id,nombre')->where("correo", $correo)->first();

            if (!$entity) {
                throw new \Exception ('Correo no registrado');
            }

            if (!Hash::check($password, $entity->password)) {
                throw new \Exception ('Contraseña incorrecta');
            }

            $token = [
                'sub' => $entity->id,
                'correo' => $entity->correo,
                'nombre' => $entity->nombre,
                'username' => $id_rol === 4 ? $entity->nombre : $entity->username,
                'role' => $entity['role'],
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60) // Expira en 7 días
            ];

            if ($id_rol === 4) {
                $token['rfc'] = $entity->rfc;
                $token['icon'] = $entity->icon;
                $token['colors'] = $entity->colors;
            } else {
                $token['img_user'] = $entity->img_user;
                $token['no_empleado'] = $entity->no_empleado;
                $token['id_empresa'] = $entity->id_empresa;
            }

            $jwt = JWT::encode($token, $this->key, 'HS256');

            return [
                'token' => $jwt,
                'data' => $entity,
                'estatus' => 'success',
                'codigo' => 200
            ];
        } catch (\Throwable $th) {
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }
        return $data;
    }

    public function renewToken($jwt, $id_rol = 2){
        try {
            $data = null;
            $auth = false;
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            $token = [
                'sub' => $decoded->sub,
                'correo' => $decoded->correo,
                'nombre' => $decoded->nombre,
                'username' => $id_rol === 4 ? $decoded->nombre : $decoded->username,
                'role' => $decoded->role,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60) // Expira en 7 días
            ];

            if ($id_rol === 4) {
                $token['rfc'] = $decoded->rfc;
                $token['icon'] = $decoded->icon;
                $token['colors'] = $decoded->colors;
            } else {
                $token['img_user'] = $decoded->img_user;
                $token['no_empleado'] = $decoded->no_empleado;
                $token['id_empresa'] = $decoded->id_empresa;
            }

            $jwt = JWT::encode($token, $this->key, 'HS256');

            $data = array(
                'token' => $jwt,
                'user' => $decoded,
                'authentication' => 'true',
                'estatus' => 'success',
                'codigo' => 200
            );

        }catch (\UnexpectedValueException $e) {
            $data = array(
                'authentication' => $auth,
                'estatus' => 'error',
                'codigo' => 401
            );
        }catch (\DomainException $e) {
            $data = array(
                'authentication' => $auth,
                'estatus' => 'error',
                'codigo' => 401
            );
        }
        return $data;
    }

    public function checkToken($jwt){
        $auth = false;
        try{
            JWT::$leeway = 600000; // $leeway in seconds
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            if (isset($decoded) && is_object($decoded) && isset($decoded->sub)) {
                $auth = true;
                $data = array(
                    'codigo' => 200,
                    'auth' => $auth,
                    'estatus' => 'success',
                    'data' => $decoded
                );
            }else{
                $auth = false;
                $data = array(
                    'codigo' => 401,
                    'auth' => $auth,
                    'estatus' => 'error',
                    'data' => $decoded
                );
            }

            return $data;

        }catch(\UnexpectedValueException $e){
            $auth = false;
                $data = array(
                    'codigo' => 401,
                    'auth' => $auth,
                    'estatus' => 'error',
                    'mensaje' => $e->getMessage()
                );
        }catch(\DomainException $e){
            $auth = false;
            $data = array(
                'codigo' => 401,
                'auth' => $auth,
                'estatus' => 'error',
                'mensaje' => $e->getMessage()
            );
        }
    }
}
?>
