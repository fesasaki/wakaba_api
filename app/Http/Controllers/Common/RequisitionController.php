<?php

namespace App\Http\Controllers\Common;

use App\Enums\RequistionStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class RequisitionController extends Controller
{
    public function createRequisiton(Request $request)
    {
        DB::beginTransaction();

        /* try { */

        $data = $request->json();

        $newUser = new User($data->all());
        $newUser->password = Str::random(8);
        $newUser->active = false;
        $newUser->user_type = UserType::UNKNOW;
        $newUser->username = Str::random(12);

        $res = $newUser->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar usuário.'], 500);
        }

        $newRequisition = new Requisition();
        $newRequisition->user_id = $newUser->id;
        $newRequisition->status = RequistionStatus::WAITING;
        $newRequisition->approver_id = null;

        $res = $newRequisition->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar usuário.'], 500);
        }

        DB::commit();

        return response()->json([
            'message' => 'Criação de usuário solicitado com sucesso.',
            'data'    => $newRequisition,
        ], 200);

        /* }
        catch(Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao solicitar usuário.'], 500);
        }    */
    }

    public function list()
    {
        $requisitions = Requisition::with('user')->get();

        return response()->json([
            'data' => $requisitions 
        ], 200);
    }
}
