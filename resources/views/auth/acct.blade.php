<!--Login/logout component.-->
<!--Dependencies: js/header.js (manually included in layout)-->

@if (!Auth::check())
	@if (App::environment('local'))
		<li id="log-in"><a id="log-in-button">Log In</a>
			<div id="log-in-hidden" class="swing-out-top-bck"> <!-- Start with swing-out-top-bck so toggling works -->
				<form action="{{ route('login') }}" method="POST">
					{{ csrf_field() }}
					<input type="text" name="email" id="log-in-user" placeholder="username">
					<input type="password" name="password" id="log-in-pwd" placeholder="password">
					<div id="rememberDiv"><input type="checkbox" name="remember" id="remember" value="remember" class="fancy-checkbox"/><label for="remember"> Remember Me</label></div>
					<input type="submit" id="logInSub" value="Go">
				</form>
				<a href="#" id="register-btn" class="link-default">Create account!</a> <!-- links to a new page on the website as it'll be annoying if you lose your progress creating an account -->
				<a href="#" id="forget-btn" class="link-default">Forgot login?</a>
			</div>
		</li>
	@else 
		<li id="log-in"><a id="log-in-button" href="{{ route('login.oauth', 'google') }}">Log In</a></li>
	@endif
@else
	<li id="log-in">
		<form action="{{ route('logout') }}" method="POST">
			{{ csrf_field() }}
			<input type="hidden" name="auth_id" id="auth_id" value="{{ Auth::user()->id }}">
			<input type="submit" id="log-in-button" value="Log Out">
		</form>
	</li>
@endif