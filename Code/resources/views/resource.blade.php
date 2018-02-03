<!-- Is a resource viewer. May be embedded into a larger page; be flexible with your box size. -->
<!-- For a model, check in with home or about blade files. You won't be directly injected where these are -->

@push('styles')
	<link rel="stylesheet" type="text/css" href="css/_resource.css">
@endpush

<!-- When you inject this as a component into a parent, remove this particular line because it's gonna go into the component that embeds this. -->
@stack('styles')
<div class="temp-container"> <!-- My container. Remove when you embed. -->
	<div class="resource-background">
		<h1>
			Resource Name
		</h1>
		<p>
			<a href="https://google.com">Resource Topic</a> <!-- Eventually link out to the topic. -->
			Resource Use(s)
			Author Name
			Date
		</p>
		<div> <!-- Module that varies: text/link/file -->
			<a href = "amazon.com">
				Amazon
			</a>
		</div>
	</div>
</div>