@extends('adminlte::page')

@section('title', 'Home')

@section('content_header')
    <h1>Home</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="mb-0">Bienvenido/a, {{ auth()->user()->name }}.</p>
        </div>
    </div>
@stop
