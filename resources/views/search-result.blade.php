<div class="div_around_subtopic" style="margin-left: .5%; margin-right: .5%; border: 5px solid black; border-bottom: 0;">
	<a href="{{ route('resources.show', ['resource' => $resource['id']]) }}" class="resource_link" style="text-decoration: none;">
		<article class="subtopic" data-subtopicid="20" data-subtopicid-selected="false" style="margin-left: 0px; margin-right: 0px; border: none;">
			<h4>
				<a href="{{ route('resources.show', ['resource' => $resource['id']]) }}">{{ $resource['name'] }}</a>
			</h4>
			<span class="author">
				<span>by: </span>{{ $resource['author'] }}</span>
			<div class="metadata" style="position: absolute; display: none; width: 10%; font-size: 86%; top: 289.275px; left: 9%; box-shadow: rgba(50, 50, 50, 0.75) 0px 0px 16px 0px; justify-content: center; align-items: center; border: 5px solid white; border-radius: 4.5px; background-color: rgb(255, 255, 255); z-index: 2; overflow: hidden;">
					<span class="search-result-meta"><span>by: </span>{{ $resource['author'] }}</span><br>
					<span class="search-result-meta">Created on: {{ $resource['created_at'] }}</span><br>
					<span class="search-result-meta">Updated on: {{ $resource['updated_at'] }}</span><br>
					<span class="search-result-meta">{{ $resource['use'] }}</span>
			</div>
		</article>
	</a>
	<div class="miniNav">
		@foreach($resource['classes'] as $class_id => $class_name)
			<a href="{{ route('classes.show', ['class' => $class_id]) }}">{{ $class_name }}</a>
			@if (!$loop->last)
				&gt;
			@endif
		@endforeach
	</div>
</div>
