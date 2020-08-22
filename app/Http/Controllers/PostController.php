<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Helpers\JwtAuth;
use Illuminate\Http\Response;

class PostController extends Controller
{
    // Utiliza middleware en todo menos en las excepciones
    public function __construct()
    {
        $this->middleware('api.auth',
            ['except' =>
                ['index',
                    'show',
                    'getImage',
                    'getPostsByCategory',
                    'getPostsByUser']
            ]);
    }

    // Obtiene todos los post
    public function index()
    {
        $posts = Post::all()
            ->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts,
        ], 200);
    }

    // Obtiene un post
    public function show($id)
    {
        $post = Post::find($id)
            ->load('category')
            ->load('user');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
            ];

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe.',
            ];
        }


        return response()->json($data, $data['code']);
    }


    // Guarda un post
    public function store(Request $request)
    {
        // Recoge datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);


        if (!empty($params_array)) {
            // Obtiene usuario identificado
            $user = $this->getIdentity($request);

            // Valida los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required',
            ]);

            // Guarda la categoría
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post (faltan datos).',
                ];

            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post,
                ];
            }


        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha recibido ningún post.',
            ];
        }


        // Devuelve el resultado
        return response()->json($data, $data['code']);
    }


    // Actualiza un post
    public function update($id, Request $request)
    {
        // Recoge datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if (!empty($params_array)) {
            // Valida los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
            ]);

            // Quita lo que no se quiere actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // Obtiene usuario identificado
            $user = $this->getIdentity($request);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Falta el nombre del post.',
                ];

            } else {
                // Busca el post a actualizar
                $post = Post::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

                // Si no está vacío y es objeto
                if (!empty($post) && is_object($post)) {
                    // Actualiza el registro concreto
                    $where = [
                        'id' => $id,
                        'user_id' => $user->sub
                    ];

                    $post->updateOrCreate($where, $params_array);

                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post,
                        'changes' => $params_array,
                    ];

                } else {
                    $data = [
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Post no encontrado o no autorizado',
                    ];
                }
            }


        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ningún post.',
            ];
        }


        // Devuelve el resultado
        return response()->json($data, $data['code']);
    }


    // Borra un post
    public function destroy($id, Request $request)
    {
        // Obtiene usuario identificado
        $user = $this->getIdentity($request);

        // Obtiene el registro (comprobando el autor)
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();

        if (!empty($post)) {
            // Borra el registro
            $post->delete();

            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
            ];

        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe',
            ];
        }


        // Devuelve el resultado
        return response()->json($data, $data['code']);

    }


    public function upload(Request $request)
    {
        // Recoge imagen de la petición
        $image = $request->file('file0');

        // Valida imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guarda imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagen.',
            ];

        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')
                ->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            ];

        }

        // Devuelve datos
        return response($data, $data['code']);

    }

    public function getImage($filename)
    {
        //Comprobar si existe el fichero
        $exists = \Storage::disk('images')
            ->exists($filename);

        if ($exists) {
            // Obtiene la imagen
            $file = \Storage::disk('images')
                ->get($filename);
            // Devuelve la imagen
            return new Response($file, 200);

        } else {
            // Si no, muestra error
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            ];

            return response()->json($data, $data['code']);
        }

    }


    public function getPostsByCategory($id)
    {
        $posts = Post::where('category_id', $id)
            ->get();

        $data = [
            'code' => 200,
            'status' => 'success',
            'posts' => $posts,
        ];

        return response()->json($data, $data['code']);
    }


    public function getPostsByUser($id)
    {
        $posts = Post::where('user_id', $id)
            ->get();

        $data = [
            'code' => 200,
            'status' => 'success',
            'posts' => $posts,
        ];

        return response()->json($data, $data['code']);
    }


    private function getIdentity($request)
    {
        // Obtiene usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }


}
