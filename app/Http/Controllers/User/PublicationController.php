<?php

namespace App\Http\Controllers\User;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\PublicationCategory;
use App\Models\PublicationReaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicationController extends Controller

{

    public function  store(Request $request)
    {
        DB::beginTransaction();

        try {

        $data = $request->json();

        $banner = $request->get('banner');
        $category = $request->get('category');
        $public = $request->get('public');

        $publication = new Publication($data->all());
        $publication->creator_id = Auth::id();

        $res = $publication->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar publicação.'], 500);
        }

        if ($banner) {
            ImageController::storeBanner($publication->uuid, $banner);
        }

        if (!$public) {
            foreach ($category as $cat) {
                if ($cat['checked']) {
                    $relation = new PublicationCategory();
                    $relation->publication_id = $publication->id;
                    $relation->category_id = $cat['id'];

                    $relation->save();
                }
            }
        }

        LogSystem::info('Cadastrou uma nova publicação: ' . $publication->title);

        DB::commit();
        return response()->json(['message' => 'Publicação feita com sucesso'], 201);

        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha salvar a publicação'], 500);
        }
    }

    public function index(Request $request)
    {

        $publications = Publication::orderBy('id', 'DESC')->with('creator')->get();

        foreach($publications as $pub) {
            $pub->banner = ImageController::bannerBase64($pub->uuid);
            $pub->creator_picture = ImageController::userBase64($pub->creator_id);
            $pub->category = $this->allCategory($pub->id);
            $pub->reaction = $this->getReaction($pub->id);
            $pub->canEdit = $this->checkSelfCreation($pub->creator_id);
        }

        return response()->json(
            [
                'data' => $publications,
            ],
            201
        );
    }

    static function allCategory($publication_id)
    {

        $categories = PublicationCategory::where('publication_id', $publication_id)->with('category')->get();

        $list = [];

        foreach($categories as $cat) {
            array_push($list, $cat->category);
        }

        return $list;
    }

    public function setReaction(Request $request )
    {   
        $publication = $request->get('publication');
        $reaction = $request->get('reaction');
        $user_id = Auth::id();

        switch($reaction) {
            case 'LIKE': {
                PublicationReaction::where('publication_id', $publication)->where('user_id', $user_id)->delete();
                $reaction = new PublicationReaction();
                $reaction->publication_id = $publication;
                $reaction->user_id = $user_id;
                $reaction->type = 'LIKE';
                $res = $reaction->save();
                break;
            }

            case 'UNLIKE': {
                PublicationReaction::where('publication_id', $publication)->where('user_id', $user_id)->delete();
                break;
            }
        }

        return response()->json(['message' => 'DONE'], 201);
    }

    public static function getReaction($id)
    {   
        $publication = PublicationReaction::where('publication_id', $id)->get();
        $user_id = Auth::id();
        $self = false;
        $size = sizeof($publication);
        $show = false;
        $template = 'A'; //Any template
        
        foreach($publication as $pub) {
            if($size > 0) {
                $show = true;
            }

            if($pub->user_id == $user_id) {
                $size = $size - 1;
                $self = true;

                if($size == 0) {
                    $template = 'C';  //When user is participating but alone
                } else {
                    $template = 'B'; //When user is participating with anothers
                }
            }
        }

        $reactions = [
            'like' => [
                'value' => $size,
                'self'  => $self,
                'show'  => $show,
                'template' => $template,
            ],
        ];

        return $reactions;
    }

    static function checkSelfCreation($user)
    {   
        $user_id = Auth::id();

        if($user == $user_id) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id) 
    {
        $user_id = Auth::id();
        $publication = Publication::find($id);

        if($user_id == $publication->creator_id) {
            $res = $publication->delete();

            if($res){
                return response()->json(['message' => 'Publicação excluída'], 201);
            }
        } else {
            return response()->json(['message' => 'Permission issue'], 501);
        }
    }
}
