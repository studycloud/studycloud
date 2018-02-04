<!--Login/logout component.-->

@if (!Auth::check())
	<li id="logIn" class="logIn"><a id="logInButton">Log In</a>
		<div id="logInHidden">
			<form action="{{ route('login') }}" method="POST" id="logInForm">
				{{ csrf_field() }}
				<input type="text" name="email" id="logInUser" placeholder="username">
				<input type="text" name="password" id="logInPwd" placeholder="password">
				<label><input type="checkbox" name="remember">Remember Me</label>
				<input type="submit" id="logInSub" value=">">
			</form>
			<a href="#" class="linkDefault">Create account!</a> <!-- links to a new page on the website as it'll be annoying if you lose your progress creating an account -->
			<a href="#" class="linkDefault">Forgot login?</a>
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