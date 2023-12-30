<?php

namespace App\Http\Controllers\User;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\UserPosition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller

{

    public function  store(Request $request)
    {

        $data = $request->json();

        $position = new Position($data->all());

        $position->description = 'Sem descrição';

        $res = $position->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar usuário.'], 500);
        }

        LogSystem::info('Cadastrou um novo cargo: ' . $position->name);

        return response()->json(['message' => 'Cargo cadastrado com sucesso!'], 201);
    }

    public function index(Request $request)
    {

        $positions = Position::orderBy('name', 'ASC')->get();

        if (!$positions) {
            return response()->json(['message' => 'Usuários não encontrado.'], 500);
        }

        foreach($positions as $pos) {
            $count = UserPosition::where('position_id', $pos->id)->get();
            $pos->count = sizeof($count);
        }

        return response()->json(
            [
                'data' => $positions,
            ],
            201
        );
    }

    public function delete($id)
    {

        try {

            $position = Position::find($id);

            $res = $position->delete();

            if ($res) {

                LogSystem::info('Deletou um cargo: ' . $position->name);

                return response()->json(['message' => 'Cargo deletado com suscesso.'], 201);
            }

        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao deletar cargo.'], 500);
        }
    }

    public function setPosition(Request $request)
    {

        DB::beginTransaction();

        try {

            $user_id = $request->get('user');
            $position_id = $request->get('position');

            UserPosition::where('user_id', $user_id)->delete();

            $new = new UserPosition();

            $new->user_id = $user_id;
            $new->position_id = $position_id;

            $res = $new->save();

            if ($res) {

                LogSystem::info('Atribuiu um cargo');

                DB::commit();

                return response()->json(['message' => 'Cargo atribuído com sucesso.'], 201);
            }

        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao atribuir cargo.'], 500);
        }
    }

    static function getPositionByUser($id)
    {
        $relation = UserPosition::where('user_id', $id)->with('position')->first();

        if($relation) {
            return $relation->position;
        } else {
            return null;
        }
    }

    public function detail($id)
    {
        $position = Position::find($id);

        if(!$position) {
            return response()->json(['message' => 'Cargo não encontrado'], 401);
        }

        return response()->json($position, 201);
    }

    public function  update(Request $request, $id)
    {   
        $name = $request->get('name');
        $position = Position::find($id);

        if(!$position) {
            return response()->json(['message' => 'Cargo não encontrado'], 401);
        }

        $position->name = $name;
        $position->update();

        return response()->json(['message' => 'Cargo atualizado'], 201);
    }
}
