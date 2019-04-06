<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles authenticating users for the application and
	| redirecting them to your home screen. The controller uses a trait
	| to conveniently provide its functionality to your applications.
	|
	*/

	use AuthenticatesUsers;

	/**
	 * Where to redirect users after login.
	 *
	 * @var string
	 */
	protected $redirectTo = '/home';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest', ['except' => 'logout']);
	}

	/**
	  * Redirect the user to the oauth provider's authentication page.
	  *
	  * @return \Illuminate\Http\Response
	  */
	public function redirectToProvider($provider)
	{
		$response = Socialite::driver($provider);
		if ($provider == 'google')
		{
			$response = $response->with([
				"access_type" => "offline",
				"prompt" => "consent select_account",
				"hd" => "g.hmc.edu"
			]);
		}
		return $response->redirect();
	}

	/**
	 * Obtain the user information from the provider.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function handleProviderCallback($provider)
	{
		try {
			$user = Socialite::driver($provider)->stateless()->user();
		} catch (\Exception $e) {
			throw $e;
			return abort(400, "Unable to process the user information provided by ".$provider);
		}

		// check if they're an existing user
		$existingUser = User::where('email', $user->email)->first();

		if($existingUser){
			// log them in
			auth()->login($existingUser, true);
		} else {
			// create a new user
			$newUser = new User;
			$newUser->fname = $user->user->name->givenName;
			$newUser->lname = $user->user->name->familyName;
			$newUser->email = $user->email;
			$newUser->password = $user->token;
			$newUser->type = "student";
			$newUser->oauth_type = $provider;
			dd($newUser);
			$newUser->save();

			auth()->login($newUser, true);
		}
		return redirect()->to('/home');
	}
}
