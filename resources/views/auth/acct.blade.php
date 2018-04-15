<!--Login/logout component.-->
<!--Dependencies: js/header.js (manually included in layout)-->

@if (!Auth::check())
	<li id="log-in"><a id="log-in-button">Log In</a>
		<div id="log-in-hidden" class="swing-out-top-bck"> <!-- Start with swing-out-top-bck so toggling works -->
			<form action="{{ route('login') }}" method="POST">
				{{ csrf_field() }}
				<input type="text" name="email" id="log-in-user" placeholder="username">
				<input type="password" name="password" id="log-in-pwd" placeholder="password">
				<label id="remember"><input type="checkbox" name="remember">Remember Me</label>
				<input type="submit" id="logInSub" value="Go">
			</form>
			<a href="#" id="register-btn" class="link-default">Create account!</a> <!-- links to a new page on the website as it'll be annoying if you lose your progress creating an account -->
			<a href="#" id="forget-btn" class="link-default">Forgot login?</a>
		</div>
	</li>
@else
	<li id="log-in">
		<form action="{{ route('logout') }}" method="POST">
			{{ csrf_field() }}
			<input type="submit" id="log-in-button" value="Log Out">
		</form>
	</li>
@endif