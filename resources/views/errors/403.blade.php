@extends('layouts.main')

@section('title', 'Acceso denegado')

@section('content_header')
    <h1><i class="fas fa-ban"></i> Acceso denegado</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <p class="mb-2">No tienes permisos para acceder a esta sección.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-home"></i> Volver al Dashboard
        </a>
    </div>
</div>
@stop
