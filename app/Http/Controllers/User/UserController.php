<?php

namespace App\Http\Controllers\User;

use App\Enums\UserType;
use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nette\Utils\ImageColor;

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

        $position = UserPosition::where('user_id', $id)->with('position')->first();

        if ($position) {
            $user->position = $position->position;
        } else {
            $user->position = false;
        }

        $categories = UserCategory::where('user_id', $id)->with('category')->get();

        $list = [];

        foreach ($categories as $cat) {
            array_push($list, $cat->category);
        }

        $user->category = $list;

        $user->picture = ImageController::userBase64(($id));

        return response()->json(
            [
                'data' => $user,
            ],
            201
        );
    }

    public function index(Request $request)
    {

        $category = $request->get('category');
        $list = [];
        $users = User::where('id', '<>', 1)->where('active', true)->orderBy('name', 'ASC')->get();

        if (!$users) {
            return response()->json(['message' => 'Usuários não encontrado.'], 500);
        }

        foreach($users as $user) {

            $categories = UserCategory::where('user_id', $user->id)->with('category')->orderBy('category_id', 'ASC')->get();
            $user->category = $categories;
            $user->picture = ImageController::userBase64($user->id);
            $user->position = PositionController::getPositionByUser($user->id);

            if($category) {
                foreach($categories as $cat) {
                    if($cat->category_id == $category) {
                        array_push($list, $user);
                    }
                }
            } else {
                array_push($list, $user);
            }
            
        }

        return response()->json(
            [
                'data' => $list,
            ],
            201
        );
    }

    public function credential()
    {

        $user_id = Auth::id();

        $user = User::find($user_id);

        $adminPass = false;

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 500);
        }

        if ($user->user_type >= UserType::ADMIN) {
            $adminPass = true;
        }

        return response()->json(
            [
                'admin_pass' => $adminPass,
            ],
            201
        );
    }

}
