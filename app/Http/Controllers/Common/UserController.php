<?php

namespace App\Http\Controllers\Common;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function store(UserRequest $request)
    {   
        
        $data = $request->json();

        $user = new User($data->all());

        $user->password = Str::random(8);

        $res = $user->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar usuário.'], 500);
        }

        // $this->mailSend($user);
        
        LogSystem::info('Cadastrou um novo usuário: ' . $user->name);

        return response()->json(['message' => 'Usuário cadastrado com sucesso!'], 201);
    }

    public function index()
    {
        return 'YES';
    }
}
