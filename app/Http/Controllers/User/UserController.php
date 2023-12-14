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

        return response()->json(
            [
                'data' => $user,
            ],
            201
        );
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

    public function storePhoto(Request $request)
    {

        $user_id = $request->get('user');

        $base64 = $request->get('photo');

        $str = substr($base64, strpos($base64, ",")+1);

        $photo = base64_decode(($str));

        $DS = DIRECTORY_SEPARATOR;

        $user = User::find($user_id);

        $folder = 'picture/user/' . $user->uuid . $DS . $user->uuid . '.png';

        Storage::disk('public')->put($folder, $photo);
    }
}
