<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function __construct() {
		parent::__construct();
		$this->beforeFilter('auth', array('only' => array('anyProfile', 'postSettings')));
		$this->beforeFilter('csrf', array('on' => 'post'));
	}

	public function anyIndex() {
		if(Auth::check()) {
			$posts = Auth::user()->posts()->paginate(10);
			$data = array('posts' => $posts);
			return View::make('home.index', $data);
		} else {
			return View::make('home.landing');
		}
	}

	public function anyProfile() {

		$this->afterFilter('log', array('only' => array('login')));
		return View::make('home.profile');
	}

	public function postSettings() {
		$inputs = array('profile_id', 'user_tweets', 'user_rts', 'user_favs', 'mentions');
		$rules = array(
			'required|numeric', // profile_id
			'boolean', // user_tweets
			'boolean', // user_rts
			'boolean', // user_favs
			'boolean', // mentions
		);
		$validator = $this->createValidator($inputs, $rules);
		if($validator->fails()) {
			$message = "Doh! Something went chiviri: <ul> ";
			foreach ($validator->messages()->all() as $msg) {
				$message .= '<li>' . $msg . '</li>';
			}
			$message .= '</ul>';
			return Redirect::to('profile')->with('flash_error', $message)->withInput();
		}

		$settings = Auth::user()->profiles()->first()->twitterSettings()->first();
		if(empty($settings)) {
			$settings = new TwitterSetting;
		}
		$settings->profile_id = Input::get('profile_id');
		$settings->user_tweets = Input::get('user_tweets', 0);
		$settings->user_rts = Input::get('user_rts', 0);
		$settings->user_favs = Input::get('user_favs', 0);
		$settings->mentions = Input::get('mentions', 0);

		$result = $settings->save();
		if($result) {
			return Redirect::to('profile')->with('flash_notice', 'Congrats! Your settings have been stored.');
		} else {
			return Redirect::to('profile')->with('flash_error', 'Doh! There was a problem and your settings could not be stored.');
		}
	}

}
