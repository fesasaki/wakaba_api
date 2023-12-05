<?php

namespace App\Http\Controllers\Common;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class NotificationController extends Controller
{
    static public function create($user_id)
    {
        $randomCode = rand(1000, 9999);

        $notification = new Notification();
        $notification->user_id = $user_id;
        $notification->code = $randomCode;
        $notification->expired = false;

        $notification->save();

        return $notification;
    }


    public function checkCode(Request $request)
    {
        $email = $request->get('email');
        $code_1 = $request->get('number1');
        $code_2 = $request->get('number2');
        $code_3 = $request->get('number3');
        $code_4 = $request->get('number4');

        $code = $code_1 . $code_2 . $code_3 . $code_4;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'e-mail não encontrado'], 400);
        }

        $notification = Notification::where('user_id', $user->id)->where('code', $code)->where('expired', false)->first();

        if ($notification) {

            $notification->expired = true;
            $notification->update();

            return response()->json(['message' => 'Validação feita com sucesso'], 200);
        } else {
            return response()->json(['message' => 'Código incorreto'], 400);
        }
    }
}