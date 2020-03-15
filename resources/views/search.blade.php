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
						   title="created on ${resource.created_at+(resource.created_at != resource.updated_at ? '; updated on '+resource.updated_at : '')}"
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

			function search_message_html(message, recommend) {
				if (recommend)
				{
					message += `
						<br>Keep note of the <a target="_blank" href='https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax'>syntax rules</a> when you search.
						<ol>
							<li>You can specify the fields within which to execute your search. For example, searching for "use:Notes" will return resources meant to be used as notes. The available fields are: <br>name, author, use, classes, contents.name, contents.type, contents.content</li>
							<li>You can use the ? and * wildcards.</li>
							<li>You can use + and - to specify whether a term must be present or absent.</li>
							<li>You can use the AND and OR operators.</li>
							<li>You can group search terms using parantheses. For example, the query "author:(Ebert OR Koss)" will search for resources created by either Ebert or Koss.</li>
							<li>You can use tildes ~ to perform fuzzy searches. For example, the query "Ebret~" will match "Ebert".</li>
							<li>You can use backslashes \\ and quotations \" to force terms to be interpreted literally.</li>
						</ol>
					`;
				}
				return `
					<div id='search_error_message'>
						${message}
					</div>
				`;
			}
		</script>
	</div>
@stop