<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthController extends Controller
{
    /**
     * Login usando el guard 'api' (JWT).
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Usuario autenticado actual (extraído del propio token, sin consultar nada más).
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Invalida el token actual.
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Genera un nuevo token a partir del actual (sin pedir contraseña de nuevo).
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Ejercicio bonus: endpoint solo para administradores.
     */
    public function adminOnly()
    {
        $user = Auth::guard('api')->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'No tienes permisos de administrador',
            ], 403);
        }

        return response()->json([
            'message' => 'Bienvenido, admin',
            'users' => [
                ['id' => 1, 'name' => 'Juan Guzman', 'role' => 'admin'],
                ['id' => 2, 'name' => 'Usuario Normal', 'role' => 'user'],
            ],
        ]);
    }

    /**
     * Da forma estándar a la respuesta con el token JWT.
     */
    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}