<?php

namespace App\Http\Controllers\User;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class ImageController extends Controller

{   

    public function  userPicture($id) {

        $DS = DIRECTORY_SEPARATOR;

        $user = User::find($id);

        $directory = storage_path('app/public/picture/user/' . $user->uuid . $DS . $user->uuid . '.png')  ;

        $default_path = storage_path('app/public/model/default.png');

        if(is_file($directory)) {
            return response()->download($directory, $user->name);
        }

        return response()->download($default_path, 'default-photo');


        
    }

}
