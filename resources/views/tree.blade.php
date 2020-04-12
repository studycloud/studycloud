@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/_tree.css') }}">
@endpush


@extends('layout')

@section('content')

<div id="main">
	<div id="topic-tree">
	</div>
</div>

<script>
	@if(isset($action) && !is_null($action))
		{{-- it's either create or edit or show --}}
		@if(isset($node) && is_null($node))
			tree = new Tree("{{ $type }}", "topic-tree", new Server(), "r0", "{{ $action }}");
		@else
			tree = new Tree("{{ $type }}", "topic-tree", new Server(), "r{{ $node }}", "{{ $action }}");
		@endif
	@else
		tree = new Tree("{{ $type }}", "topic-tree", new Server());
	@endif
</script>
<script src="{{ asset('js/topics.js') }}"></script>

@stop