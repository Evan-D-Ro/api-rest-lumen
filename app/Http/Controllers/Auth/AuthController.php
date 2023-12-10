<?php
namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Login
     *
     * @return void
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'senha' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Campos inválidos'], 400);
        }
        // Tenta encontrar o usuário pelo e-mail
        $user = User::where('email', $request->input('email'))->first();

        // Verifica se o usuário existe e se a senha está correta
        if ($user && $user->senha == $request->input('senha')) {
            // Autenticação manual
            Auth::login($user);

            // Gera e retorna o token de acesso (opcional)
            $token = auth()->login($user);
            return $this->respondWithToken($token);
        }

        // Caso as credenciais estejam incorretas
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * refresh
     *
     * @return void
     */
    public function refresh()
    {
        $token = auth()->refresh();
        return $this->respondWithToken($token);
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'Logout with success!'
        ], 401);
    }

    /**
     * Undocumented function
     *
     * @param [type] $token
     * @return void
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 // default 1 hour
        ]);
    }
}