<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
{
    $search = request('search');

    if ($search) {
        $startDate = null;
        $endDate = null;

        // Verifica se o parâmetro de busca é uma data válida ou intervalo de datas
        if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}( - \d{1,2}\/\d{1,2}\/\d{4})?$/", $search)) {
            $searchDates = explode(' - ', $search);
            $startDate = Carbon::createFromFormat('d/m/Y', $searchDates[0])->format('Y-m-d');
            if (count($searchDates) > 1) {
                $endDate = Carbon::createFromFormat('d/m/Y', $searchDates[1])->addDays(1)->format('Y-m-d');
            } else {
                $endDate = Carbon::createFromFormat('d/m/Y', $searchDates[0])->addDays(1)->format('Y-m-d');
            }
        }

        // Se a data for válida, adiciona a condição de busca por intervalo de datas
        if ($startDate && $endDate) {
            $events = Event::whereBetween('date', [$startDate, $endDate])
                ->orWhere('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhereRaw('JSON_CONTAINS(items, \'["' . $search . '"]\')')
                ->orWhereHas('users', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->get();
        } else {
            $events = Event::where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhereRaw('JSON_CONTAINS(items, \'["' . $search . '"]\')')
                ->orWhereHas('users', function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->get();
        }
    } else {
        $events = Event::all();
    }

    return view('welcome', ['events' => $events, 'search' => $search]);
}
    

    public function create() {
        return view('events.create');
    }

    public function store(Request $request) {

        $event = new Event;

        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $event->image = $imageName;

        }

        $user = auth()->user();
        $event->user_id = $user->id;

        $event->save();

        return redirect('/')->with('msg', 'Evento criado com sucesso!');

    }

    public function show($id) {

        $event = Event::findOrFail($id);

        $user = auth()->user();
        $hasUserJoined = false;

        if($user) {

            $userEvents = $user->eventsAsParticipant->toArray();

            foreach($userEvents as $userEvent) {
                if($userEvent['id'] == $id) {
                    $hasUserJoined = true;
                }
            }

        }

        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', ['event' => $event, 'eventOwner' => $eventOwner, 'hasUserJoined' => $hasUserJoined]);
        
    }

    public function dashboard() {

        $user = auth()->user();

        $events = $user->events;

        $eventsAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard', 
            ['events' => $events, 'eventsasparticipant' => $eventsAsParticipant]
        );

    }

    public function destroy($id) {

        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg', 'Evento excluído com sucesso!');

    }

    public function edit($id) {

        $user = auth()->user();

        $event = Event::findOrFail($id);

        if($user->id != $event->user_id) {
            return redirect('/dashboard');
        }

        return view('events.edit', ['event' => $event]);

    }

    public function update(Request $request) {

        $data = $request->all();

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $data['image'] = $imageName;

        }

        Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg', 'Evento editado com sucesso!');

    }

    public function joinEvent($id) {

        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua presença está confirmada no evento ' . $event->title);

    }

    public function leaveEvent($id) {

        $user = auth()->user();

        $user->eventsAsParticipant()->detach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Você saiu com sucesso do evento: ' . $event->title);

    }
    public function removeUser(Event $event, User $user){

        $event->users()->detach($user);
    
        
    
        return redirect("/events/{$event->id}")->with('msg', 'Participante saiu com sucesso do evento');
    
    }



}