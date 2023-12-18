<?php

namespace App\Http\Controllers\Common;

use App\Enums\RequistionStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RequisitionController extends Controller
{
    public function createRequisiton(Request $request)
    {
        DB::beginTransaction();

        /* try { */

        $rules = [
            'email' => Rule::unique('users'),
        ];

        $request->validate($rules);

        $data = $request->json();
        $birthday = $request->get('birthday');

        $newUser = new User($data->all());
        $newUser->password = Str::random(8);
        $newUser->active = false;
        $newUser->started = false;
        $newUser->approved = false;
        $newUser->birthday = date('Y-d-m', strtotime($birthday));
        $newUser->user_type = UserType::USER;
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

    public function list(Request $request)
    {
        $status = $request->get('status');

        $where[] = ['status', $status];

        $requisitions = Requisition::where($where)->with('user', 'approver')->orderBy('id', 'DESC')->get();

        return response()->json([
            'data' => $requisitions
        ], 200);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {

            $id = $request->get('requisition_id');
            $decision = $request->get('decision');
            $text = '';
            $notification = false;

            $requisition = Requisition::find($id);
            $user = User::find($requisition->user_id);

            if ($decision) {
                $text = 'aprovado';
                $requisition->status = RequistionStatus::APPROVED;
                $requisition->approver_id = Auth::id();

                $user->approved = true;
                $user->active = true;
                $user->started = false;

                $notification = true;
            } else {
                $text = 'reprovado';
                $requisition->status = RequistionStatus::REPROVED;
                $requisition->approver_id = Auth::id();

                $user->approved = false;
                $user->active = false;
                $user->started = false;

                $notification = false;
            }

            $requisition->update();
            $user->update();

            if ($notification) {
                NotificationController::create($user->id);
            }

            DB::commit();

            return response()->json([
                'message' => 'Solicitação ' . $text . ' com sucesso'
            ], 200);
        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao solicitar usuário.'], 500);
        }
    }
}
