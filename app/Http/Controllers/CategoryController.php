<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    // Utiliza middleware en todo menos en las excepciones
    public function __construct()
    {
        $this->middleware('api.auth',
            ['except' =>
                ['index', 'show']
            ]);
    }

    // Obtiene todas las categorías
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories,
        ]);
    }


    // Obtiene una categoría
    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category,
            ];

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoría no existe.',
            ];
        }


        return response()->json($data, $data['code']);

    }


    // Guarda una categoría
    public function store(Request $request)
    {
        // Recoge datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Valida los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required',
            ]);

            // Guarda la categoría
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'La categoría no existe.',
                ];

            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category,
                ];
            }


        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoría.',
            ];
        }


        // Devuelve el resultado
        return response()->json($data, $data['code']);

    }


    // Actualiza una categoría
    public function update($id, Request $request)
    {
        // Recoge datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Valida los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required',
            ]);

            // Quita lo que no se quiere actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            // Actualiza la categoría
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Falta el nombre de la categoría.',
                ];

            } else {
                $category = Category::where('id', $id)
                    ->updateOrCreate($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category,
                    'changes' => $params_array,
                ];
            }


        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoría.',
            ];
        }


        // Devuelve el resultado
        return response()->json($data, $data['code']);
    }


}
