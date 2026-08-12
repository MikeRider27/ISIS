@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <h1>Perfil</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-2">Nombre</dt>
                <dd class="col-sm-10">{{ auth()->user()->name }}</dd>

                <dt class="col-sm-2">Correo</dt>
                <dd class="col-sm-10">{{ auth()->user()->email }}</dd>
            </dl>
        </div>
    </div>
@stop
