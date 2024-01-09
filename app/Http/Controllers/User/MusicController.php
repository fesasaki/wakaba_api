<?php

namespace App\Http\Controllers\User;

use App\Enums\EventStatus;
use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Music;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller

{

    public function  store(Request $request)
    {
        DB::beginTransaction();

        try {

            $title = $request->get('title');
            $DS = DIRECTORY_SEPARATOR;

            $music = new Music();
            $music->title = $title;
            $music->creator_id = Auth::id();

            $res = $music->save();

            if (!$res) {
                return response()->json(['message' => 'Falha ao criar música.'], 500);
            }

            $folder = storage_path('app') . $DS . 'public/music/' . $DS . $music->uuid;

            $document = $request->file('document');

            $document->move($folder, $music->uuid . '.pdf');

            LogSystem::info('Salvou uma nova música' . $music->title);

            DB::commit();
            return response()->json(['message' => 'Música salva com sucesso!'], 201);
        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao salvar música.'], 500);
        }
    }

    public function index(Request $request)
    {

        $musics = Music::orderBy('title', 'ASC')->get();
        $DS = DIRECTORY_SEPARATOR;
        

        if (!$musics) {
            return response()->json(['message' => 'Categorias não encontradas.'], 500);
        }

        foreach ($musics as $music) {

            $folder = 'music/' . $music->uuid . $DS . $music->uuid . '.pdf';
            $document = Storage::disk('public')->get($folder);
            $base64 = $base64 = base64_encode($document);

            $music->document = $base64;
        }

        return response()->json(
            [
                'data' => $musics,
            ],
            201
        );
    }
}
