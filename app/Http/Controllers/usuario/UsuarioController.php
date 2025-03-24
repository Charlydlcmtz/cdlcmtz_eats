<?php

namespace App\Http\Controllers\usuario;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\Role;
use App\Models\CodigoVerificador;
use App\Helpers\JwtAuth;
use App\Mail\RecuperacionMail;

class UsuarioController extends Controller
{
    public function register(Request $request) {
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $username = !is_null($request->username) && isset($request->username) ? strip_tags($request->username) :null;
            $apellido_p = !is_null($request->apellido_p) && isset($request->apellido_p) ? strip_tags($request->apellido_p) : null;
            $apellido_m = !is_null($request->apellido_m) && isset($request->apellido_m) ? strip_tags($request->apellido_m) : null;
            $telefono = !is_null($request->telefono) && isset($request->telefono) ? strip_tags($request->telefono) : null;
            // $img_user = !is_null($request->apellido_p) && isset($request->apellido_p) ? strip_tags($request->apellido_p) : null;
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required',
                'username' => 'required',
                'apellido_p' => 'required',
                'apellido_m' => 'required',
                'telefono' => 'required|min:8|max:10',
                'correo' => 'required|email|unique:users,correo',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]+$/'
                ]
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $usuario = new User();
            $usuario->nombre = strtolower($nombre);
            $usuario->username = strtolower($username);
            $usuario->apellido_p = strtolower($apellido_p);
            $usuario->apellido_m = strtolower($apellido_m);
            $usuario->telefono = strtolower($telefono);
            $usuario->correo = strtolower($correo);
            $usuario->password = Hash::make($password);
            $usuario->id_role = 2;

            if ($usuario->save()) {
                // Cargar el nombre del rol
                $usuario->load('role:id,nombre');
                DB::commit();
                $token = $jwtAuth->signup($correo, $password);
                $data = array(
                    'mensaje' => 'Usuario Registrado Correctamente!!',
                    'token' => $token['token'],
                    'user' => $usuario,
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function login(Request $request){
        try {
            $data = null;
            $jwtAuth = new JWTAuth();

            //Recoger post
            $correo = !is_null($request->correo) && isset($request->correo) ? strtolower(strip_tags($request->correo)) : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;


            if(is_null($correo) || is_null($password)){
                throw new \Exception('Las credenciales son necesarias', 400);
            }

            $token = $jwtAuth->signup($correo, $password);

            if(!$token){
                throw new \Exception('Las credenciales proporcionadas son incorrectas', 400);
            }else {
                $data = array(
                    'token' => $token['token'],
                    'user' => $token['data'],
                    'estatus' => $token['estatus'],
                    'codigo' => $token['codigo'],
                );
            }

        } catch (\Throwable $th) {
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo'=> 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function check_renew_token(Request $request){
        try {
            $data = null;
            $jwtAuth = new JWTAuth();

            //Recoger post
            $token = $request->bearerToken();

            if(!$token){
                throw new \Exception('Inicia sesion nuevamente.', 401);
            }

            $renewToken = $jwtAuth->renewToken($token);
            $data = $renewToken;

            if(!$renewToken) {
                throw new \Exception('tu token expiro inicia sesion nuevamente.', 401);
            }

        } catch (\Throwable $th) {
            $data = array(
                'mensaje'=> $th->getmessage(),
                'estatus'=> 'error',
                'codigo'=> 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function list_usuarios(Request $request){
        try {
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger por Post
            $token = $request->bearerToken();
            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $rol = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            switch ($rol) {
                case 'Admin_Role':
                    $usuarios = User::with('role:id,nombre')->orderBy('id', 'desc')->get();
                break;

                case 'User_role':
                    $data = array();
                break;

                case 'Chef_Role':
                    $data = array();
                break;

                case 'Company_Role':
                    $data_company = $checktoken['data'];
                    $usuarios = User::with('role:id,nombre')
                        ->where('id_empresa', $data_company->sub)
                        ->orderBy('id', 'desc')
                        ->get();
                break;

                case 'Delivery_Role':
                    $data = array();
                break;
            }

            if (count($usuarios) > 0) {
                $data = array(
                    'user' => $usuarios,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['user'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay comida registrada',
                    'estatus' => 'error',
                    'codigo' => 400
                );
            }

        } catch (\Throwable $th) {
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }

        return response()->json($data, $data['codigo']);
    }

    public function list_roles(Request $request){
        try {
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger por Post
            $token = $request->bearerToken();
            $checktoken = $jwtAuth->checkToken($token);


            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            switch ($checktoken['data']->role->nombre) {
                case 'Admin_Role':
                    $roles = Role::where('estatus', 1)
                                ->orderBy('id', 'desc')
                                ->get();
                break;

                case 'Company_Role':
                    $roles = Role::where('nombre', '!=', 'Admin_Role')
                                ->where('estatus', 1)
                                ->orderBy('id', 'desc')
                                ->get();
                break;

                default:
                    $roles = array();
                break;
            }

            if (count($roles) > 0) {
                $data = array(
                    'roles' => $roles,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['roles'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay comida registrada',
                    'estatus' => 'error',
                    'codigo' => 400
                );
            }

        } catch (\Throwable $th) {
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }

        return response()->json($data, $data['codigo']);
    }

    public function usuarios_add(Request $request) {
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $username = !is_null($request->username) && isset($request->username) ? strip_tags($request->username) :null;
            $apellido_p = !is_null($request->apellido_p) && isset($request->apellido_p) ? strip_tags($request->apellido_p) : null;
            $apellido_m = !is_null($request->apellido_m) && isset($request->apellido_m) ? strip_tags($request->apellido_m) : null;
            $id_rol = !is_null($request->id_empresa) && isset($request->id_empresa) ? $request->id_empresa : null;
            $telefono = !is_null($request->telefono) && isset($request->telefono) ? strip_tags($request->telefono) : null;
            $no_empleado = !is_null($request->no_empleado) && isset($request->no_empleado) ? strip_tags($request->no_empleado) : null;
            $id_empresa = !is_null($request->id_empresa) && isset($request->id_empresa) ? $request->id_empresa : null;
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus : 1;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required',
                'username' => 'required',
                'apellido_p' => 'required',
                'apellido_m' => 'required',
                'rol' => 'required',
                'telefono' => 'required|min:8|max:10',
                'correo' => 'required|email',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]+$/'
                ]
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            if($no_empleado == null){
                $estatus = 0;
            }

            if ($request->has('img_usuario_file') || $request->has('img_usuario')) {
                $img_usuario = $request->img_usuario;

                if ($request->hasFile('img_usuario_file')) {
                    // Caso 1: Imagen subida como archivo
                    $img_usuario_name = $request->file('img_usuario_file');
                    $extension = $img_usuario_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('img_users')->put($img_usuario_path, File::get($img_usuario_name));
                    $img_usuario = $img_usuario_path;
                } elseif (strpos($img_usuario, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $img_usuario_data = explode(',', $img_usuario);
                    $img_usuario_extension = explode(';', explode('/', $img_usuario_data[0])[1])[0] != '' ?  explode(';', explode('/', $img_usuario_data[0])[1])[0] : 'jpg';
                    $img_usuario_path = time(). '_' . strtolower($nombre) . '.' . $img_usuario_extension;

                    $img_usuario_data_decoded = base64_decode($img_usuario_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$img_usuario_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('img_users')->put($img_usuario_path, $img_usuario_data_decoded);
                    $img_usuario = $img_usuario_path;
                } else {
                    // Si la imagen no está codificada en base64
                    $img_usuario_extension = pathinfo($img_usuario, PATHINFO_EXTENSION) != '' ? pathinfo($img_usuario, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$img_usuario_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $img_usuario);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $img_usuario_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('img_users')->put($img_usuario_path, $img_usuario_data_decoded);
                    $img_usuario = $img_usuario_path;
                }
            } else {
                $img_usuario = '';
            }

            $usuario = new User();
            $usuario->nombre = strtolower($nombre);
            $usuario->username = strtolower($username);
            $usuario->apellido_p = strtolower($apellido_p);
            $usuario->apellido_m = strtolower($apellido_m);
            $usuario->id_role = $id_rol;
            $usuario->telefono = strtolower($telefono);
            $usuario->img_user = $img_usuario;
            $usuario->no_empleado = $no_empleado;
            $usuario->id_empresa = $id_empresa;
            $usuario->correo = strtolower($correo);
            $estatus->estatus = $estatus;
            $usuario->password = Hash::make($password);

            if ($usuario->save()) {
                $usuario->load('role:id,nombre');
                DB::commit();
                $data = array(
                    'mensaje' => 'Usuario Registrado Correctamente!!',
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function update_usuario($id_usuario, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $id_empresa_user = null;
            $jwtAuth = new JwtAuth();


            //Recoger post
            $token = $request->bearerToken();
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $username = !is_null($request->username) && isset($request->username) ? strip_tags($request->username) :null;
            $apellido_p = !is_null($request->apellido_p) && isset($request->apellido_p) ? strip_tags($request->apellido_p) : null;
            $apellido_m = !is_null($request->apellido_m) && isset($request->apellido_m) ? strip_tags($request->apellido_m) : null;
            $telefono = !is_null($request->telefono) && isset($request->telefono) ? strip_tags($request->telefono) : null;
            $no_empleado = !is_null($request->no_empleado) && isset($request->no_empleado) ? strip_tags($request->no_empleado) : null;
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $id_empresa = !is_null($request->id_empresa) && isset($request->id_empresa) ? $request->id_empresa : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus : 0;

            // return response()->json($request->all(), 200);

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required',
                'username' => 'required',
                'apellido_p' => 'required',
                'apellido_m' => 'required',
                'telefono' => 'required|min:8|max:10',
                'correo' => 'required|email',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]+$/'
                ]
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $usuario = User::where('id', $id_usuario)->first();

            if ($request->has('img_usuario_file') || $request->has('img_usuario')) {
                $img_usuario = $request->img_usuario;

                if ($request->hasFile('img_usuario_file')) {
                    // Caso 1: Imagen subida como archivo
                    $img_usuario_name = $request->file('img_usuario_file');
                    $extension = $img_usuario_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('img_users')->put($img_usuario_path, File::get($img_usuario_name));
                    $img_usuario = $img_usuario_path;
                } elseif (strpos($img_usuario, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $img_usuario_data = explode(',', $img_usuario);
                    $img_usuario_extension = explode(';', explode('/', $img_usuario_data[0])[1])[0] != '' ?  explode(';', explode('/', $img_usuario_data[0])[1])[0] : 'jpg';
                    $img_usuario_path = time(). '_' . strtolower($nombre) . '.' . $img_usuario_extension;

                    $img_usuario_data_decoded = base64_decode($img_usuario_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$img_usuario_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('img_users')->put($img_usuario_path, $img_usuario_data_decoded);
                    $img_usuario = $img_usuario_path;
                } else {
                    // Si la imagen no está codificada en base64
                    $img_usuario_extension = pathinfo($img_usuario, PATHINFO_EXTENSION) != '' ? pathinfo($img_usuario, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_usuario = $usuario->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_usuario_path = "{$id_usuario}_{$nombre_simplificado}_" . time() . ".{$img_usuario_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $img_usuario);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $img_usuario_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('img_users')->put($img_usuario_path, $img_usuario_data_decoded);
                    $img_usuario = $img_usuario_path;
                }
                Storage::disk('img_users')->delete($usuario->img_user);
            } else {
                $img_usuario = '';
            }

            $usuario->nombre = $usuario->nombre != $nombre ? $nombre : $usuario->nombre;
            $usuario->apellido_p = $usuario->apellido_p != $apellido_p ? $apellido_p : $usuario->apellido_p;
            $usuario->apellido_m = $usuario->apellido_m != $apellido_m ? $apellido_m : $usuario->apellido_m;
            $usuario->username = $usuario->username != $username ? $username : $usuario->username;
            $usuario->img_user = $img_usuario != $usuario->img_user ? $img_usuario_path : $usuario->img_user;
            $usuario->telefono = $telefono != $usuario->telefono ? $telefono : $usuario->telefono;
            $usuario->no_empleado = $usuario->no_empleado != $no_empleado ? $no_empleado : $usuario->no_empleado;
            $usuario->correo = $usuario->correo != $correo ? $correo : $usuario->correo;
            $usuario->id_empresa = $usuario->id_empresa != $id_empresa ? $id_empresa : $usuario->id_empresa;
            $usuario->estatus = $usuario->estatus != $estatus ? $estatus : $usuario->estatus;
            $usuario->password = $password != $usuario->password ? $password : $usuario->password;

            if($usuario->update()){
                $usuario->load('role:id,nombre');
                DB::commit();
                $data = array(
                    'mensaje' => 'Usuario Actualizada Correctamente!!',
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }

        } catch (\Throwable $th) {
            DB::rollback();
            $data = array(
                "mensaje" => $th->getmessage(),
                "estatus" => "error",
                "codigo" => 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function getUsuarioById($id_usuario, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();

            $checktoken = $jwtAuth->checkToken($token);


            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $usuario = User::with('role:id,nombre')->where('id', $id_usuario)->first();

            if (is_object($usuario)) {
                $data = array(
                    'usuario' => $usuario,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['usuario'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay usuario registrado',
                    'estatus' => 'error',
                    'codigo' => 400
                );
            }


        } catch (\Throwable $th) {
            DB::rollback();
            $data = array(
                "mensaje" => $th->getmessage(),
                "estatus" => "error",
                "codigo" => 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function getUsuariosearch($nombre_usuario = '', Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $usuarios = User::with('role:id,nombre')->where('nombre', 'like', '%'.$nombre_usuario.'%')->orderby('id', 'desc')->get();

            if (count($usuarios) > 0) {
                $data = array(
                    'usuarios' => $usuarios,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['usuarios'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay menus registrados',
                    'estatus' => 'error',
                    'codigo' => 400
                );
            }


        } catch (\Throwable $th) {
            DB::rollback();
            $data = array(
                "mensaje" => $th->getmessage(),
                "estatus" => "error",
                "codigo" => 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function getCodigoValidador(Request $request){
        try {
            DB::beginTransaction();
            $data = null;

            //Recoger post
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $tipo_app = !is_null($request->tipo_app) && isset($request->tipo_app) ? strip_tags($request->tipo_app) : 'web';

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'correo' => 'required|email'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $usuario = User::with('role:id,nombre')->where('correo', $request->correo)->first();

            $codigo_creado = mt_Rand(1000, 9999);

            $codigo_verificador = new CodigoVerificador();
            $codigo_verificador->codigo = $codigo_creado;
            $codigo_verificador->correo = $correo;

            $detalles = [
                'codigo' => $codigo_creado,
                'usuario' => $usuario,
            ];

            if(Mail::to($correo)->send(new RecuperacionMail($detalles, $tipo_app))){
                if($codigo_verificador->save()){
                    DB::commit();
                    return $data = array(
                        'mensaje' => 'Se ha enviado un codigo a su correo.',
                        'estatus' => 'success',
                        'codigo' => 200
                    );
                }
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            $data = array(
                'mensaje' => $th->getmessage(),
                'estatus' => 'error',
                'codigo' => 400
            );
        }
        return response()->json($data, $data['codigo']);
    }

    public function change_password(Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;
            $codigo_validador = !is_null($request->codigo_validador) && isset($request->codigo_validador) ? $request->codigo_validador : 0;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'password' => [
                    'required',
                    'min:8',
                    'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]+$/'
                ],
                'codigo_validador' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $codigo_verificador = CodigoVerificador::where('codigo', $codigo_validador)->first();
            $usuario = User::where('correo', $codigo_verificador->correo)->first();

            $usuario->password = $password != $usuario->password ? $password : $usuario->password;

            if($usuario->update()){
                $codigo_verificador->delete();
                // Cargar el nombre del rol
                $usuario->load('role:id,nombre');
                DB::commit();
                $token = $jwtAuth->signup($usuario->correo, $password);
                $data = array(
                    'mensaje' => 'Password Actualizado Correctamente!!',
                    'token' => $token['token'],
                    'user' => $usuario,
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }

        } catch (\Throwable $th) {
            DB::rollback();
            $data = array(
                "mensaje" => $th->getmessage(),
                "estatus" => "error",
                "codigo" => 400,
            );
        }
        return response()->json($data, $data['codigo']);
    }
}
