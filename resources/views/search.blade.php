@extends('layout')

@section('content')

		<!-- Page content goes here -->
		<div id='main'>
			<div id="search-results">
				@if ($results->count() == 0)
					<span>Your search found no results.</span>
				@else
					@each('search-result', $results, 'resource')
				@endif
			</div>
		</div>
@stop