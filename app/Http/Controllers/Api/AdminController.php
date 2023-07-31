<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use App\Models\Servicio;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AdminController extends Controller
{

    public function adminProfile(Request $request) {

        if (auth()->user()->role_id===1) {
            return response()->json([
                "message" => "PERFIL DE ADMINISTRADOR",
                "userData" => auth()->user()
            ]);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function registerEmployee(Request $request) {

        if (auth()->user()->role_id===1) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|regex:/^[A-Za-z\s]+$/',
                'email' => 'required|email|unique:users',
                'password' => 'required|string',
                'cedula' => 'required|string|max:10|unique:users',
                'celular' => 'required|string|max:10|unique:users',
                'fnac' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                'direccion' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            //alta del usuario
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->cedula = $request->cedula;
            $user->celular = $request->celular;
            $user->fnac = $request->fnac;
            $user->direccion = $request->direccion;
            $user->role_id = 3;
            $user->save();
            //respuesta
            $role = Role::findOrFail(2);
            return response()->json([
                "message" => "metodo registerEmployee exitoso",
                'user' => $user,
                'role' => $role
            ]);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

//CRUD CATEGORIAS
    public function indexCat()
    {
        if (auth()->user()->role_id===1) {
            $categorias = Categoria::all();
            return response()->json($categorias);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function storeCat(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:20|unique:categorias',
            'descripcion' => 'required|string|max:40',
            'image_url' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error en la validación de datos',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        if (auth()->user()->role_id===1) {

            $imagePath = $request->file('image_url')->getRealPath();
            $uploadedImage = Cloudinary::upload($imagePath);
            // Obtén la URL de la imagen subida
            $imageUrl = $uploadedImage->getSecurePath();

            // Guarda la URL en la tabla
            $categoria = new Categoria();
            $categoria->nombre=$request->input('nombre');
            $categoria->descripcion=$request->input('descripcion');
            $categoria->image_url = $imageUrl;
            $categoria->save();

            return response()->json($categoria, 201);
        } else {

            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function updateCat(Request $request, $id)
    {
        if (auth()->user()->role_id===1) {


            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|unique:categorias|max:20',
                'descripcion' => 'sometimes|required|max:40',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $categoria = Categoria::findOrFail($id);

            if ($request->has('nombre')) {
                $categoria->nombre = $request->input('nombre');
            }

            if ($request->has('descripcion')) {
                $categoria->descripcion = $request->input('descripcion');
            }

            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image')->getRealPath();
                $uploadedImage = Cloudinary::upload($imagePath);
                $imageUrl = $uploadedImage->getSecurePath();
                $categoria->image_url = $imageUrl;
            }

            $categoria->save();

            return response()->json($categoria, 200);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroyCat($id)
    {
        if (auth()->user()->role_id===1) {
            $categoria = Categoria::findOrFail($id);

            if ($categoria->servicios()->exists()) {
                return response()->json([
                    "message" => "No se puede eliminar la categoría porque tiene servicios relacionados"
                ]);
            }

            if ($categoria->delete()) {
                return response()->json([
                    "message" => "Categoria eliminada exitosamente"
                ]);
            } else {
                return response()->json([
                    "message" => "No se pudo eliminar la categoria"
                ]);
            }
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


//CRUD SERVICIOS

//mostrar servicios de una categoria
    public function showCat($id)
    {
        if (auth()->user()->role_id===1) {

            $categoria = Categoria::findOrFail($id);
            $servicios = $categoria->servicios()->get();
            return response()->json($servicios);

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

//C
    public function storeServ(Request $request)
    {
        if (auth()->user()->role_id===1) {

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:20|unique:servicios',
                'vehiculo' => 'required|string|max:20',
                'descripcion' => 'required|string|max:40',
                'precio_h' => 'required|numeric',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'categorias' => [
                    'required',
                    'array',
                    function ($attribute, $value, $fail) {
                        foreach ($value as $item) {
                            if (!is_numeric($item)) {
                                $fail('El campo ' . $attribute . ' debe contener solo números.');
                            } else {
                                $categoria = Categoria::find($item);
                                if (!$categoria) {
                                    $fail('La categoría con ID ' . $item . ' no existe.');
                                }
                            }
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $imagePath = $request->file('image')->getRealPath();
            $uploadedImage = Cloudinary::upload($imagePath);
            // Obtén la URL de la imagen subida
            $imageUrl = $uploadedImage->getSecurePath();

            //alta del servicio
            $servicio = new Servicio();
            $servicio->nombre = $request->nombre;
            $servicio->vehiculo = $request->vehiculo;
            $servicio->descripcion = $request->descripcion;
            $servicio->precio_h = $request->precio_h;
            $servicio->image_url= $imageUrl;
            $servicio->save();


            // Obtener las categorías seleccionadas
            $categoriasSeleccionadas = $request->categorias;

            // Asignar las categorías al servicio
            foreach ($categoriasSeleccionadas as $categoriaId) {
                $categoria = Categoria::find($categoriaId);

                if ($categoria) {
                    $servicio->categorias()->attach($categoria);
                }
            }
            return response()->json($servicio, 201);

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }



    public function showServ($id)
    {
        if (auth()->user()->role_id===1) {

            //$servicio = Servicio::findOrFail($id);

            $servicio = Servicio::with('categorias')->find($id);
            return response()->json($servicio);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateServ(Request $request, $id)
    {
        if (auth()->user()->role_id===1) {
            $servicio = Servicio::findOrFail($id);

                // Validar los campos ingresados
            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:20|unique:servicios',
                'vehiculo' => 'sometimes|required|max:20',
                'descripcion' => 'sometimes|required|max:40',
                'precio_h' => 'sometimes|required|numeric|max:10',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Actualizar los campos que se hayan proporcionado en la solicitud
            if ($request->has('nombre')) {
                $servicio->nombre = $request->input('nombre');
            }

            if ($request->has('vehiculo')) {
                $servicio->vehiculo = $request->input('vehiculo');
            }

            if ($request->has('descripcion')) {
                $servicio->descripcion = $request->input('descripcion');
            }

            if ($request->has('precio_h')) {
                $servicio->precio_h = $request->input('precio_h');
            }

            // Actualizar la imagen si se ha proporcionado una nueva
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->getRealPath();
                $uploadedImage = Cloudinary::upload($imagePath);
                $imageUrl = $uploadedImage->getSecurePath();
                $servicio->image_url = $imageUrl;
            }

            $servicio->save();
            return response()->json($servicio, 200);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function destroyServ($id)
    {
        if (auth()->user()->role_id===1) {

            $servicio = Servicio::findOrFail($id);

            if ($servicio->pedidos()->exists()) {
                return response()->json([
                    "message" => "No se puede eliminar el servicio porque están en proceso de atención"
                ]);
            }

            if ($servicio->delete()) {
                return response()->json([
                    "message" => "Servicio eliminado exitosamente"
                ]);
            } else {
                return response()->json([
                    "message" => "No se pudo eliminar el servicio"
                ]);
            }

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

/*     public function show($id)
    {
        if (auth()->user()->role_id===1) {
            $servicio = Servicio::findOrFail($id);
            return response()->json($servicio);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role_id===1) {
            $servicio = Servicio::findOrFail($id);
            $servicio->update($request->all());
            return response()->json($servicio, 200);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }


    }

    public function destroy($id)
    {
        if (auth()->user()->role_id===1) {
            Servicio::findOrFail($id)->delete();
            return response()->json(null, 204);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }


    }
 */


//mostrar todos los servicios
    public function indexServ()
    {
        if (auth()->user()->role_id===1) {
            $servicios = Servicio::all();
            return response()->json($servicios);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

//pedidos
    public function showPedidos(){
        if (auth()->user()->role_id===1) {
            $pedidos = Pedido::with('servicios', 'users')->get();
            return response()->json($pedidos);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function putState(Request $request, $id)
    {
        if (auth()->user()->role_id===1) {

            $validator = Validator::make($request->all(), [
                'estado' => 'required|string|in:aprobado,rechazado',
                'observacionAdmin' => 'nullable|string|max:40',
                'empleado_id' => 'nullable|exists:users,id,role_id,3',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $pedido = Pedido::find($id);
            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }
            $pedido->estado = $request->estado;

            if ($request->has('observacionAdmin')) {
                $pedido->observacionAdmin = $request->input('observacionAdmin');
            }else{
                $pedido->observacionAdmin = "-";
            }

            //asignar empleado
            $userId = $request->empleado_id;
            $user = User::find($userId);
            if($user){
                if($user->role_id===3){

                    //VERIFICAR QUE no se pueda asignar mientras no finalice algun pedido
                    $relatedPedido = $user->pedidos()->whereIn('estado', ['rechazado', 'aprobado', 'pendiente'])->first();
                    if ($relatedPedido) {
                        return response()->json([
                            'message' => 'El usuario ya está relacionado a otro pedido sin finalizar',
                            'user' => $user,
                            'pedidos' => $relatedPedido
                        ]);
                    }

                    $pedido->users()->syncWithoutDetaching([$request->empleado_id]);
                }else{
                    return response()->json([
                        'message' => "El id no corresponde "
                        //'rol' => $user->role_id
                ]);
                }
            }

            $pedido->save();
            $pedido->load('users');
            return response()->json([
                'message' => 'Estado modificado correctamente',
                'pedido' => $pedido
        ]);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public function showPedido($id)
    {
        if (auth()->user()->role_id===1) {
            $pedido = Pedido::findOrFail($id);
            return response()->json($pedido);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function comentarioAdmin(Request $request, $id)
    {
        if (auth()->user()->role_id===1) {

            $validator = Validator::make($request->all(), [
                'comentarioAdmin' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $pedido = Pedido::find($id);
            if (!$pedido) {
                return response()->json(['message' => 'Pedido no encontrado'], 404);
            }
            $pedido->comentarioAdmin = $request->comentarioAdmin;
            $pedido->save();
            return response()->json([
                'message' => 'Comentario agregado',
                'pedido' => $pedido
        ]);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }

    }


    /* public function uploadImage(Request $request)
    {
        $imagePath = $request->file('image')->getRealPath();

        $uploadedImage = Cloudinary::upload($imagePath);

        // Obtén la URL de la imagen subida
        $imageUrl = $uploadedImage->getSecurePath();

        // Guarda la URL en la tabla
        $categoria = new Categoria();
        $categoria->nombre="cat test";
        $categoria->descripcion="cat test";
        $categoria->image_url = $imageUrl;
        $categoria->save();

        return response()->json(['url' => $imageUrl]);
    } */




}
