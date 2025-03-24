<?php

namespace App\Http\Controllers\empresa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Models\Empresa;
use App\Helpers\JwtAuth;

class EmpresaController extends Controller
{
    public function list_empresas(Request $request){
        try {
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger por Post
            $token = $request->bearerToken();

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $empresas = Empresa::orderBy('id', 'desc')->get();

            if (count($empresas) > 0) {
                $data = array(
                    'empresa' => $empresas,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['empresa'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay Empresas registradas',
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

    public function register_company(Request $request) {
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();
            $type = 'company';

            //Recoger post
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $rfc = !is_null($request->rfc) && isset($request->rfc) ? strip_tags($request->rfc) : null;
            $telefono = !is_null($request->telefono) && isset($request->telefono) ? strip_tags($request->telefono) : null;
            $colors = !is_null($request->colors) && isset($request->colors) ? strip_tags($request->colors) : null;
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;
            $id_rol = 4;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required',
                'rfc' => 'required|min:13',
                'telefono' => 'required|min:8',
                'correo' => 'required|email|unique:empresas,correo',
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

            if ($request->has('icon_file') || $request->has('icon')) {
                $icon = $request->icon;

                if ($request->hasFile('icon_file')) {
                    // Caso 1: Imagen subida como archivo
                    $icon_name = $request->file('icon_file');
                    $extension = $icon_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $empresa->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('empresa')->put($icon_path, File::get($icon_name));
                    $icon = $icon_path;

                } elseif (strpos($icon, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $icon_data = explode(',', $icon);
                    $icon_extension = explode(';', explode('/', $icon_data[0])[1])[0] != '' ?  explode(';', explode('/', $icon_data[0])[1])[0] : 'jpg';
                    $icon_path = time(). '_' . strtolower($nombre) . '.' . $icon_extension;

                    $icon_data_decoded = base64_decode($icon_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$icon_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('empresa')->put($icon_path, $icon_data_decoded);
                    $icon = $icon_path;
                } else {
                    // Si la imagen no está codificada en base64
                    $icon_extension = pathinfo($icon, PATHINFO_EXTENSION) != '' ? pathinfo($icon, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $empresa->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$icon_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $icon);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $icon_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('empresa')->put($icon_path, $icon_data_decoded);
                    $icon = $icon_path;
                }
            } else {
                $icon = '';
            }

            $empresa = new Empresa();
            $empresa->nombre = $nombre;
            $empresa->rfc = $rfc;
            $empresa->telefono = $telefono;
            $empresa->correo = $correo;
            $empresa->id_role = $id_rol;
            $empresa->icon = $icon;
            $empresa->colors = $colors;
            $empresa->password = Hash::make($password);

            if ($empresa->save()) {
                DB::commit();
                $token = $jwtAuth->signup($correo, $password, $id_rol);
                $data = array(
                    'mensaje' => 'Empresa Registrada Correctamente!!',
                    'token' => $token['token'],
                    'empresa' => $empresa,
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

    public function update_empresa($id_empresa, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $id_empresa_user = null;
            $jwtAuth = new JwtAuth();
            // $messages = [
            //     'costo.min' => 'El costo debe ser mayor a 0.',
            //     'img_comida.image' => 'El archivo debe ser una imagen.',
            //     'img_comida.mimes' => 'La imagen debe ser en formato: jpeg, png, jpg, gif o webp.',
            //     'img_comida.max' => 'La imagen no debe superar los 2MB.',
            // ];

            //Recoger post
            $token = $request->bearerToken();
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $rfc = !is_null($request->rfc) && isset($request->rfc) ? strip_tags($request->rfc) :null;
            $telefono = !is_null($request->telefono) && isset($request->telefono) ? strip_tags($request->telefono) : 0;
            $correo = !is_null($request->correo) && isset($request->correo) ? strip_tags($request->correo) : null;
            $colores = !is_null($request->colores) && isset($request->colores) ? strip_tags($request->colores) : null;
            $password = !is_null($request->password) && isset($request->password) ? strip_tags($request->password) : null;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus : 1;

            // return response()->json($request->all(), 200);

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $rol = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            if ($rol == "Company_Role") {
                $id_empresa_user = $checktoken["data"]->sub;
            } else {
                $id_empresa_user = $checktoken["data"]->id_empresa;
            }

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required',
                'rfc' => 'required|min:13',
                'telefono' => 'required|min:8',
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

            $empresa = Empresa::where('id', $id_empresa)->first();

            if ($request->has('icon_file') || $request->has('icon')) {
                $icon = $request->icon;

                if ($request->hasFile('icon_file')) {
                    // Caso 1: Imagen subida como archivo
                    $icon_name = $request->file('icon_file');
                    $extension = $icon_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $empresa->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('empresa')->put($icon_path, File::get($icon_name));
                    $icon = $icon_path;

                } elseif (strpos($icon, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $icon_data = explode(',', $icon);
                    $icon_extension = explode(';', explode('/', $icon_data[0])[1])[0] != '' ?  explode(';', explode('/', $icon_data[0])[1])[0] : 'jpg';
                    $icon_path = time(). '_' . strtolower($nombre) . '.' . $icon_extension;

                    $icon_data_decoded = base64_decode($icon_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$icon_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('empresa')->put($icon_path, $icon_data_decoded);
                    $icon = $icon_path;
                } else {
                    // Si la imagen no está codificada en base64
                    $icon_extension = pathinfo($icon, PATHINFO_EXTENSION) != '' ? pathinfo($icon, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_empresa = $empresa->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $nombre_simplificado = strtolower($nombre);
                    $nombre_simplificado = preg_replace('/[^a-z0-9 ]/', '', $nombre_simplificado); // Quitar caracteres especiales
                    $nombre_simplificado = implode('_', array_slice(explode(' ', $nombre_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $icon_path = "{$id_empresa}_{$nombre_simplificado}_" . time() . ".{$icon_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $icon);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $icon_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('empresa')->put($icon_path, $icon_data_decoded);
                    $icon = $icon_path;
                }
                Storage::disk('empresa')->delete($empresa->icon);
            } else {
                $icon = '';
            }

            $empresa->nombre = $empresa->nombre != $nombre ? $nombre : $empresa->nombre;
            $empresa->rfc = $empresa->rfc != $rfc ? $rfc : $empresa->rfc;
            $empresa->telefono = $empresa->telefono != $telefono ? $telefono : $empresa->telefono;
            $empresa->correo = $empresa->correo != $correo ? $correo : $empresa->correo;
            $empresa->icon = $icon != $empresa->icon ? $icon_path : $empresa->icon;
            $empresa->colors = $colores != $empresa->colors ? $colores : $empresa->colors;
            $empresa->password = $empresa->password != $password ? $password : $empresa->password;
            // $empresa->estatus = $empresa->estatus != $estatus ? $estatus : $empresa->estatus;

            if($empresa->update()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Empresa Actualizada Correctamente!!',
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

    public function getEmpresaById($id_empresa, Request $request){
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

            $empresa = Empresa::where('id', $id_empresa)->first();

            if (is_object($empresa)) {
                $data = array(
                    'empresa' => $empresa,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['empresa'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay empresa registrada',
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

    public function getEmpresasearch($nombre_empresa = '', Request $request){
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

            $empresas = Empresa::where('nombre', 'like', '%'.$nombre_empresa.'%')->orderby('id', 'desc')->get();

            if (count($empresas) > 0) {
                $data = array(
                    'empresa' => $empresas,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['empresa'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay empresas registrados',
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
}
