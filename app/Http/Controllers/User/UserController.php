<?php

namespace App\Http\Controllers\User;

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

    public function detail(Request $request, $id)
    {   
        
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 500);
        }
        
        return response()->json(
            [
                'data' => $user,
            ], 201);
    }

    public function index(Request $request)
    {   
       
        $tag = $request->get('tag');
        
        $users = User::where('id', '<>', 1)->where('active', true)->orderBy('name', 'ASC')->get();
        
        if (!$users) {
            return response()->json(['message' => 'Usuários não encontrado.'], 500);
        }
        
        return response()->json(
            [
                'data' => $users,
            ], 201);
    }

}
