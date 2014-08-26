<?php

class AuthController extends BaseController {

	public function __construct() {
		parent::__construct();

		// You can connect a social media account at any time
		$this->beforeFilter('guest', array('except' => array('anyLogout', 'anyCallback', 'anySocial', 'anyTest')));
		$this->beforeFilter('auth', array('only' => 'anyLogout'));
		$this->beforeFilter('csrf', array('on' => 'post'));
		$this->afterFilter('log', array('only' => array('postLogin', 'postRegister')));
	}

	/**
	 * Redirect from the root of the controller
	 *
	 * @return Redirect to profile or login page
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public function anyIndex() {
		if(Auth::check()) {
			Redirect::to('profile');
		}
		return Redirect::to('auth/login');
	}

	/**
	 * Registration Page
	 */
	public function getRegister() {
		return View::make('auth.register');
	}

	/**
	 * Login Page
	 */
	public function getLogin() {
		return View::make('auth.login');
	}

	/**
	 * Proccess input to register a user
	 *
	 * @return Redirects to /profile on success
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public function postRegister() {
		// it a POST Request, you should validate the form
		$inputs = array('username', 'password', 'password2', 'email');
		$rules = array(
			'required|alpha_dash', // username
			'required|alpha_dash', // password
			'required|alpha_dash', // password2
			'email', // password2
		);
		$this->createValidator($inputs, $rules);

		$username =  strtolower(Input::get('username'));
		if(strcmp(Input::get('password'), Input::get('password2')) !== 0) {
			return Redirect::to('register')
			->with('flash_error', "Your passwords didn't match.")
			->withInput();
		}

		User::create(array(
			'username' => $username,
			'password' => Hash::make(Input::get('password')),
			'name' => Input::get('name'),
			'email' => Input::get('email'),
			'passwordEnabled' => 1,
		));
		// Create the default settings

		return Redirect::to('profile')->with('flash_notice', 'Yeeehaaa! You have signed up!');
	}


	/**
	 * Login with user/pass combination
	 *
	 * @return Redirects to profile or to getLogin in case of error.
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public function postLogin() {
		$inputs = array('username', 'password', 'remember_me');
		$rules = array(
			'required|alpha_dash', // username
			'required|alpha_dash', // password
			'boolean', // remember_me
		);
		$validator = $this->createValidator($inputs, $rules);
		if($validator->fails()) {
			$message = "Doh! Something went chiviri: <ul> ";
			foreach ($validator->messages()->all() as $msg) {
				$message .= '<li>' . $msg . '</li>';
			}
			$message .= '</ul>';
			return Redirect::to('auth/login')->with('flash_error', $message)->withInput();
		}

		// it a POST Request, you should validate the form
		$login = array(
			'username' => Input::get('username'),
			'password' => Input::get('password')
		);

		if(Input::get('remember_me', FALSE)) {
			$remember_me = TRUE;
		} else {
			$remember_me = FALSE;
		}

		$result = Auth::attempt($login, $remember_me);
		if ($result) {
			// get logged user id.
			if(!Auth::user()->passwordEnabled) {
				Auth::logout();
				View::make('auth.login')
					->with('flash_error',
							"Caramba! The user you're trying to log in with has disabled the
							login with password. Try login with your social profiles or <a href=\"mailto:" . Config::get('app.support.email') . "\">drop us
							an email</a> with your problem.");
			}
			return Redirect::to('profile');
		}
		return View::make('auth.login')->with('flash_error', 'Snap! We could not sign you in. Username or password did not match.')->withInput();
	}

	/**
	 * Provide a login url for social services (only Twitter for now)
	 */
	public function anySocial($provider = '') {
		switch($provider) {
			case 'twitter':
				$sign_in_twitter = TRUE;
				$force_login = FALSE;
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/auth/callback';
				$config = array('token' => '', 'secret' => '');
				Twitter::set_new_config($config);
				$token = Twitter::getRequestToken($url);
				if( isset( $token['oauth_token_secret'] ) ) {
					$url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);

					Session::put('oauth_state', 'start');
					Session::put('oauth_request_token', $token['oauth_token']);
					Session::put('oauth_request_token_secret', $token['oauth_token_secret']);
					return Redirect::to($url);
				}
				break;
			default:
				return Redirect::to('auth/login')
						->with('flash_error',
								'Sorry, ' . $provider . ' is not supported yet for authentication.</br>' .
								'Do you think it should be? <a href="mailto:"'. Config::get('app.support.email') .
								'"> Drop us an email and we\'ll try to add it soon based on popular demand.');
				break;
		}
		return Redirect::to('auth/login');
	}

	/**
	 * Callback for social logins (only Twiter for now)
	 *
	 * @return return
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public function anyCallback() {
		if(Session::has('oauth_request_token')) {
			$request_token = array(
				'token' => Session::get('oauth_request_token'),
				'secret' => Session::get('oauth_request_token_secret'),
			);

			Twitter::set_new_config($request_token);

			$oauth_verifier = FALSE;
			if(Input::has('oauth_verifier')) {
				$oauth_verifier = Input::get('oauth_verifier');
			}

			// getAccessToken() will reset the token for you
			$token = Twitter::getAccessToken( $oauth_verifier );
			if( !isset( $token['oauth_token_secret'] ) OR empty($token['oauth_token_secret'])) {
				return Redirect::to('auth/login')->with('flash_error', 'We could not log you in on Twitter.');
			}

			$credentials = Twitter::query('account/verify_credentials');
			if( is_object( $credentials ) && !isset( $credentials->error ) ) {
				$credentials->oauth_token = $token['oauth_token'];
				$credentials->oauth_token_secret = $token['oauth_token_secret'];
				$profile = $this->findTwitterProfile($credentials);
				if($profile) {
					return Redirect::to('/profile')->with('flash_notice', "Congrats! You've successfully signed in!");
				}
			}
			return Redirect::to('/auth/login')->with('flash_error', 'Crab! Something went wrong while signing you up!');
		}
	}

	/**
	 * Log out from the session
	 *
	 * @return Redirects to the auth/login page with a success message
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	public function anyLogout() {
		// Log out
		Auth::logout();
		return Redirect::to('auth/login')->with('flash_notice', "You've been logged out!");
	}






	/**
	 * Try to find on the system the user and profile that logged in.
	 * If it doesn't exist, it'll create both.
	 *
	 * @param Object $user_info Object returned by Twitter's API
	 *
	 * @return Object|Bool The profile model or FALSE
	 *
	 * @author Julio Foulquié <julio@tnwlabs.com>
	 */
	private function findTwitterProfile($user_info) {
		$user = FALSE;
		$profile = Profile::where('social_id', '=', $user_info->id)->where('provider', '=','twitter')->first();

		/**
		 * Check if user is currently logged in first
		 *    ... then check if their is a profile matching the social user
		 *    ... then check if the email matches
		 * If none of the above, create a new user.
		 */
		if (Auth::check()) {
			$user = Auth::user();
		} elseif ($profile) {
			// ok, we found an existing user
			$user = $profile->user()->first();
			Log::debug('Found a profile='.$profile->display_name);
			Log::debug('Logging in!!');
		}

		// If we haven't found a user, we need to create a new one
		if (!$user) {
			Log::debug('Did not find user, creating');
			$user = new User();

			// Default config
			$user->username = $user_info->screen_name;
			// Create a random password but disable the login w/ it
			$user->password = Hash::make(uniqid());
			$user->passwordEnabled = 0;

			// get the custom config from the db.php config file
			$result_user = $user->save();
			if ( !$result_user ) {
				Log::error('FAILED TO SAVE USER');
				return FALSE;
			}
		}
		Log::info('succesful login!');

		if (!$profile) {
			Log::info('Creating twitter profile');
			Log::info(print_r($user_info, TRUE));
			// If we didn't find the profile, we need to create a new one
			$profile = $this->createTwitterProfile($user_info, $user);

		} else {
			Log::info('Updating twitter profile');
			// If we did find a profile, make sure we update any changes to the source
			$profile = $this->updateTwitterProfile($user_info, $profile);
		}
		$result_profile = $profile->save();
		$settings = $profile->twitterSettings()->first();
		if(empty($settings)) {
			Log::info('Failed to find settings, creting new ones.');
			$settings = $this->createDefaultTwitterSettings($user->id, $profile->social_id);
		}

		if (!$result_profile) {
			Log::error('FAILED TO SAVE PROFILE');
			return FALSE;
		} elseif(empty($settings)) {
			Log::error('Failed to save or find settings.');
			return FALSE;
		}

		// Login the user
		Auth::login($user);

		return $profile;
	}

	private function createDefaultTwitterSettings($user_id, $profile_id) {
		return TwitterSetting::create(array('profile_id' => $profile_id, 'user_id' => $user_id));
	}

	private function createTwitterProfile($social_user, $user) {
		$profile = new Profile;

		$profile->user_id 			= $user->id;
		$profile->provider 			= 'twitter';
		$profile->social_id 		= $social_user->id;
		$profile->access_token		= $social_user->oauth_token;
		$profile->secret			= $social_user->oauth_token_secret;
		$profile->social_username	= $social_user->screen_name;
		$profile->display_name		= $social_user->name;
		$profile->avatar			= $social_user->profile_image_url;

		return $profile;
	}

	private function updateTwitterProfile($social_user, $profile) {
		$profile->access_token		= $social_user->oauth_token;
		$profile->secret			= $social_user->oauth_token_secret;
		$profile->social_username	= $social_user->screen_name;
		$profile->display_name		= $social_user->name;
		$profile->avatar			= $social_user->profile_image_url;

		return $profile;
	}

}
