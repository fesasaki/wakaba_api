<?php

namespace App\Http\Controllers\User;

use App\Enums\UserType;
use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\UserPosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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


    public function update(Request $request, $id)
    {

        $rules = [
            'email' => Rule::unique('users'),
        ];

        $request->validate($rules);

        $data = $request->json();

        $user = User::find($id);

        $res = $user->update($data->all());

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar usuário.'], 500);
        }

        LogSystem::info('Atualizou um usuário: ' . $user->name);

        return response()->json(['message' => 'Usuário atualizado'], 201);
    }


    public function detail(Request $request, $id)
    {

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado.'], 500);
        }

        if ($user->birthday) {
            $birthYear = date('Y', strtotime($user->birthday));
            $todayYear = date('Y', strtotime(Carbon::now()));
            $user->birthday = date('d/m/Y', strtotime($user->birthday));
            $user->age = $todayYear - $birthYear;
        } else {
            $user->age = null;
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

        foreach ($users as $user) {

            $categories = UserCategory::where('user_id', $user->id)->with('category')->orderBy('category_id', 'ASC')->get();
            $user->category = $categories;
            $user->picture = ImageController::userBase64($user->id);
            $user->position = PositionController::getPositionByUser($user->id);

            if ($category) {
                foreach ($categories as $cat) {
                    if ($cat->category_id == $category) {
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

    public function setAdmin(Request $request)
    {

        $user_id = $request->get('user');
        $admin = $request->get('admin');

        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 500);
        }

        $user->user_type = $admin;
        $user->update();

        LogSystem::info('Atualizou o tipo de usuário: ' . $user->name);

        return response()->json(['message' => 'Usuário atualizado'], 201);
    }

    public function inactive(Request $request)
    {
        $user_id = Auth::id();
        $user = User::find($user_id);
        $target_id = $request->get('user');
        $target = User::find($target_id);

        if($user->user_type > UserType::USER) {
            $target->active = false;
            $target->update();

            return response()->json(['message' => 'Usuário inativado'], 201);
        } else {
            return response()->json(['message' => 'Ação não autorizada'], 501);
        }
    }
}
