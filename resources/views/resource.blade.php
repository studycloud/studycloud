<!-- Is a resource viewer. May be embedded into a larger page; be flexible with your box size. -->
<!-- For a model, check in with home or about blade files. You won't be directly injected where these are -->

@push('styles')
<link rel="stylesheet" type="text/css" href="css/_resource.css">
@endpush

@push('scripts')
<script type="text/javascript" src="js/resource_viewer.js"></script>
@endpush

<!-- When you inject this as a component into a parent, remove next two lines because it's gonna go into the component that embeds this. -->
@extends('layout')

@section('content')

<div class="temp-container"> <!-- My container. Remove when you embed. -->
	<div class="resource-background">
		<h1 id="resource-name">
			Resource Name
		</h1>
		<div>
			<div id="author-name">Author Name</div>

		</div>
		<div> <!-- Module that varies: text/link/file -->
			<div id="content-0"></div>
			<div id="date-created">Date Created</div>
			<div id="date-modified">Date Modified</div>
		</div>
	</div>
</div>

@stop