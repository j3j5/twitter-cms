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
		$this->beforeFilter('auth', array('only', 'anyProfile'));
	}

	public function anyIndex() {
		$posts = Auth::user()->posts()->get();
		$data = array('posts' => $posts);
		return View::make('home.index', $data);
	}

	public function anyProfile() {

		$this->afterFilter('log', array('only' => array('login')));
		return View::make('home.profile');
	}

}
