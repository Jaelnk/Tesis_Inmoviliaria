<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Servicio;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
/**
 * @OA\Schema(
 *      schema="Categoria",
 *      required={"id", "nombre", "descripcion", "image_url"},
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="nombre", type="string", example="Mudanzas Básicas/sencillas"),
 *      @OA\Property(property="descripcion", type="string", example="Para mudanzas sencillas y economicas, incluye servicios Camión/Transporte y de Personal"),
 *      @OA\Property(property="image_url", type="string", example="https://res.cloudinary.com/dq81q15op/image/upload/v1691291493/usiynsb8itzqllpxj3mx.jpg"),
 *      @OA\Property(property="created_at", type="string", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */


class UserController extends Controller
{

    /**
     * @OA\Get(
     *      path="/profile",
     *      operationId="getUserProfile",
     *      tags={"Perfil"},
     *      summary="Obtener perfil del usuario autenticado",
     *      security={{ "sanctum":{} }},
     *      @OA\Response(
     *          response=200,
     *          description="Perfil de usuario",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="PERFIL DE USUARIO")
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
    public function profile(Request $request) {

        if (auth()->user()) {
            return response()->json([
                "message" => "PERFIL DE USUARIO",
                "userData" => auth()->user()
            ]);
        } else {
            return response()->json([
                "message" => "acceso denegado"
            ], Response::HTTP_BAD_REQUEST);
        }
    }


//categorias

    /**
     * @OA\Get(
     *      path="/cli/categories",
     *      operationId="getUserCategories",
     *      tags={"Cliente"},
     *      summary="Obtener todas las categorías para el usuario autenticado",
     *      security={{ "sanctum":{} }},
     *      @OA\Response(
     *          response=200,
     *          description="Lista de categorías",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Categoria")
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
        if (auth()->user()->role_id===2) {
            $categorias = Categoria::all();
            return response()->json($categorias);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

//servicios


/**
 * @OA\Get(
 *      path="/cli/categories/{id}/services",
 *      operationId="getCategoryServices",
 *      tags={"Cliente"},
 *      summary="Obtener servicios de una categoría para el usuario autenticado",
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
 *          description="Lista de servicios de la categoría"
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
        if (auth()->user()->role_id===2) {
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

//crear pedido

/**
 * @OA\Post(
 *      path="/newPedido",
 *      operationId="createPedido",
 *      tags={"Cliente"},
 *      summary="Crear un nuevo pedido para el usuario autenticado",
 *      security={{ "sanctum":{} }},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="partida", type="string", example="Dirección de partida"),
 *              @OA\Property(property="destino", type="string", example="Dirección de destino"),
 *              @OA\Property(property="m_pago", type="string", enum={"transferencia", "efectivo"}, example="transferencia"),
 *              @OA\Property(property="servicios", type="array", @OA\Items(type="integer", example=1), example={1, 2}),
 *              @OA\Property(property="observaciones", type="string", example="Observaciones adicionales"),
 *              @OA\Property(property="fecha_hora", type="string", format="date-time", example="2023-08-15 12:00:00")
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Pedido creado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Pedido creado"),
 *              @OA\Property(property="user", type="object",
 *                  @OA\Property(property="partida", type="string"),
 *                  @OA\Property(property="destino", type="string"),
 *                  @OA\Property(property="m_pago", type="string"),
 *                  @OA\Property(property="iva", type="number", format="double"),
 *                  @OA\Property(property="subtotal", type="number", format="double"),
 *                  @OA\Property(property="p_total", type="number", format="double"),
 *                  @OA\Property(property="estado", type="string"),
 *                  @OA\Property(property="observacionCli", type="string"),
 *                  @OA\Property(property="observacionAdmin", type="string"),
 *                  @OA\Property(property="comentarioAdmin", type="string"),
 *                  @OA\Property(property="comentarioCli", type="string"),
 *                  @OA\Property(property="fecha_hora", type="string", format="date-time"),
 *                  @OA\Property(property="calificacion", type="string"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time"),
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="id", type="integer")
 *              )
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
 *          response=403,
 *          description="Acceso denegado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado")
 *          )
 *      )
 * )
 */
    public function newPedido(Request $request)
    {
        if (auth()->user()->role_id===2) {
            //asigna un pedido que puede estar en las proximas 24 horas
            $validator = Validator::make($request->all(), [
                'partida' => 'required|string',
                'destino' => 'required|string',
                'm_pago' => 'required|in:transferencia,efectivo',
                'servicios' => 'required|array',
                'servicios.*' => 'exists:servicios,id',
                'observaciones' => 'nullable|string|max:255',
                'fecha_hora' => [
                    'required',
/*                     'date',
                    'after:' . now()->addHour(), // Asegura que sea después de una hora desde ahora
                    'before:' . now()->addDay() // Asegura que sea antes de un día desde ahora */
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $pedido = new Pedido();
            $pedido->partida =$request->partida;
            $pedido->destino =$request->destino;
            $pedido->m_pago =$request->m_pago;
            // Obtener los servicios seleccionados
            $serviciosSeleccionadas = $request->servicios;
            $total=0;
            foreach ($serviciosSeleccionadas as $servicio_id){
                $servicio = Servicio::findOrFail($servicio_id);
                $total = $total + $servicio->precio_h;
            }
            $pedido->iva =$total*12/100;
            $pedido->subtotal =$total*88/100;
            $pedido->p_total =$total;
            $pedido->estado ='pendiente';

            $pedido->observacionCli =$request->observaciones;
            $pedido->observacionAdmin = '';
            $pedido->comentarioAdmin = '';
            $pedido->comentarioCli = '';
            $pedido->fecha_hora =$request->fecha_hora;
            $pedido->calificacion= '0';
            $pedido->save();

            // Asignar los servicios al pedido
            foreach ($serviciosSeleccionadas as $servicio_id) {
                $servicio = Servicio::find($servicio_id);
                if ($servicio) {
                    $pedido->servicios()->attach($servicio);
                }
            }

            //asociar el cliente al pedido
            $pedido->users()->syncWithoutDetaching([auth()->user()->id]);

            //respuesta
            return response()->json([
                "message" => "Pedido creado",
                'user' => $pedido
            ]);

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

// mostrar pedidos del usuario

/**
 * @OA\Get(
 *      path="/cli/pedidos",
 *      operationId="getUserPedidos",
 *      tags={"Cliente"},
 *      summary="Obtener los pedidos del usuario autenticado",
 *      security={{ "sanctum":{} }},
 *      @OA\Response(
 *          response=200,
 *          description="Lista de pedidos del usuario"
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
    public function showPedido(){
        if (auth()->user()->role_id===2) {

            $user = User::find(auth()->user()->id);
            // Obtener los pedidos del usuario
            $pedidos = $user->pedidos()->get();

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
 *      path="/cli/pedido/{id}",
 *      operationId="addPedidoComentario",
 *      tags={"Cliente"},
 *      summary="Agregar comentario y calificación a un pedido",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID del pedido",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="comentarioCli", type="string", example="Buen servicio, entrega puntual."),
 *              @OA\Property(property="calificacion", type="integer", example=4)
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Comentario agregado al pedido",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Comentario agregado")
 *          )
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Acceso denegado o error de validación",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="acceso denegado o error de validación")
 *          )
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Pedido no encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Pedido no encontrado")
 *          )
 *      )
 * )
 */
    public function comentarPedido(Request $request, $id)
    {
        if (auth()->user()->role_id===2) {

            $validator = Validator::make($request->all(), [
                'comentarioCli' => 'required|string|max:255',
                'calificacion' =>   'required|numeric|between:1,5',
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
            if ($pedido->estado=='finalizado'){
                $pedido->comentarioCli=$request->comentarioCli;
                $pedido->save();
                return response()->json([
                    'message' => 'Comentario agregado',
                    'pedido' => $pedido
            ]);
            }else{
                return response()->json([
                    'message' => '  Comentario no agregado, Pedido aun no finalizado',
                    'pedido' => $pedido
                ]);
            }

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }

    }


    /**
 * @OA\Post(
 *      path="/cotizarpedido",
 *      operationId="cotizarServicios",
 *      tags={"Cliente"},
 *      summary="Realizar cotización de servicios",
 *      security={{ "sanctum":{} }},
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="servicios", type="array", @OA\Items(type="integer", example=1))
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Cotización de servicios",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Cotización")
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
    public function cotizar(Request $request)
    {
        if (auth()->user()->role_id===2) {

            $validator = Validator::make($request->all(), [
                'servicios' => 'required|array',
                'servicios.*' => 'exists:servicios,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error en la validación de datos',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $pedido = new Pedido();

            // Obtener los servicios seleccionados
            $serviciosSeleccionados = $request->servicios;
            $total = 0;
            $servicios = [];

            // Validar servicios repetidos y no existentes
            $serviciosIds = array_unique($serviciosSeleccionados);
            $serviciosExistentes = Servicio::whereIn('id', $serviciosIds)->get();
            $serviciosInexistentes = array_diff($serviciosIds, $serviciosExistentes->pluck('id')->toArray());

            if (!empty($serviciosInexistentes)) {
                return response()->json([
                    'message' => 'Algunos servicios no existen: ' . implode(', ', $serviciosInexistentes)
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($serviciosExistentes as $servicio) {
                $total += $servicio->precio_h;
                $servicios[] = $servicio;
            }

            $pedido->iva = $total * 12 / 100;
            $pedido->subtotal = $total * 88 / 100;
            $pedido->p_total = $total;

            return response()->json([
                'message' => 'Cotización',
                'pedido' => $pedido,
                'servicios' => $servicios
            ]);

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

}
