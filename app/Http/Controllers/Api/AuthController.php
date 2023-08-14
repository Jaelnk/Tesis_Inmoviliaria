<?php
/* * @OA\Server(url="http://mudanzapp.duckdns.org/") */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

/**
* @OA\Info(
*             title="Mudanzas API",
*             version="1.0",
*             description="Backend para servicio de mudanzas en la ciudad de Quito"
* )
*

*/
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="registerUser",
     *      tags={"Autenticación"},
     *      summary="Registrar un nuevo usuario",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="name", type="string", example="byron"),
     *                  @OA\Property(property="email", type="string", format="email", example="byron1@hotmail.com"),
     *                  @OA\Property(property="password", type="string", format="password", example="password123"),
     *                  @OA\Property(property="cedula", type="string", example="1234578933"),
     *                  @OA\Property(property="celular", type="string", example="1234578933"),
     *                  @OA\Property(property="fnac", type="string", format="date", example="1999-12-20"),
     *                  @OA\Property(property="direccion", type="string", example="centro")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Registro exitoso",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="metodo register exitosa"),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="name", type="string", example="byron"),
     *                  @OA\Property(property="email", type="string", example="byron1@hotmail.com"),
     *                  @OA\Property(property="cedula", type="string", example="1234578933"),
     *                  @OA\Property(property="celular", type="string", example="1234578933"),
     *                  @OA\Property(property="fnac", type="string", format="date", example="1999-12-20"),
     *                  @OA\Property(property="direccion", type="string", example="centro"),
     *                  @OA\Property(property="role_id", type="integer", example=2),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time"),
     *                  @OA\Property(property="id", type="integer", example=2)
     *              ),
     *              @OA\Property(property="role", type="object",
     *                  @OA\Property(property="id", type="integer", example=2),
     *                  @OA\Property(property="name", type="string", example="Cliente")
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
     *      )
     * )
     */
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[A-Za-z\s]+$/',
            'email' => 'required|email|unique:users|max:20',
            'password' => 'required|string|max:20',
            'cedula' => 'required|string|size:10|unique:users',
            'celular' => 'required|string|size:10|unique:users',
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
        $user->role_id = 2;
        $user->save();

        //respuesta
        $role = Role::findOrFail(2);
        return response()->json([
            "message" => "metodo register exitosa",
            'user' => $user,
            'role' => $role
        ]);
        //return response($user, Response::HTTP_CREATED);
    }

    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="loginUser",
     *      tags={"Autenticación"},
     *      summary="Iniciar sesión",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="email", type="string", format="email", example="adminEmail@example.com"),
     *                  @OA\Property(property="password", type="string", format="password", example="password123")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Inicio de sesión exitoso",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Inicio de sesión exitoso"),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="---"),
     *                  @OA\Property(property="email", type="string", example="adminEmail@example.com"),
     *                  @OA\Property(property="cedula", type="string", example="---"),
     *                  @OA\Property(property="celular", type="string", example="---"),
     *                  @OA\Property(property="fnac", type="string", example="---"),
     *                  @OA\Property(property="direccion", type="string", example="---"),
     *                  @OA\Property(property="role_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              ),
     *              @OA\Property(property="token", type="string", example="526|qSpQNmAYLIVpCKrRy3KT3tFGKjv7sI6O3Cb59gAi")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Credenciales inválidas"
     *      )
     * )
     */
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error en la validación de datos',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            $cookie = cookie('cookie_token', $token, 60 * 24);

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'user' => $user,
                'token' => $token
            ], Response::HTTP_OK)->withoutCookie($cookie);
        } else {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

/**
     * @OA\Put(
     *      path="/perfil",
     *      operationId="updateProfile",
     *      tags={"Perfil"},
     *      summary="Actualizar perfil del usuario",
     *      security={{ "sanctum":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="string", format="email"),
     *              @OA\Property(property="cedula", type="string"),
     *              @OA\Property(property="celular", type="string"),
     *              @OA\Property(property="fnac", type="string", format="date"),
     *              @OA\Property(property="direccion", type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Perfil actualizado",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Perfil actualizado"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autorizado"
     *      )
     * )
     */
    public function perfil(Request $request)
    {
        // Obtener el usuario actual
        $user = User::findOrFail(Auth::user()->id);
        $user->update($request->all());

        return response()->json([
            'message' => 'Perfil actualizado',
            'user' => $user,
        ]);
    }

    /**
     * @OA\Put(
     *      path="/updatePassword",
     *      operationId="updatePassword",
     *      tags={"Perfil"},
     *      summary="Actualizar contraseña del usuario",
     *      security={{ "sanctum":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="password_actual", type="string", format="password"),
     *              @OA\Property(property="password", type="string", format="password")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Contraseña actualizada",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Contraseña actualizada")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Contraseña actual incorrecta",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="La contraseña actual es incorrecta.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autorizado"
     *      )
     * )
     */
    public function updatePassword(Request $request)
    {
        // Obtener el usuario actual
        $user = User::findOrFail(Auth::user()->id);
        //$user->update($request->all());

        // Verificar la contraseña actual
        if (!Hash::check($request->password_actual, $user->password)) {
            return redirect()->back()->with('error', 'La contraseña actual es incorrecta.');
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada',
            'user' => $user,
        ]);
    }


/**
     * @OA\Post(
     *      path="/logout",
     *      operationId="logout",
     *      tags={"Autenticación"},
     *      summary="Cerrar sesión",
     *      security={{ "sanctum":{} }},
     *      @OA\Response(
     *          response=200,
     *          description="Cierre de sesión OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Cierre de sesión OK")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="No autorizado"
     *      )
     * )
     */
    public function logout(Request $request) {
        $cookie = Cookie::forget('cookie_token');

        $request->user()->tokens()->delete();
        return response(["message"=>"Cierre de sesión OK"], Response::HTTP_OK)->withCookie($cookie);
    }

    //VER  USUARIOS CARGADOS

/*     public function allUsers() {
       $users = User::all();
       return response()->json([
        "users" => $users
       ]);
    } */
/*     public function userProfile(Request $request) {
        return response()->json([
            "message" => "userProfile OK",
            "userData" => auth()->user()
        ], Response::HTTP_OK);
    } */

}
