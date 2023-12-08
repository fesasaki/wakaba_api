<?php

namespace App\Http\Controllers\Common;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserLoginRequest;
use App\Models\User;
use Exception;
use Faker\Core\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    /**
     * Autentica o usuário com username ou email e senha
     * Em caso de sucesso gera o token JWT.
     */
    public function login(Request $request)
    {
        // try {
        // validate inputs
        $rules = [
            'email' => 'required',
            'password' => 'required|string'
        ];

        $request->validate($rules);
        
        $email = $request->get('email');
        $password = $request->get('password');

        // find user email in users table
        $user = User::where('email', $email)->first();
        
        if (!$user) {

            LogSystem::warn("Falha na tentativa de login, usuário $email . Usuário não encontrado.");
            
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        
        if (!Hash::check($password, $user->password)) {
            
            LogSystem::warn("Falha na tentativa de login, usuário $email. Senha Inválida.");
            
            return response()->json(['message' => 'Senha incorreta'], 401);
        }
        
        if (!$user->active) {

            LogSystem::warn("Falha na tentativa de login, usuário $email. Usuário desativado.");

            return response()->json(['message' => 'Este usuário foi desativado'], 401);
        };

        $payload = [
            'user_data' => $user,
        ];
        
        $token = Auth::claims($payload)->login($user);

        LogSystem::info('Entrou no sistema');

        return $this->_respondWithToken($token);

        /* } catch (Exception $exc) {
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao solicitar login.'], 500);
        } */
    }

    public function checkUser(Request $request)
    {
        try {
            // validate inputs
            $rules = [
                'email' => 'required',
            ];

            $request->validate($rules);

            $user = User::where('email', $request->email)->where('active', true)->first();

            if ($user) {

                $response = [
                    'started' => $user->started,
                    'approved' => $user->approved,
                ];

                return response()->json($response, 200);
            }

            $response = ['message' => 'E-mail não encontrado'];

            return response()->json($response, 400);
        } catch (Exception $exc) {
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao verificar usuário.'], 500);
        }
    }

    public function recoverPassword(Request $request)
    {
        $password = $request->get('password');
        $confirm = $request->get('confirm');
        $email = $request->get('email');

        if ($password != $confirm) {
            return response()->json(['message' => 'Senhas não coincidem'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 400);
        }

        $user->password = $password;

        $res = $user->update();

        if ($res) {
            return response()->json(['data' => $user], 200);
        }

        return response()->json(['message' => 'Falha ao atualizar senha'], 400);
    }

    protected function _respondWithToken($token)
    {
        $user = Auth::user();

        $name = explode(' ', trim($user->name));
        $len = count($name);

        if ($len > 1) {
            $name = $name[0] . ' ' . $name[($len - 1)];
        } else {
            $name = $name[0];
        }

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Carbon::now()->addMinutes(JWTAuth::factory()->getTTL())->toDateTimeString()
        ];

        return response()->json($data, 200);
    }
}
