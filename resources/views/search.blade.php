@push('styles')
	<link rel="stylesheet" type="text/css" href="{{ asset('css/search.css') }}">
@endpush

@extends('layout')

@section('content')

	    <!-- Page content goes here -->
	    <div id="main">
	    	@if ($result->count() == 0)
	    		<span>Your search found no results.</span>
	    	@else
	    		{!! $result !!}
	    	@endif
	    </div>

@stop