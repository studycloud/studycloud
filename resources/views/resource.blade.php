<!-- BASICALLY DON'T USE THIS FILE I GUESS -->

<!-- Is a resource viewer. May be embedded into a larger page; be flexible with your box size. -->
<!-- For a model, check in with home or about blade files. You won't be directly injected where these are -->

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/_resource.css') }}">
@endpush

@push('scripts')
<script type="text/javascript">
	// load necessary data
	var resourceUseData = @json( App\ResourceUse::select('id', 'name')->get() );
	var contentTypeData = @json( App\ResourceContent::getPossibleTypes() );
	// var temp_resource_id = {{ $resource -> id}};
	// for now, content_id == resource_id because each resource only has 1 id
	// TODO: what to do when we have multiple contents? (not MVP)
	// TODO: how to get content id?
	// var temp_content_id = temp_resource_id;
	// if the url is: resources/{resource_id}/edit
	// isEditor is true, else it's false
	var isEditor = {{ $edit ? 'true' : 'false' }};
</script>
@endpush

<!-- When you inject this as a component into a parent, remove next two lines because it's gonna go into the component that embeds this. -->
@extends('layout')

@section('content')

<!-- The Modal -->
<div id="my-modal" class="modal">
	<div class="modal-content">
		<span id="close-modal"><i class="fas fa-times"></i></span>
		<span id="open-resource-editor"></span>
		<!-- Container for resource. -->
		<div id="resource-container">
		</div>
	</div>
</div>
@stop

@push('scripts')
<script type="text/javascript">
	// load the resource viewer or resource editor once the page is ready
	$(document).ready(function(){ 
		if (isEditor) {
			openResourceEditor(temp_resource_id);
		} else {
			openResourceViewer(temp_resource_id);
		}

		});
</script>
@endpush