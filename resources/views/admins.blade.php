@extends('layout')

@section('content')
	<!-- {{ $user }} -->
	{{ $user->adminJobs()->get() }}
@stop