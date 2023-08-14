<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    //

    public function employeeProfile(Request $request) {

        if (auth()->user()->role_id===3) {
            return response()->json([
                "message" => "PERFIL DE EMPLEADO",
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
 * @OA\Get(
 *      path="/emp/pedidos",
 *      operationId="obtenerPedidosEmpleado",
 *      tags={"Empleado"},
 *      summary="Obtener los pedidos de un empleado",
 *      security={{ "sanctum":{} }},
 *      @OA\Response(
 *          response=200,
 *          description="Pedidos obtenidos exitosamente",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Pedidos obtenidos exitosamente")
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
 *      )
 * )
 */
    public function pedidos(Request $request) {

        if (auth()->user()->role_id===3) {
            //$user = User::findOrFail(auth()->user()->role_id);
            //$pedidos = $user->pedidos()->users()->get();
            info(auth()->user());
            $user = User::with('pedidos', 'pedidos.users')->find(auth()->user()->id);
            //Pedido::with('servicios', 'users')->get();
            return response()->json($user);

        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
 * @OA\Put(
 *      path="/emp/pedido/{id}",
 *      operationId="finalizarPedido",
 *      tags={"Empleado"},
 *      summary="Finalizar un pedido",
 *      security={{ "sanctum":{} }},
 *      @OA\Parameter(
 *          name="id",
 *          description="ID del pedido",
 *          required=true,
 *          in="path",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="estado", type="string", enum={"finalizado", "aprobado"}, example="finalizado")
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
 *          description="Pedido no encontrado",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Pedido no encontrado")
 *          )
 *      )
 * )
 */
    public function finalizarPedido(Request $request, $id)
    {
        if (auth()->user()->role_id===3) {

            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:finalizado,aprobado',
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
            $pedido->save();
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

}
