@extends('layout')

@section('content')

	    <!-- Page content goes here -->
	    <div id='main'>
		    <div id="search-results">
		    	@if ($result->count() == 0)
		    		<span>Your search found no results.</span>
		    	@else
		    		{!! $result !!}
		    	@endif
		    </div>
		</div>
@stop