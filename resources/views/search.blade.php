@extends('layout')

@section('content')

		<!-- Page content goes here -->
		<div id='main'>
			<div id="search-results">
				<div style='padding: 0.7em'>Loading search results...</div>
				{{-- @each('search-result', $results, 'resource') --}}
				{{-- <div class="result">
					<a href="{{ route('resources.show', ['resource' => $resource['id']]) }}"
					   title="created on {{ $resource['created_at'].($resource['created_at'] != $resource['updated_at'] ? ' and updated on '.$resource['updated_at'] : '') }}"
					   data-resultid="{{ $resource['id'] }}"
					>
						<h4 title="The name of the resource">{{ $resource['name'] }}</h4>
						<div class="metadata">
							<span title="The recommended use of this resource">{{ $resource['use'] }}</span> &vert; <span title="The author of this resource">{{ $resource['author'] }}</span>
						</div>
					</a>
					<div class="miniNav">
						@foreach($resource['classes'] as $class_id => $class_name)
							<a href="{{ route('classes.show', ['class' => $class_id]) }}" data-classid="{{ $class_id }}">{{ $class_name }}</a>
							@if (!$loop->last)
								&gt;
							@endif
						@endforeach
					</div>
				</div> --}}
			</div>
			{{-- Now, we have to define all of the same HTML as above but in JS instead --}}
			<script type="text/javascript">
				function search_result_html(resource)
				{
					return `
						<div class="result">
							<a href="${"{{ route('resources.show', ['resource' => ":id"]) }}".replace("/:id", "/"+resource.id)}"
							   title="created on ${resource.created_at+(resource.created_at != resource.updated_at ? ' and updated on '+resource.updated_at : '')}"
							   data-resultid="${resource.id}"
							>
								<h4 title="The name of the resource">${resource.name}</h4>
								<div class="metadata">
									<span title="The recommended use of this resource">${resource.use}</span> &vert; <span title="The author of this resource">${resource.author}</span>
								</div>
							</a>
							<div class="miniNav">
								${Object.keys(resource.classes).map(
									function(class_id)
									{
										return `
											<a href="${"{{ route('classes.show', ['class' => ':id']) }}".replace("/:id", "/"+class_id)}" data-classid="${class_id}">${resource.classes[class_id]}</a>
										`;
								}).join(' &gt; ')}
							</div>
						</div>
					`;
				}
				$(document).ready(function(){
					update_search_results(@json($results));
				});
			</script>
		</div>
@stop