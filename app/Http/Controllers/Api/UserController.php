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

class UserController extends Controller
{
    //

    public function profile(Request $request) {

        if (auth()->user()->role_id===2) {
            return response()->json([
                "message" => "PERFIL DE USUARIO",
                "userData" => auth()->user()
            ]);
        } else {
            return response()->json([
                "message" => "acceso denegado",
                "userData" => auth()->user()
            ], Response::HTTP_BAD_REQUEST);
        }
    }


//categorias

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

    //mostrar servicios de una categoria
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
