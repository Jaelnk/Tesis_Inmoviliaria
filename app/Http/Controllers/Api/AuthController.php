<?php

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


class AuthController extends Controller
{
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
