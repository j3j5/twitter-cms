<?php

class AuthController extends BaseController {

	public function __construct() {
		parent::__construct();

		$this->beforeFilter('guest', array('except' => 'anyLogout'));
		$this->beforeFilter('auth', array('only' => 'anyLogout'));
		$this->beforeFilter('csrf', array('on' => 'post'));
		$this->afterFilter('log', array('only' => array('postLogin', 'postRegister')));

// 		$this->layout = 'layout2';
	}

	public function anyIndex() {
		return Redirect::to('auth/login');
	}

	/**
	 * Registration Page
	 */
	public function getRegister() {
		return View::make('auth.register');
	}

	public function postRegister() {
		// it a POST Request, you should validate the form

		$username =  strtolower(Input::get('username'));
		if(strcmp(Input::get('password'), Input::get('password2')) !== 0) {
			return Redirect::to('register')
			->with('flash_error', "Your password didn't match.")
			->withInput();
		}

		User::create(array(
			'username' => $username,
			'password' => Hash::make(Input::get('password')),
			'name' => Input::get('name'),
			'email' => Input::get('email'),
		));
		return Redirect::to('profile')->with('flash_notice', 'Yeeehaaa! You have signed up!');
	}

	/**
	 * Login Page
	 */
	public function getLogin() {
		return View::make('auth.login');
	}

	public function postLogin() {
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
			$user_id = Auth::user()->id;
			return Redirect::to('profile');
		}
		return View::make('auth.login')->with('flash_error', 'Snap! We could not sign you in. Username or password did not match.')->withInput();
	}

	public function anyLogout() {
		// Log out
		Auth::logout();
		return Redirect::to('auth/login')->with('flash_notice', "You've been logged out!");
	}

}
