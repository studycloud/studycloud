<!--Login/logout component.-->
<!--Dependencies: js/header.js (manually included in layout)-->

@if (!Auth::check())
	<li id="log-in"><a id="log-in-button" href="{{ route('login.oauth', 'google') }}">Log In</a></li>
@else
	<li id="log-in">
		<form action="{{ route('logout') }}" method="POST">
			{{ csrf_field() }}
			<input type="submit" id="log-in-button" value="{{ Auth::user()->name() }}">
		</form>
	</li>
@endif