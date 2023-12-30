<?php

namespace App\Http\Controllers\User;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    static function userBase64($id) {


        $DS = DIRECTORY_SEPARATOR;

        $user = User::find($id);

        $folder = 'picture/user/' . $user->uuid . $DS . $user->uuid . '.png';

        $photo = Storage::disk('public')->get($folder);

        if($photo) {
            $base64 = base64_encode($photo);
        } else {
            $default = Storage::disk('public')->get('model/default.png');
            $base64 = base64_encode($default);
        }

        return $base64;

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

        return response()->json(['message' => 'Foto de perfil atualizada com sucesso'], 201);
    }

    static function storeBanner($uuid, $base64)
    {

        $str = substr($base64, strpos($base64, ",")+1);

        $photo = base64_decode(($str));

        $DS = DIRECTORY_SEPARATOR;

        $folder = 'publication/' . $uuid . $DS . $uuid . '.jpg';

        Storage::disk('public')->put($folder, $photo);

        return response()->json(['message' => 'Foto de perfil atualizada com sucesso'], 201);
    }

    static function bannerBase64($uuid) {


        $DS = DIRECTORY_SEPARATOR;

        $folder = 'publication/' . $uuid . $DS . $uuid . '.jpg';

        $photo = Storage::disk('public')->get($folder);

        if($photo) {
            $base64 = base64_encode($photo);
        } else {
            $default = Storage::disk('public')->get('model/default.png');
            $base64 = base64_encode($default);
        }

        return $base64;

    }


    static function storeBannerEvent($uuid, $base64)
    {

        $str = substr($base64, strpos($base64, ",")+1);

        $photo = base64_decode(($str));

        $DS = DIRECTORY_SEPARATOR;

        $folder = 'event/' . $uuid . $DS . $uuid . '.jpg';

        Storage::disk('public')->put($folder, $photo);

        return response()->json(['message' => 'Foto de perfil atualizada com sucesso'], 201);
    }

    static function bannerBase64Event($uuid) {


        $DS = DIRECTORY_SEPARATOR;

        $folder = 'event/' . $uuid . $DS . $uuid . '.jpg';

        $photo = Storage::disk('public')->get($folder);

        if($photo) {
            $base64 = base64_encode($photo);
        } else {
            $base64 = null;
        }

        return $base64;

    }

}
