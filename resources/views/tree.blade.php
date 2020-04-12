@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/_tree.css') }}">
@endpush


@extends('layout')

@section('content')

<div id="main">
	<div id="topic-tree"></div>
</div>

<!-- resource modals --> 
<!-- put here if we only want the modals to appear where the tree appears -->
<div id="my-modal" class="modal">
	<div class="modal-content">
		<span id="close-modal"><i class="fas fa-times"></i></span>
		<span id="open-resource-editor"><i id="edit-icon" display="none" class='fas fa-edit'></i></span>
		<!-- Container for resource. -->
		<div id="resource-container">
			<div class="resource-background">
				<div id="resource-head"></div>
				<div id="modules"> <!-- This is where you put the modules. -->
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	@if(isset($action) && !is_null($action))
		{{-- it's either create or edit or show --}}
		@if(isset($node) && is_null($node))
			tree = new Tree("{{ $type }}", "topic-tree", new Server(), 0, "{{ $action }}");
		@else
			tree = new Tree("{{ $type }}", "topic-tree", new Server(), {{ $node }}, "{{ $action }}");
		@endif
	@else
		tree = new Tree("{{ $type }}", "topic-tree", new Server());
	@endif
</script>
<script src="{{ asset('js/topics.js') }}"></script>

@stop