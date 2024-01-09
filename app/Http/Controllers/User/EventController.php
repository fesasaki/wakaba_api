<?php

namespace App\Http\Controllers\User;

use App\Enums\EventStatus;
use App\Facades\LogSystem;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventSubscription;
use App\Models\Position;
use App\Models\UserCategory;
use App\Models\UserPosition;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller

{

    public function  store(Request $request)
    {
        DB::beginTransaction();

        // try {

            $data = $request->json();
            $public = $request->get('public');
            $category = $request->get('category');
            $banner = $request->get('banner');

            $event = new Event($data->all());
            $event->creator_id = Auth::id();
            $event->status = EventStatus::ONGOING;
            $event->date = Carbon::now();

            $res = $event->save();

            if (!$res) {
                return response()->json(['message' => 'Falha ao criar evento.'], 500);
            }

            if (!$public) {
                foreach ($category as $cat) {
                    if ($cat['checked']) {
                        $relation = new EventCategory();
                        $relation->event_id = $event->id;
                        $relation->category_id = $cat['id'];

                        $relation->save();
                    }
                }
            }

            if ($banner) {
                ImageController::storeBannerEvent($event->uuid, $banner);
            }

            LogSystem::info('Criou um novo evento: ' . $event->title);

            DB::commit();
            return response()->json(['message' => 'Evento criado com sucesso!'], 201);
        /* } catch (Exception $exc) {
            DB::rollback();
            Log::error($exc->getMessage());
            return response()->json(['message' => 'Falha ao criar evento.'], 500);
        } */
    }


    public function index(Request $request)
    {

        $year = $request->get('year');
        $total = 0;
        $list = [];

        $months =  [
            ['value' => '01', 'name' => 'Janeiro', 'events' => []],
            ['value' => '02', 'name' => 'Fevereiro', 'events' => []],
            ['value' => '03', 'name' => 'Março', 'events' => []],
            ['value' => '04', 'name' => 'Abril', 'events' => []],
            ['value' => '05', 'name' => 'Maio', 'events' => []],
            ['value' => '06', 'name' => 'Junho', 'events' => []],
            ['value' => '07', 'name' => 'Julho', 'events' => []],
            ['value' => '08', 'name' => 'Agosto', 'events' => []],
            ['value' => '09', 'name' => 'Setembro', 'events' => []],
            ['value' => '10', 'name' => 'Outubro', 'events' => []],
            ['value' => '11', 'name' => 'Novembro', 'events' => []],
            ['value' => '12', 'name' => 'Dezembro', 'events' => []],
        ];


        foreach($months as $m) {
            
            $events = Event::whereMonth('date_initial', $m['value'])->whereYear('date', $year)->orderBy('date', 'ASC')->with('creator')->get();

            foreach($events as $evt) {
                $evt->subscriber = $this->countSubscriber($evt->id);
                $total++;
            }

            $m['events'] = $events;
            
            array_push($list, $m);
        }

        return response()->json(
            [
                'data' => $list,
                'total' => $total
            ],
            201
        );
    }

    public function index2(Request $request)
    {

        $events = Event::orderBy('id', 'DESC')->with('creator')->get();
        $total_ongoing = 0;
        $list = [];

        foreach ($events as $evt) {

            if ($evt->public) {
                $inclusion = true;
            } else {
                $inclusion = $this->checkInclusion($evt->id);
            }

            if ($inclusion) {
                $evt->banner = ImageController::bannerBase64Event($evt->uuid);
                $evt->creator_picture = ImageController::userBase64($evt->creator_id);
                $evt->category = $this->allCategory($evt->id);
                $evt->subscriber = $this->countSubscriber($evt->id);
                $evt->waiting = $this->checkWaiting($evt->id);
                $evt->canEdit = $this->checkSelfCreation($evt->creator_id);

                if ($evt->status == EventStatus::ONGOING) {
                    $total_ongoing++;
                }

                array_push($list, $evt);
            }
        }

        return response()->json(
            [
                'data' => $list,
                'total_ongoing' => $total_ongoing
            ],
            201
        );
    }

    static function allCategory($event_id)
    {

        $categories = EventCategory::where('event_id', $event_id)->with('category')->get();

        $list = [];

        foreach ($categories as $cat) {
            array_push($list, $cat->category);
        }

        return $list;
    }

    public function subscribe(Request $request)
    {

        $user_id = Auth::id();
        $event_id = $request->get('event');

        $event = Event::find($event_id);

        if($event->status != EventStatus::ONGOING) {
            return response()->json(['message' => 'Inscrição já está encerrada'], 401);
        }


        EventSubscription::where('event_id', $event_id)->where('user_id', $user_id)->delete();


        $subscriber = new EventSubscription();
        $subscriber->event_id = $event_id;
        $subscriber->user_id = $user_id;
        $subscriber->save();

        return response()->json(['message' => 'Inscrição feita com sucesso'], 201);
    }

    public function cancelSubscribe(Request $request)
    {

        $user_id = Auth::id();
        $event_id = $request->get('event');

        $event = Event::find($event_id);

        if($event->status != EventStatus::ONGOING) {
            return response()->json(['message' => 'Inscrição já está encerrada'], 401);
        }

        EventSubscription::where('event_id', $event_id)->where('user_id', $user_id)->delete();

        return response()->json(['message' => 'Inscrição cancelada', 'id' => $event_id], 201);
    }

    static function allSubscriber($event_id)
    {

        $subscribers = EventSubscription::where('event_id', $event_id)->with('user')->get();

        $list = [];

        foreach ($subscribers as $sub) {
            array_push($list, $sub->user);
        }

        return $list;
    }

    static function countSubscriber($event_id)
    {

        $subscribers = EventSubscription::where('event_id', $event_id)->with('user')->get();

        $list = [];

        foreach ($subscribers as $sub) {
            array_push($list, $sub->user);
        }

        return sizeof($list);
    }

    public function checkInclusion($event_id)
    {
        $user_id = Auth::id();
        $categories = UserCategory::where('user_id', $user_id)->get();
        $event_categories = EventCategory::where('event_id', $event_id)->get();
        $list = [];

        foreach ($categories as $cat) {
            array_push($list, $cat->category_id);
        }

        foreach ($event_categories as $category) {
            if (in_array($category->category_id, $list)) {
                return true;
            }
        }

        return false;
    }

    public function checkWaiting($event_id)
    {
        $user_id = Auth::id();
        $event_categories = EventSubscription::where('event_id', $event_id)->get();

        foreach ($event_categories as $category) {
            if ($category->user_id == $user_id) {
                return false;
            }
        }

        return true;
    }

    public function detail($id)
    {   
        $user_id = Auth::id();
        $event = Event::with('creator')->find($id);
        $list = [];
        $self = false;

        if (!$event) {
            return response()->json(['message' => 'Evento não encontrado'], 401);
        }

        $event->banner = ImageController::bannerBase64Event($event->uuid);
        $event->category = $this->allCategory($event->id);
        $event->creator_picture = ImageController::userBase64($event->creator_id);
        $event->canEdit = $this->checkSelfCreation($event->creator_id);

        if($event->status == EventStatus::ONGOING) {
            $event->open = true;
        } else {
            $event->open = false;
        }

        $subscriber = EventSubscription::with('user')->where('event_id', $id)->get();

        foreach($subscriber as $sub) {

            $user_picture = ImageController::userBase64($sub->user_id);
            $sub->user->picture = $user_picture;
            $sub->user->subscribed_at = $sub->created_at;

            array_push($list, $sub->user);

            if($sub->user_id == $user_id) {
                $self = true;
            }
        }

        return response()->json([
            'detail' => $event,
            'subscriber' => $list,
            'self' => $self,
        ]);
    }

    public function delete($id) 
    {
        $user_id = Auth::id();
        $event = Event::find($id);

        if($user_id == $event->creator_id) {
            $res = $event->delete();

            if($res){
                return response()->json(['message' => 'Evento excluído'], 201);
            }
        } else {
            return response()->json(['message' => 'Permission issue'], 501);
        }
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

    public function updateStatus(Request $request)
    {
        $event_id = $request->get('event');
        $status = $request->get('status');

        $event = Event::find($event_id);

        if(!$event){
            return response()->json(['message' => 'Evento não encontrado'], 401);
        }

        $event->status = $status;
        $res = $event->update();

        if($res){
            return response()->json(['message' => 'Evento atualizado'], 201);
        }
    }
}
