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
    static public function create($user)
    {    
        $randomCode = rand(1000, 9999);

        $notification = new Notification();
        $notification->user_id = 2;
        $notification->code = $randomCode;

        $notification->save();

        return $notification;

    }

}
