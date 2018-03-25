<!--Login/logout component.-->

@if (!Auth::check())
	<li id="logIn" class="logIn"><a id="logInButton">Log In</a>
		<div id="logInHidden" class="swing-out-top-bck"> <!-- Start with swing-out-top-bck so toggling works -->
			<form action="{{ route('login') }}" method="POST" id="logInForm">
				{{ csrf_field() }}
				<input type="text" name="email" id="logInUser" placeholder="username">
				<input type="password" name="password" id="logInPwd" placeholder="password">
				<label id="remember"><input type="checkbox" name="remember">Remember Me</label>
				<input type="submit" id="logInSub" value="Go">
			</form>
			<a href="#" id="register-btn" class="linkDefault">Create account!</a> <!-- links to a new page on the website as it'll be annoying if you lose your progress creating an account -->
			<a href="#" id="forget-btn" class="linkDefault">Forgot login?</a>
		</div>
	</li>
@else
	<li id="logIn" class="logIn">
		<form action="{{ route('logout') }}" method="POST">
			{{ csrf_field() }}
			<input type="submit" id="logInButton" value="Log Out">
		</form>
	</li>
@endif