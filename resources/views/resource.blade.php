@extends('layout')

@section('content')

@if ($resource->status == 1)
<p>{{ $resource->name }} </p>

@elseif ($resource->status == 0)
<p> This resource has been disabled.</p>

@endif

@stop