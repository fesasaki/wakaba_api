<?php

namespace App\Http\Controllers\User;

use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller

{

    public function  store(Request $request)
    {

        $data = $request->json();

        $category = new Category($data->all());

        $category->description = 'Sem descrição';

        $res = $category->save();

        if (!$res) {
            return response()->json(['message' => 'Falha ao cadastrar categoria.'], 500);
        }

        LogSystem::info('Cadastrou uma nova categoria: ' . $category->name);

        return response()->json(['message' => 'Categoria cadastrada com sucesso!'], 201);
    }

    public function index(Request $request)
    {

        $categories = Category::orderBy('name', 'ASC')->get();

        if (!$categories) {
            return response()->json(['message' => 'Categorias não encontradas.'], 500);
        }

        foreach($categories as $cat) {
            $count = UserCategory::where('category_id', $cat->id)->get();
            $cat->count = sizeof($count);
        }

        return response()->json(
            [
                'data' => $categories,
            ],
            201
        );
    }

    public function delete($id)
    {

        try {

            $category = Category::find($id);

            $res = $category->delete();

            if ($res) {

                LogSystem::info('Deletou uma categoria: ' . $category->name);

                return response()->json(['message' => 'Categoria deletada com suscesso.'], 201);
            }

        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao deletar categoria.'], 500);
        }
    }

    public function setPosition(Request $request)
    {

        DB::beginTransaction();

        try {

            $user_id = $request->get('user');
            $category_id = $request->get('category');

            $repeat = UserCategory::where('user_id', $user_id)->where('category_id', $category_id)->first();

            if($repeat) {
                return response()->json(['message' => 'Categoria já atribuída a este usuário.'], 500);
            }


            $new = new UserCategory();

            $new->user_id = $user_id;
            $new->category_id = $category_id;

            $res = $new->save();

            if ($res) {

                LogSystem::info('Atribuiu uma categoria');

                DB::commit();

                return response()->json(['message' => 'Categoria atribuída com sucesso.'], 201);
            }

        } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao atribuir categoria.'], 500);
        }
    }

    public function unsetPosition(Request $request)
    {

        DB::beginTransaction();

        // try {

            $user_id = $request->get('user');
            $category_id = $request->get('category');

            $res = UserCategory::where('user_id', $user_id)->where('category_id', $category_id)->delete();

            if ($res) {

                LogSystem::info('Desatribuiu uma categoria');

                DB::commit();

                return response()->json(['message' => 'Categoria desatribuída com sucesso.'], 201);
            }

        /* } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao atribuir categoria.'], 500);
        } */
    }


}
