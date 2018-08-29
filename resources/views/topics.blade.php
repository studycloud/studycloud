@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/_tree.css') }}">
@endpush


@extends('layout')

@section('content')

<div id="main">
	<div id="topic-tree">
	</div>
</div>

<script src="{{ asset('js/topics.js') }}"></script>

@stop