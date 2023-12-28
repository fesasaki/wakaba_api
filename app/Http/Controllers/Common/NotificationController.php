<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;


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

    static public function reCreate(Request $request)
    {   
        $email = $request->get('email');
        $randomCode = rand(1000, 9999); 

        $user = User::where('email', $email)->first();

        $notification = Notification::where('user_id', $user->id)->get();

        foreach($notification as $notif) {
            $notif->expired = true;
            $notif->update();
        }

        $notification = new Notification();
        $notification->user_id = $user->id;
        // $notification->code = $randomCode;
        $notification->code = 1111;
        $notification->expired = false;

        $notification->save();

        return response()->json(['message' => 'Código reenviado para ' . $email], 200);
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

            $user->started = true;
            $user->update();

            return response()->json(['message' => 'Validação feita com sucesso'], 200);
        } else {
            return response()->json(['message' => 'Código incorreto'], 400);
        }
    }
}
