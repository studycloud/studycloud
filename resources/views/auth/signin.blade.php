<!--Login/logout component.-->
<!--Dependencies: js/header.js (manually included in layout)-->

@if (!Auth::check())
	<li id="log-in"><a id="log-in-button" href="{{ route('login.oauth', 'google') }}">Log In</a></li>
@else

@endif