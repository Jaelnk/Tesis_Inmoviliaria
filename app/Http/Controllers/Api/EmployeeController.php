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
