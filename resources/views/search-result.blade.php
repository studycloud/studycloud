<div class="result">
	<a href="{{ route('resources.show', ['resource' => $resource['id']]) }}"
	   title="created on {{ $resource['created_at']->format('M j, Y g:i A').($resource['created_at']->format('M j, Y g:i A') != $resource['updated_at']->format('M j, Y g:i A') ? ' and updated on '.$resource['updated_at']->format('M j, Y g:i A') : '') }}"
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
</div>
