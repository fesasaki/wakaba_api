<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserLoginRequest;
use App\Models\User;
use Exception;
use Faker\Core\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Autentica o usuário com username ou email e senha
     * Em caso de sucesso gera o token JWT.
     */
    public function login(Request $request)
    {
        try {
            // validate inputs
            $rules = [
                'email' => 'required',
                'password' => 'required|string'
            ];

            $request->validate($rules);

            // find user email in users table
            $user = User::where('email', $request->email)->first();

            // if user email found and password is correct
            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Personal Access Token')->plainTextToken;
                $response = ['user' => $user, 'token' => $token];
                return response()->json($response, 200);
            }

            $response = ['message' => 'Senha ou email incorreto'];
            
            return response()->json($response, 400);

        } catch (Exception $exc) {
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao solicitar login.'], 500);
        }
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

    
}
