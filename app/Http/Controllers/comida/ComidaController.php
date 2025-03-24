<?php

namespace App\Http\Controllers\comida;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Helpers\JwtAuth;

use App\Models\Menu;
use App\Models\TipoMenu;
use App\Models\Pedido;
use App\Models\Tarjeta;

class ComidaController extends Controller
{
    // funciones para la comida llamada menu.
    public function list_food(Request $request){
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
                    $data_user = $checktoken['data'];
                    $comidas = Menu::with(['empresa', 'tipo_menu'])
                        ->orderBy('id', 'desc')
                        ->get();
                break;

                case 'User_Role':
                    $comidas = array();
                break;

                case 'Chef_Role':
                    $data_user = $checktoken['data'];
                    $comidas = Menu::with(['empresa', 'tipo_menu'])
                        ->where('id_empresa', $data_user->id_empresa)
                        ->orderBy('id', 'desc')
                        ->get();
                break;
                case 'Company_Role':
                    $data_company = $checktoken['data'];
                    $comidas = Menu::with(['empresa', 'tipo_menu'])
                    ->where('id_empresa', $data_company->sub)
                    ->orderBy('id', 'desc')
                    ->get();
                break;
                case 'Delivery_Role':
                    $comidas = array();
                break;
            }

            if (count($comidas) > 0) {
                $data = array(
                    'menu' => $comidas,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['menu'], $data['codigo']);
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

    public function add_food(Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();
            // $messages = [
            //     'costo.min' => 'El costo debe ser mayor a 0.',
            //     'img_comida.image' => 'El archivo debe ser una imagen.',
            //     'img_comida.mimes' => 'La imagen debe ser en formato: jpeg, png, jpg, gif o webp.',
            //     'img_comida.max' => 'La imagen no debe superar los 2MB.',
            // ];

            //Recoger post
            $token = $request->bearerToken();
            $platillo = !is_null($request->platillo) && isset($request->platillo) ? strip_tags($request->platillo) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;
            $costo = !is_null($request->costo) && isset($request->costo) ? strip_tags($request->costo) : 0;
            $calorias = !is_null($request->calorias) && isset($request->calorias) ? strip_tags($request->calorias) : null;
            $img_comida = !is_null($request->img_comida) && isset($request->img_comida) ? strip_tags($request->img_comida) : null;
            $fecha_inicio = !is_null($request->fecha_inicio) && isset($request->fecha_inicio) ? strip_tags($request->fecha_inicio) : null;
            $fecha_fin = !is_null($request->fecha_fin) && isset($request->fecha_fin) ? strip_tags($request->fecha_fin) : null;
            $id_tipo_menu = !is_null($request->id_tipo_menu) && isset($request->id_tipo_menu) ? strip_tags($request->id_tipo_menu) : 1;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'platillo' => 'required|string',
                'costo' => 'required|numeric|min:0.01', // ✅ No permite 0 ni valores negativos
                'id_tipo_menu' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            if ($request->has('img_comida_file') || $request->has('img_comida')) {
                $img_comida = $request->img_comida;

                if ($request->hasFile('img_comida_file')) {
                    // Caso 1: Imagen subida como archivo
                    $img_comida_name = $request->file('img_comida_file');
                    $extension = $img_comida_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('comida')->put($img_comida_path, File::get($img_comida_name));
                    $img_comida = $img_comida_path;
                } elseif (strpos($img_comida, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $img_comida_data = explode(',', $img_comida);
                    $img_comida_extension = explode(';', explode('/', $img_comida_data[0])[1])[0] != '' ?  explode(';', explode('/', $img_comida_data[0])[1])[0] : 'jpg';
                    $img_comida_path = time(). '_' . strtolower($platillo) . '.' . $img_comida_extension;

                    $img_comida_data_decoded = base64_decode($img_comida_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$img_comida_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                } else {
                     // Si la imagen no está codificada en base64
                    $img_comida_extension = pathinfo($img_comida, PATHINFO_EXTENSION) != '' ? pathinfo($img_comida, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$img_comida_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $img_comida);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $img_comida_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                }
            } else {
                $img_comida = '';
            }

            $menu = new Menu();
            $menu->platillo = $platillo;
            $menu->descripcion = $descripcion;
            $menu->costo = $costo;
            $menu->calorias = $calorias;
            $menu->img_comida= $img_comida;
            $menu->inicio_fecha_platillo = $fecha_inicio;
            $menu->fin_fecha_platillo = $fecha_fin;
            $menu->id_tipo_menu = $id_tipo_menu;

            if($menu->save()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Platillo Guardado Correctamente!!',
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

    public function getFoodById($id_food, Request $request){
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

            $comida = Menu::where('id', $id_food)->first();

            if (is_object($comida)) {
                $data = array(
                    'menu' => $comida,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['menu'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay comida registrada',
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

    public function update_food($id_comida, Request $request){
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
            $platillo = !is_null($request->platillo) && isset($request->platillo) ? strip_tags($request->platillo) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;
            $costo = !is_null($request->costo) && isset($request->costo) ? strip_tags($request->costo) : 0;
            $calorias = !is_null($request->calorias) && isset($request->calorias) ? strip_tags($request->calorias) : null;
            $inicio_fecha_platillo = !is_null($request->inicio_fecha_platillo) && isset($request->inicio_fecha_platillo) ? strip_tags($request->inicio_fecha_platillo) : null;
            $fin_fecha_platillo = !is_null($request->fin_fecha_platillo) && isset($request->fin_fecha_platillo) ? strip_tags($request->fin_fecha_platillo) : null;
            $id_tipo_menu = !is_null($request->id_tipo_menu) && isset($request->id_tipo_menu) ? strip_tags($request->id_tipo_menu) : 1;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus : 1;

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
                'platillo' => 'required|string',
                'costo' => 'required|numeric|min:0.01', // ✅ No permite 0 ni valores negativos

            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $menu = Menu::where('id', $id_comida)->first();

            if ($request->has('img_comida_file') || $request->has('img_comida')) {
                $img_comida = $request->img_comida;

                if ($request->hasFile('img_comida_file')) {
                    // Caso 1: Imagen subida como archivo
                    $img_comida_name = $request->file('img_comida_file');
                    $extension = $img_comida_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('comida')->put($img_comida_path, File::get($img_comida_name));
                    $img_comida = $img_comida_path;
                } elseif (strpos($img_comida, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $img_comida_data = explode(',', $img_comida);
                    $img_comida_extension = explode(';', explode('/', $img_comida_data[0])[1])[0] != '' ?  explode(';', explode('/', $img_comida_data[0])[1])[0] : 'jpg';
                    $img_comida_path = time(). '_' . strtolower($platillo) . '.' . $img_comida_extension;

                    $img_comida_data_decoded = base64_decode($img_comida_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$img_comida_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                } else {
                     // Si la imagen no está codificada en base64
                    $img_comida_extension = pathinfo($img_comida, PATHINFO_EXTENSION) != '' ? pathinfo($img_comida, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_comida = $menu->id ?? uniqid();

                    // Obtener las primeras 2 palabras del platillo sin caracteres especiales
                    $platillo_simplificado = strtolower($platillo);
                    $platillo_simplificado = preg_replace('/[^a-z0-9 ]/', '', $platillo_simplificado); // Quitar caracteres especiales
                    $platillo_simplificado = implode('_', array_slice(explode(' ', $platillo_simplificado), 0, 2)); // Tomar 2 primeras palabras

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_{$platillo_simplificado}_" . time() . ".{$img_comida_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $img_comida);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $img_comida_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                }
                Storage::disk('comida')->delete($menu->img_comida);
            } else {
                $img_comida = '';
            }

            $menu->platillo = $menu->platillo != $platillo ? $platillo : $menu->platillo;
            $menu->descripcion = $menu->descripcion != $descripcion ? $descripcion : $menu->descripcion;
            $menu->costo = $menu->costo != $costo ? $costo : $menu->costo;
            $menu->calorias = $menu->calorias != $calorias ? $calorias : $menu->calorias;
            $menu->img_comida = $img_comida != $menu->img_comida ? $img_comida_path : $menu->img_comida;
            $menu->inicio_fecha_platillo = $menu->inicio_fecha_platillo != $inicio_fecha_platillo ? $inicio_fecha_platillo : $menu->inicio_fecha_platillo;
            $menu->fin_fecha_platillo = $menu->fin_fecha_platillo != $fin_fecha_platillo ? $fin_fecha_platillo : $menu->fin_fecha_platillo;
            $menu->id_empresa = $menu->id_empresa != $id_empresa_user ? $id_empresa_user : $menu->id_empresa;
            $menu->id_tipo_menu = $menu->id_tipo_menu != $id_tipo_menu ? $id_tipo_menu : $menu->id_tipo_menu;
            $menu->estatus = $menu->estatus != $estatus ? $estatus : $menu->estatus;

            if($menu->update()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Platillo Actualizado Correctamente!!',
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

    public function update_food_movil($id_comida, Request $request){
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
            $platillo = !is_null($request->platillo) && isset($request->platillo) ? strip_tags($request->platillo) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;
            $costo = !is_null($request->costo) && isset($request->costo) ? strip_tags($request->costo) : 0;
            $calorias = !is_null($request->calorias) && isset($request->calorias) ? strip_tags($request->calorias) : null;
            $inicio_fecha_platillo = !is_null($request->inicio_fecha_platillo) && isset($request->inicio_fecha_platillo) ? strip_tags($request->inicio_fecha_platillo) : null;
            $fin_fecha_platillo = !is_null($request->fin_fecha_platillo) && isset($request->fin_fecha_platillo) ? strip_tags($request->fin_fecha_platillo) : null;
            $id_tipo_menu = !is_null($request->id_tipo_menu) && isset($request->id_tipo_menu) ? strip_tags($request->id_tipo_menu) : 1;
            $img_comida = !is_null($request->img_comida) && isset($request->img_comida) ? strip_tags($request->img_comida) : null;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus : 1;

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
                'platillo' => 'required|string',
                'costo' => 'required|numeric|min:0.01', // ✅ No permite 0 ni valores negativos

            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $menu = Menu::where('id', $id_comida)->first();

            $menu->platillo = $menu->platillo != $platillo ? $platillo : $menu->platillo;
            $menu->descripcion = $menu->descripcion != $descripcion ? $descripcion : $menu->descripcion;
            $menu->costo = $menu->costo != $costo ? $costo : $menu->costo;
            $menu->calorias = $menu->calorias != $calorias ? $calorias : $menu->calorias;
            $menu->img_comida = $img_comida != $menu->img_comida ? $img_comida : $menu->img_comida;
            $menu->inicio_fecha_platillo = $menu->inicio_fecha_platillo != $inicio_fecha_platillo ? $inicio_fecha_platillo : $menu->inicio_fecha_platillo;
            $menu->fin_fecha_platillo = $menu->fin_fecha_platillo != $fin_fecha_platillo ? $fin_fecha_platillo : $menu->fin_fecha_platillo;
            $menu->id_empresa = $menu->id_empresa != $id_empresa_user ? $id_empresa_user : $menu->id_empresa;
            $menu->id_tipo_menu = $menu->id_tipo_menu != $id_tipo_menu ? $id_tipo_menu : $menu->id_tipo_menu;
            $menu->estatus = $menu->estatus != $estatus ? $estatus : $menu->estatus;

            if($menu->update()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Platillo Actualizado Correctamente!!',
                    'estatus' => 'success',
                    'codigo' => 200,
                    'id' => $menu->id // 👈 Agregamos el id actualizado
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

    public function update_food_file(Request $request){
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

            if ($request->has('img_comida_file') || $request->has('img_comida')) {
                $img_comida = $request->img_comida;

                if ($request->hasFile('img_comida_file')) {
                    // Caso 1: Imagen subida como archivo
                    $img_comida_name = $request->file('img_comida_file');
                    $extension = $img_comida_name->getClientOriginalExtension();

                    // Obtener el ID o generar un hash único corto
                    $id_comida = uniqid();

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_" . time() . ".{$extension}";

                    // Guardar la imagen con el nuevo nombre
                    Storage::disk('comida')->put($img_comida_path, File::get($img_comida_name));
                    $img_comida = $img_comida_path;
                } elseif (strpos($img_comida, ';base64') !== false) {
                    // Si la imagen está codificada en base64
                    $img_comida_data = explode(',', $img_comida);
                    $img_comida_extension = explode(';', explode('/', $img_comida_data[0])[1])[0] != '' ?  explode(';', explode('/', $img_comida_data[0])[1])[0] : 'jpg';
                    $img_comida_path = time(). '_' . '.' . $img_comida_extension;

                    $img_comida_data_decoded = base64_decode($img_comida_data[1]);

                    // Obtener el ID o generar un hash único corto
                    $id_comida = uniqid();

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_" . time() . ".{$img_comida_extension}";

                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                } else {
                     // Si la imagen no está codificada en base64
                    $img_comida_extension = pathinfo($img_comida, PATHINFO_EXTENSION) != '' ? pathinfo($img_comida, PATHINFO_EXTENSION) : 'jpg';
                    // Obtener el ID o generar un hash único corto
                    $id_comida = uniqid();

                    // Generar un nombre seguro y corto
                    $img_comida_path = "{$id_comida}_" . time() . ".{$img_comida_extension}";
                    // $img_heroe_data_decoded = file_get_contents($img_heroe);
                    // Inicializar cURL
                    $ch = curl_init();

                    // Establecer la URL de la imagen
                    curl_setopt($ch, CURLOPT_URL, $img_comida);

                    // Establecer la opción para devolver el resultado como una cadena en lugar de imprimirlo directamente
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    // Realizar la solicitud
                    $img_comida_data_decoded = curl_exec($ch);

                    // Cerrar la sesión cURL
                    curl_close($ch);
                    // Guardar la imagen en el sistema de archivos
                    Storage::disk('comida')->put($img_comida_path, $img_comida_data_decoded);
                    $img_comida = $img_comida_path;
                }

                // Storage::disk('comida')->delete($menu->img_comida);
            } else {
                $img_comida = '';
            }

            if($img_comida != ''){
                DB::commit();
                $data = array(
                    'img' => $img_comida,
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }

        } catch (\Throwable $th) {
            DB::rollback();
            \Log::error('❌ Error al subir imagen:', [
                'mensaje' => $th->getMessage(),
                'linea' => $th->getLine(),
                'archivo' => $th->getFile()
            ]);
            $data = array(
                "mensaje" => $th->getMessage(),
                "estatus" => "error",
                "codigo" => 400,
            );
        }
        return response()->json(['image' => $data['img']], $data['codigo']);
    }

    public function delete_food($id_comida, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            $token =  $request->bearerToken();

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

           $comida = Menu::where("id", "=", $id_comida)->first();
           if ($comida->estatus == 0) {
                throw new \Exception ("La comida fue dada de baja.", 401);
           }

            if ($comida->delete()) {
                DB::commit();
                $data = array(
                    'mensaje' => 'La comida fue dada de baja.',
                    'estatus' => 'success',
                    'codigo' => 200
                );
            }else{
                DB::rollBack();
                $data = array(
                    'mensaje' => 'No se elimino la comida.',
                    'estatus' => 'error',
                    'codigo' => 400
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

        return response()->json($data, $data['code']);
    }

    public function getsearch($nombre_comida = '', Request $request){
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

            $comidas = Menu::where('platillo', 'like', '%'.$nombre_comida.'%')->orderby('id', 'desc')->get();

            if (count($comidas) > 0) {
                $data = array(
                    'menu' => $comidas,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['menu'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay comidas registrada',
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

    // funciones referente a los pedidos.

    public function list_orders(Request $request){
        try {
            $jwtAuth = new JwtAuth();

            // Obtener el token y validar autenticación
            $token = $request->bearerToken();
            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $rol = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            // Definir la consulta base
            $query = Pedido::with([
                'menu',
                'empresas',
                'tipo_menu',
                'usuario',
                'estatus',
            ]);

            // Aplicar filtros según el tipo de usuario
            switch ($rol) {
                case 'Admin_Role':
                    // Ejecutar la consulta
                    $pedidos = $query->orderBy('id', 'desc')
                        ->get();
                break;

                case 'User_Role':
                    $pedidos = array();
                break;
                case 'Chef_Role':
                    $data_user = $checktoken['data'];
                    $query->where('id_empresa', $data_user->id_empresa);
                    $pedidos = $query->where('id_estatus', 1)
                        ->orwhere('id_estatus', 2)
                        ->orderBy('id', 'desc')
                        ->get();
                break;
                case 'Company_Role':
                    $data_user = $checktoken['data'];
                    $query->where('id_empresa', $data_user->id_empresa);
                    $pedidos = $query->orderBy('id', 'desc')
                        ->get();
                break;
                case 'Delivery_Role':
                    $data_user = $checktoken['data'];
                    $query->where('id_empresa', $data_user->id_empresa);
                    $pedidos = $query->where('id_estatus', 3)
                        ->orderBy('id', 'desc')
                        ->get();
                break;
            }

            // Construcción de la respuesta
            if ($pedidos->count() > 0) {
                return response()->json($pedidos, 200);
            } else {
                return response()->json([
                    'mensaje' => 'No hay pedidos registrados',
                    'estatus' => 'error',
                    'codigo' => 400
                ], 400);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'mensaje' => $th->getMessage(),
                'estatus' => 'error',
                'codigo' => 400
            ], 400);
        }
    }

    public function create_order(Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();
            $platillos = !is_null($request->platillos) && isset($request->platillos) ? json_decode($request->platillos) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;
            $numero_tarjeta = !is_null($request->numero_tarjeta) && isset($request->numero_tarjeta) ? strip_tags($request->numero_tarjeta) :null;
            $cvv = !is_null($request->cvv) && isset($request->cvv) ? strip_tags($request->cvv) :null;
            $estatus = 1;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'platillos' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $id_empresa = $checktoken["data"]->id_empresa;
            $id_usuario = $checktoken["data"]->sub;

            $tarjeta = new Tarjeta();
            $tarjeta->numero_tarjeta = $numero_tarjeta;
            $tarjeta->cvv = $cvv;
            $tarjeta->id_user = $id_usuario;

            if($tarjeta->save()){
                $pedido = new Pedido();
                $pedido->descripcion = $descripcion;
                $pedido->id_empresa = $id_empresa;
                $pedido->id_user = $id_usuario;
                $pedido->id_estatus = $estatus;

                if($pedido->save()){
                    if(count($platillos)){
                        // Agregar los menús con cantidades en la tabla pivote pedido_menu
                        foreach ($platillos as $platillo) {
                            $pedido->menu()->attach($platillo->comida->id, ['cantidad' => $platillo->cantidad]);
                        }

                        DB::commit(); // Confirmar la transacción
                        $data = array(
                            'mensaje' => 'Tu Orden esta Completo!!',
                            'estatus' => 'success',
                            'codigo' => 200
                        );
                    }
                }else{
                    throw new \Exception("Hubo un error con tu pedido.", 400);
                }
            }else{
                throw new \Exception("Hubo un error con tu tarjeta.", 400);
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

    public function user_order(Request $request){
        try {
            $jwtAuth = new JwtAuth();

            // Obtener el token y validar autenticación
            $token = $request->bearerToken();
            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $type = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            // Definir la consulta base
            $query = Pedido::with([
                'menu',
                'empresas',
                'tipo_menu',
                'usuario',
                'estatus',
            ]);

            // Ejecutar la consulta
            $pedido = $query->where('id_user', $checktoken['data']->sub)->orderBy('id', 'desc')->first();

            // Construcción de la respuesta
            if ($pedido->count() > 0) {
                 // Calcular el total sumando (cantidad * costo) de cada menú
                $totalCost = $pedido->menu->sum(function ($menu) {
                    return $menu->pivot->cantidad * $menu->costo;
                });

                $data = array(
                    "pedido" => $pedido,
                    "costo_total" => $totalCost,
                    "estatus" => "success",
                    "codigo" => 200

                );

                return response()->json($data, 200);
            } else {
                return response()->json([
                    'mensaje' => 'No hay pedidos solicitados',
                    'estatus' => 'error',
                    'codigo' => 400
                ], 400);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'mensaje' => $th->getMessage(),
                'estatus' => 'error',
                'codigo' => 400
            ], 400);
        }
    }

    public function user_historial(Request $request){
        try {
            $jwtAuth = new JwtAuth();

            // Obtener el token y validar autenticación
            $token = $request->bearerToken();
            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Definir la consulta base
            $query = Pedido::with([
                'menu',
                'empresas',
                'tipo_menu',
                'usuario',
                'estatus',
            ]);

            // Ejecutar la consulta
            $pedidos = $query->where('id_user', $checktoken['data']->sub)->orderBy('id', 'desc')->get();

            // Construcción de la respuesta
            if ($pedidos->count() > 0) {
                // Recorrer todos los pedidos y calcular el total de cada uno
                $pedidos->transform(function ($pedido) {
                    $pedido->costo_total = $pedido->menu->sum(function ($menu) {
                        return $menu->pivot->cantidad * $menu->costo;
                    });

                    return $pedido;
                });
                $data = array(
                    "pedidos" => $pedidos,
                    "estatus" => "success",
                    "codigo" => 200

                );

                return response()->json($pedidos, 200);
            } else {
                return response()->json([
                    'mensaje' => 'No hay pedidos solicitados',
                    'estatus' => 'error',
                    'codigo' => 400
                ], 400);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'mensaje' => $th->getMessage(),
                'estatus' => 'error',
                'codigo' => 400
            ], 400);
        }
    }

    public function update_order($id_pedido, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();
            $estatus = !is_null($request->pedido) && isset($request->pedido) && is_array($request->pedido) ? $request->pedido['estatus']['id'] : null;

            // Validaciones de cada campo
            $validate = Validator::make($request->pedido['estatus'], [
                'id' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);


            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $pedido = Pedido::where("id", $id_pedido)->first();
            $pedido->id_estatus = $estatus;

            if($pedido->update()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Orden Completada!!',
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

    public function cancel_order($id_pedido, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();
            $estatus = !is_null($request->pedido) && isset($request->pedido) && is_array($request->pedido) ? $request->pedido['estatus']['id'] : null;

            // Validaciones de cada campo
            $validate = Validator::make($request->pedido['estatus'], [
                'id' => 'required',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            $pedido = Pedido::where("id", $id_pedido)->first();
            $pedido->id_estatus = $estatus;

            if($pedido->update()){
                DB::commit();
                $data = array(
                    'mensaje' => 'Orden Cancelada!!',
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

    // funciones dedicadas al tipo de menus
    public function list_type_menu(Request $request){
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
                    $data_company = $checktoken['data'];
                    $tipos_menu = TipoMenu::orderBy('id', 'desc')->get();
                break;

                case 'User_role':
                    $tipos_menu = array();
                break;

                case 'Chef_Role':
                    $tipos_menu = array();
                break;

                case 'Company_Role':
                    $data_company = $checktoken['data'];
                    $tipos_menu = TipoMenu::where('id_empresa', $data_company->sub)->orderBy('id', 'desc')->get();
                break;

                case 'Delivery_Role':
                    $tipos_menu = array();
                break;
            }

            if (count($tipos_menu) > 0) {
                $data = array(
                    'tipo_menu' => $tipos_menu,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($tipos_menu, $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay comida registrada',
                    'estatus' => 'error',
                    'codigo' => 200
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

    public function food($id_comida, Request $request){
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
                   $data_user = $checktoken['data'];
                   $comidas = Menu::with(['empresa', 'tipo_menu'])
                       ->where("id", "=", $id_comida)
                       ->orderBy('id', 'desc')
                       ->first();
               break;

               case 'User_Role':
                   $comidas = array();
               break;

               case 'Chef_Role':
                   $data_user = $checktoken['data'];
                   $comidas = Menu::with(['empresa', 'tipo_menu'])
                       ->where('id_empresa', $data_user->id_empresa)
                       ->orWhere("id", "=", $id_comida)
                       ->orderBy('id', 'desc')
                       ->first();
               break;
               case 'Company_Role':
                   $data_company = $checktoken['data'];
                   $comidas = Menu::with(['empresa', 'tipo_menu'])
                   ->where('id_empresa', $data_company->sub)
                   ->orWhere("id", "=", $id_comida)
                   ->orderBy('id', 'desc')
                   ->first();
               break;
               case 'Delivery_Role':
                   $comidas = array();
               break;
           }

           if (is_object($comidas)) {
               $data = array(
                   'menu' => $comidas,
                   'estatus' => 'success',
                   'codigo' => 200
               );
               return response()->json($data['menu'], $data['codigo']);
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

    public function add_type_menu(Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $id_empresa_user = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required|string',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $rol = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            switch ($rol) {
                case 'Admin_Role':
                    $tipo_menu = new TipoMenu();
                    $tipo_menu->nombre_menu = $nombre;
                    $tipo_menu->descripcion_menu = $descripcion;
                    $tipo_menu->id_empresa = null;

                    if($tipo_menu->save()){
                        DB::commit();
                        $data = array(
                            'mensaje' => 'Menu Guardado Correctamente!!',
                            'estatus' => 'success',
                            'codigo' => 200
                        );
                    }
                break;

                case 'User_role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para dar de alta!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;

                case 'Chef_Role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para dar de alta!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;

                case 'Company_Role':
                    $data_company = $checktoken['data'];
                    $tipo_menu = new TipoMenu();
                    $tipo_menu->nombre_menu = $nombre;
                    $tipo_menu->descripcion_menu = $descripcion;
                    $tipo_menu->id_empresa = $data_company->sub;

                    if($tipo_menu->save()){
                        DB::commit();
                        $data = array(
                            'mensaje' => 'Menu Guardado Correctamente!!',
                            'estatus' => 'success',
                            'codigo' => 200
                        );
                    }
                break;

                case 'Delivery_Role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para dar de alta!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;
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

    public function update_menu($id_comida, Request $request){
        try {
            DB::beginTransaction();
            $data = null;
            $id_empresa_user = null;
            $jwtAuth = new JwtAuth();

            //Recoger post
            $token = $request->bearerToken();
            $nombre = !is_null($request->nombre) && isset($request->nombre) ? strip_tags($request->nombre) : null;
            $descripcion = !is_null($request->descripcion) && isset($request->descripcion) ? strip_tags($request->descripcion) :null;
            $id_empresa = !is_null($request->id_empresa) && isset($request->id_empresa) ? $request->id_empresa :null;
            $estatus = !is_null($request->estatus) && isset($request->estatus) ? $request->estatus :null;
            $type = !is_null($request->type) && isset($request->type) ? strip_tags($request->type) :null;

            // Validaciones de cada campo
            $validate = Validator::make($request->all(), [
                'nombre' => 'required|string',
            ]);

            if ($validate->fails()) {
                $data = array(
                    'mensaje' => $validate->errors()->all(),
                    'estatus' => 'error',
                    'codigo' => 400
                );
                return response()->json($data, $data['codigo']);
            }

            $checktoken = $jwtAuth->checkToken($token);

            if (!$checktoken['auth']) {
                throw new \Exception("No te encuentras autenticado.", 401);
            }

            // Determinar si es una empresa o un usuario
            $rol = !is_null($checktoken['data']->role) && isset($checktoken['data']->role) ? $checktoken['data']->role->nombre : 'User_Role';

            if ($type == "company") {
                $id_empresa_user = $checktoken["data"]->sub;
            } else {
                $id_empresa_user = $checktoken["data"]->id_empresa;
            }

            $tipo_menu = TipoMenu::where('id', $id_comida)->first();

            switch ($rol) {
                case 'Admin_Role':
                    $tipo_menu->nombre_menu = $tipo_menu->nombre_menu != $nombre ? $nombre : $tipo_menu->nombre_menu;
                    $tipo_menu->descripcion_menu = $tipo_menu->descripcion_menu != $descripcion ? $descripcion : $tipo_menu->descripcion_menu;
                    $tipo_menu->estatus = $tipo_menu->estatus != $estatus ? $estatus : $tipo_menu->estatus;

                    if($tipo_menu->update()){
                        DB::commit();
                        $data = array(
                            'mensaje' => 'Menu Actualizado Correctamente!!',
                            'estatus' => 'success',
                            'codigo' => 200
                        );
                    }
                break;

                case 'User_role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para actualizar!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;

                case 'Chef_Role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para actualizar!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;

                case 'Company_Role':
                    $data_company = $checktoken['data'];
                    $tipo_menu->nombre_menu = $tipo_menu->nombre_menu != $nombre ? $nombre : $tipo_menu->nombre_menu;
                    $tipo_menu->descripcion_menu = $tipo_menu->descripcion_menu != $descripcion ? $descripcion : $tipo_menu->descripcion_menu;
                    $tipo_menu->id_empresa = $tipo_menu->id_empresa != $data_company->sub ? $data_company->sub : $tipo_menu->id_empresa;
                    $tipo_menu->estatus = $tipo_menu->estatus != $estatus ? $estatus : $tipo_menu->estatus;

                    if($tipo_menu->update()){
                        DB::commit();
                        $data = array(
                            'mensaje' => 'Menu Actualizado Correctamente!!',
                            'estatus' => 'success',
                            'codigo' => 200
                        );
                    }
                break;

                case 'Delivery_Role':
                    $data = array(
                        'mensaje' => 'No tienes permisos para actualizar!!',
                        'estatus' => 'error',
                        'codigo' => 401
                    );
                break;
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

    public function getMenuById($id_menu, Request $request){
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

            $menu = TipoMenu::where('id', $id_menu)->first();

            if (is_object($menu)) {
                $data = array(
                    'menu' => $menu,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['menu'], $data['codigo']);
            }else{
                $data = array(
                    'mensaje' => 'No hay menu registrada',
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

    public function getMenusearch($nombre_menu = '', Request $request){
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

            $menus = TipoMenu::where('nombre_menu', 'like', '%'.$nombre_menu.'%')->orderby('id', 'desc')->get();

            if (count($menus) > 0) {
                $data = array(
                    'menu' => $menus,
                    'estatus' => 'success',
                    'codigo' => 200
                );
                return response()->json($data['menu'], $data['codigo']);
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
}
