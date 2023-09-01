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



    /**
 * @OA\Post(
 *      path="/admin/registerEmployee",
 *      operationId="registerEmployee",
 *      tags={"Admin"},
 *      summary="Registrar nuevo empleado",
 *      security={{ "sanctum":{} }},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="name", type="string", example="John Doe"),
 *              @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *              @OA\Property(property="password", type="string", example="password123"),
 *              @OA\Property(property="cedula", type="string", example="1234567890"),
 *              @OA\Property(property="celular", type="string", example="1234567890"),
 *              @OA\Property(property="fnac", type="string", format="date", example="1990-01-01"),
 *              @OA\Property(property="direccion", type="string", example="123 Main St")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Empleado registrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Método registerEmployee exitoso")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado o error de validación",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado o error de validación")
 *          )
 *      )
 * )
 */
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

/**
 * @OA\Get(
 *      path="/admin/categories",
 *      operationId="getCategories",
 *      tags={"Admin"},
 *      summary="Obtener todas las categorías",
 *      security={{ "sanctum":{} }},
 *      @OA\Response(
 *          response=200,
 *          description="Lista de categorías",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="OK")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      )
 * )
 */
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

    /**
 * @OA\Post(
 *      path="/admin/categories",
 *      operationId="createCategoria",
 *      tags={"Admin"},
 *      summary="Crear una nueva categoría",
 *      security={{ "sanctum":{} }},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="nombre", type="string", example="Nombre de la categoría"),
 *              @OA\Property(property="descripcion", type="string", example="Descripción de la categoría"),
 *              @OA\Property(property="image_url", type="string", format="binary", description="Imagen de la categoría")
 *          )
 *      ),
 *      @OA\Response(
 *          response=201,
 *          description="Categoría creada",
 *          @OA\JsonContent(
 *              @OA\Property(property="id", type="integer"),
 *              @OA\Property(property="nombre", type="string"),
 *              @OA\Property(property="descripcion", type="string"),
 *              @OA\Property(property="image_url", type="string"),
 *              @OA\Property(property="created_at", type="string", format="date-time"),
 *              @OA\Property(property="updated_at", type="string", format="date-time")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Error en la validación de datos"),
 *              @OA\Property(property="errors", type="object")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      )
 * )
 */
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

/**
 * @OA\Put(
 *      path="/admin/categories/{id}",
 *      operationId="updateCategoria",
 *      tags={"Admin"},
 *      summary="Actualizar una categoría existente",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID de la categoría a actualizar",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="nombre", type="string", example="Nombre actualizado de la categoría"),
 *              @OA\Property(property="descripcion", type="string", example="Descripción actualizada de la categoría"),
 *              @OA\Property(property="image", type="string", format="binary", description="Imagen actualizada de la categoría")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Categoría actualizada",
 *          @OA\JsonContent(
 *              @OA\Property(property="id", type="integer"),
 *              @OA\Property(property="nombre", type="string"),
 *              @OA\Property(property="descripcion", type="string"),
 *              @OA\Property(property="image_url", type="string"),
 *              @OA\Property(property="created_at", type="string", format="date-time"),
 *              @OA\Property(property="updated_at", type="string", format="date-time")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Error en la validación de datos"),
 *              @OA\Property(property="errors", type="object")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Categoría no encontrada",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Categoría no encontrada")
 *          )
 *      )
 * )
 */
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

    /**
 * @OA\Delete(
 *      path="/admin/categories/{id}",
 *      operationId="deleteCategoria",
 *      tags={"Admin"},
 *      summary="Eliminar una categoría",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID de la categoría a eliminar",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Categoría eliminada exitosamente",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Categoria eliminada exitosamente")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="No se pudo eliminar la categoría o tiene servicios relacionados",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="No se pudo eliminar la categoria o tiene servicios relacionados")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Categoría no encontrada",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Categoría no encontrada")
 *          )
 *      )
 * )
 */
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

//mostrar servicios de una categoria -admin

/**
 * @OA\Get(
 *      path="/admin/categories/{id}/services",
 *      operationId="getAdminCategoryServices",
 *      tags={"Admin"},
 *      summary="Mostrar servicios de una categoría para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID de la categoría",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Lista de servicios de la categoría",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  required={"id", "nombre", "vehiculo", "descripcion", "precio_h", "image_url", "created_at", "updated_at"},
 *                  @OA\Property(property="id", type="integer", example=1),
 *                  @OA\Property(property="nombre", type="string", example="Transporte liviano"),
 *                  @OA\Property(property="vehiculo", type="string", example="Toyota Hiace"),
 *                  @OA\Property(property="descripcion", type="string", example="Furgoneta de tamaño mediano con capacidad de 10 m³"),
 *                  @OA\Property(property="precio_h", type="string", example="24.99"),
 *                  @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291499/bgifc4ymzvmm9jbilmk0.jpg"),
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time"),
 *                  @OA\Property(property="pivot", type="object",
 *                      @OA\Property(property="categoria_id", type="integer", example=2),
 *                      @OA\Property(property="servicio_id", type="integer", example=1)
 *                  )
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Categoría no encontrada",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Categoría no encontrada")
 *          )
 *      )
 * )
 */
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

/**
 * @OA\Post(
 *      path="/admin/categories/{id}/newServ",
 *      operationId="createAdminService",
 *      tags={"Admin"},
 *      summary="Crear un nuevo servicio en una categoría para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID de la categoría",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          description="Datos del nuevo servicio",
 *          @OA\JsonContent(
 *              required={"nombre", "vehiculo", "descripcion", "precio_h", "image", "categorias"},
 *              @OA\Property(property="nombre", type="string", example="Servicio de transporte"),
 *              @OA\Property(property="vehiculo", type="string", example="Toyota Hiace"),
 *              @OA\Property(property="descripcion", type="string", example="Servicio de transporte para mudanzas"),
 *              @OA\Property(property="precio_h", type="number", example=24.99),
 *              @OA\Property(property="image", type="string", format="binary"),
 *              @OA\Property(property="categorias", type="array", @OA\Items(type="integer", example=1, description="ID de categoría")),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=201,
 *          description="Servicio creado exitosamente",
 *          @OA\JsonContent(
 *              type="object",
 *              required={"id", "nombre", "vehiculo", "descripcion", "precio_h", "image_url", "created_at", "updated_at"},
 *              @OA\Property(property="id", type="integer", example=1),
 *              @OA\Property(property="nombre", type="string", example="Servicio de transporte"),
 *              @OA\Property(property="vehiculo", type="string", example="Toyota Hiace"),
 *              @OA\Property(property="descripcion", type="string", example="Servicio de transporte para mudanzas"),
 *              @OA\Property(property="precio_h", type="number", example=24.99),
 *              @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291499/bgifc4ymzvmm9jbilmk0.jpg"),
 *              @OA\Property(property="created_at", type="string", format="date-time"),
 *              @OA\Property(property="updated_at", type="string", format="date-time"),
 *              @OA\Property(property="categorias", type="array", @OA\Items(
 *                  type="object",
 *                  required={"id", "nombre", "descripcion", "image_url", "created_at", "updated_at"},
 *                  @OA\Property(property="id", type="integer", example=1),
 *                  @OA\Property(property="nombre", type="string", example="Categoría de transporte"),
 *                  @OA\Property(property="descripcion", type="string", example="Categoría de servicios de transporte"),
 *                  @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291493/usiynsb8itzqllpxj3mx.jpg"),
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time")
 *              )),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Error en la validación de datos"),
 *              @OA\Property(property="errors", type="object")
 *          )
 *      ),
 * )
 */
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


/**
 * @OA\Get(
 *      path="/admin/service/{id}",
 *      operationId="showAdminService",
 *      tags={"Admin"},
 *      summary="Mostrar detalles de un servicio para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID del servicio",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Detalles del servicio",
 *          @OA\JsonContent(
 *              type="object",
 *              required={"id", "nombre", "vehiculo", "descripcion", "precio_h", "image_url", "created_at", "updated_at", "categorias"},
 *              @OA\Property(property="id", type="integer", example=1),
 *              @OA\Property(property="nombre", type="string", example="Servicio de transporte"),
 *              @OA\Property(property="vehiculo", type="string", example="Toyota Hiace"),
 *              @OA\Property(property="descripcion", type="string", example="Servicio de transporte para mudanzas"),
 *              @OA\Property(property="precio_h", type="number", example=24.99),
 *              @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291499/bgifc4ymzvmm9jbilmk0.jpg"),
 *              @OA\Property(property="created_at", type="string", format="date-time"),
 *              @OA\Property(property="updated_at", type="string", format="date-time"),
 *              @OA\Property(property="categorias", type="array", @OA\Items(
 *                  type="object",
 *                  required={"id", "nombre", "descripcion", "image_url", "created_at", "updated_at"},
 *                  @OA\Property(property="id", type="integer", example=1),
 *                  @OA\Property(property="nombre", type="string", example="Categoría de transporte"),
 *                  @OA\Property(property="descripcion", type="string", example="Categoría de servicios de transporte"),
 *                  @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291493/usiynsb8itzqllpxj3mx.jpg"),
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time")
 *              )),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Servicio no encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Servicio no encontrado")
 *          )
 *      )
 * )
 */
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


    /**
 * @OA\Put(
 *      path="/admin/service/{id}",
 *      operationId="updateAdminService",
 *      tags={"Admin"},
 *      summary="Actualizar un servicio para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID del servicio",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="nombre", type="string", example="Nuevo servicio"),
 *              @OA\Property(property="vehiculo", type="string", example="Nuevo vehículo"),
 *              @OA\Property(property="descripcion", type="string", example="Nueva descripción"),
 *              @OA\Property(property="precio_h", type="number", example=30.50),
 *              @OA\Property(property="image", type="string", format="binary")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Servicio actualizado exitosamente",
 *          @OA\JsonContent(
 *              type="object",
 *              required={"id", "nombre", "vehiculo", "descripcion", "precio_h", "image_url", "created_at", "updated_at"},
 *              @OA\Property(property="id", type="integer", example=1),
 *              @OA\Property(property="nombre", type="string", example="Nuevo servicio"),
 *              @OA\Property(property="vehiculo", type="string", example="Nuevo vehículo"),
 *              @OA\Property(property="descripcion", type="string", example="Nueva descripción"),
 *              @OA\Property(property="precio_h", type="number", example=30.50),
 *              @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291499/bgifc4ymzvmm9jbilmk0.jpg"),
 *              @OA\Property(property="created_at", type="string", format="date-time"),
 *              @OA\Property(property="updated_at", type="string", format="date-time")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Error en la validación de datos"),
 *              @OA\Property(property="errors", type="object")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Acceso denegado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Servicio no encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Servicio no encontrado")
 *          )
 *      )
 * )
 */
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

/**
 * @OA\Delete(
 *      path="/admin/service/{id}",
 *      operationId="deleteAdminService",
 *      tags={"Admin"},
 *      summary="Eliminar un servicio para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID del servicio",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Servicio eliminado exitosamente",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Servicio eliminado exitosamente")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="No se puede eliminar el servicio",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="No se puede eliminar el servicio porque están en proceso de atención")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Acceso denegado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Servicio no encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Servicio no encontrado")
 *          )
 *      )
 * )
 */
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

/**
 * @OA\Get(
 *      path="/pedidos",
 *      operationId="getAdminPedidos",
 *      tags={"Admin"},
 *      summary="Obtener la lista de todos los pedidos para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Response(
 *          response=200,
 *          description="Lista de todos los pedidos",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=1),
 *                  @OA\Property(property="partida", type="string", example="Mena 2"),
 *                  @OA\Property(property="destino", type="string", example="Centro"),
 *                  @OA\Property(property="m_pago", type="string", example="efectivo"),
 *                  @OA\Property(property="iva", type="number", example=6.5976),
 *                  @OA\Property(property="subtotal", type="number", example=48.3824),
 *                  @OA\Property(property="p_total", type="number", example=54.98),
 *                  @OA\Property(property="estado", type="string", example="pendiente"),
 *                  @OA\Property(property="observacionCli", type="string", example="Sin observaciones"),
 *                  @OA\Property(property="observacionAdmin", type="string", example=""),
 *                  @OA\Property(property="comentarioAdmin", type="string", example=""),
 *                  @OA\Property(property="comentarioCli", type="string", example=""),
 *                  @OA\Property(property="fecha_hora", type="string", format="date-time", example="2023-07-17 15:30:00"),
 *                  @OA\Property(property="calificacion", type="string", example="0"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time"),
 *                  @OA\Property(property="created_at", type="string", format="date-time")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      )
 * )
 */
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

    /**
 * @OA\Put(
 *      path="/pedidos/{id}",
 *      operationId="updatePedidoState",
 *      tags={"Admin"},
 *      summary="Cambiar el estado de un pedido por el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID del pedido",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer",
 *              format="int64"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="estado", type="string", enum={"aprobado", "rechazado"}, example="aprobado"),
 *              @OA\Property(property="observacionAdmin", type="string", maxLength=40, nullable=true, example="Observaciones del administrador"),
 *              @OA\Property(property="empleado_id", type="integer", nullable=true, example=3),
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Estado del pedido modificado correctamente",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Estado modificado correctamente")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado o error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado o error en la validación de datos"),
 *              @OA\Property(property="errors", type="object", nullable=true)
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Pedido no encontrado"
 *      )
 * )
 */
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

    /**
 * @OA\Get(
 *      path="/pedido/{id}",
 *      operationId="getPedidoById",
 *      tags={"Admin"},
 *      summary="Mostrar un pedido por su ID para el usuario administrador",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID del pedido",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer",
 *              format="int64"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Pedido encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Pedido encontrado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado"),
 *              @OA\Property(property="userData", type="object", nullable=true)
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Pedido no encontrado"
 *      )
 * )
 */
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

    /**
 * @OA\Put(
 *      path="/pedido/{id}",
 *      operationId="agregarComentarioAdmin",
 *      tags={"Admin"},
 *      summary="Agregar un comentario administrativo a un pedido",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID del pedido",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer",
 *              format="int64"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="comentarioAdmin", type="string", example="Comentario administrativo para el pedido.")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Comentario agregado exitosamente",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Comentario agregado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Error en la validación de datos",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Error en la validación de datos"),
 *              @OA\Property(property="errors", type="object", nullable=true)
 *          )
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="No autorizado"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Pedido no encontrado"
 *      )
 * )
 */
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
