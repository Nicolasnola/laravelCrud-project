@extends('layouts.main')

@section('title', 'HDC Events')

@section('content')


<div id="search-container" class="col-md-12">
    <h1>Busque um evento</h1>
    <form action="/" method="GET">
        <div class="flex">
            <div>
                <div class="form-group">
                    <input type="text" id="titleSearch" name="title_search" class="form-control" placeholder="Título">
                </div>
                <div class="form-group">

                    <input type="text" id="userSearch" name="user_search" class="form-control"
                        placeholder="Participante">
                </div>
            </div>
            <div>
                <div class="row g-2">
                    <div class="col-md-0">

                        <input type="text" id="itemSearch" name="item_search" class="form-control" placeholder="Item">

                    </div>
                    <div class="col-md">

                        <input type="text" id="dateSearch" name="date_search" class="form-control"
                            placeholder="Data. EX: 01/02/2023 - 04/05/2023">

                    </div>
                    <div class="col-md-0">

                        <button type="submit" class="btn btn-primary">
                            <ion-icon name="search"></ion-icon>
                        </button>

                    </div>
                </div>
            </div>
        </div>

    </form>
</div>




<div id="events-container" class="col-md-12">
    @if($titleSearch || $dateSearch || $itemSearch || $userSearch)
    <h2>Buscando por eventos com:</h2>
    <ul>
        @if($titleSearch)
        <li>Título: {{ $titleSearch }}</li>
        @endif
        @if($dateSearch)
        <li>Data: {{ $dateSearch }}</li>
        @endif
        @if($itemSearch)
        <li>Item: {{ $itemSearch }}</li>
        @endif
        @if($userSearch)
        <li>Participante: {{ $userSearch }}</li>
        @endif
    </ul>
    @else
    <h2>Próximos Eventos</h2>
    <p class="subtitle">Veja os eventos dos próximos dias</p>
    @endif
    <div id="cards-container" class="row">
        @foreach($events as $event)
        <div class="card col-md-3">
            <img src="/img/events/{{ $event->image }}" alt="{{ $event->title }}">
            <div class="card-body">
                <p class="card-date">{{ date('d/m/Y', strtotime($event->date)) }}</p>
                <h5 class="card-title">{{ $event->title }}</h5>
                <p class="card-participants"> {{ count($event->users) }} Participantes</p>
                <a href="/events/{{ $event->id }}" class="btn btn-primary">Saber mais</a>
            </div>
        </div>
        @endforeach
        @if(count($events) == 0 && ($titleSearch || $dateSearch || $itemSearch || $userSearch))
        <p>Não foi possível encontrar nenhum evento com os critérios de busca informados! <a href="/">Ver todos</a></p>
        @elseif(count($events) == 0)
        <p>Não há eventos disponíveis</p>
        @endif
    </div>
</div>
@endsection