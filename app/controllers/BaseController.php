<?php

class BaseController extends Controller {

	public function __construct() {
		Asset::container('header')->style('bootstrap_css', "//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css");
		Asset::container('header')->style('font_awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css");
		Asset::container('header')->style('open_sans', "//fonts.googleapis.com/css?family=Open+Sans:400,800,700,600,600italic,400italic");
		Asset::container('header')->style('global_css', "css/global.css");

		Asset::container('footer')->script('jquery', '//code.jquery.com/jquery-1.11.1.min.js');
		Asset::container('footer')->script('bootstrap_js', "//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js");
		Asset::container('footer')->add('global_js', "js/global.js");
	}

	public function createValidator($inputs, $rules) {
		if(count($inputs) !== count($rules)) {
			Log::warning("Trying to create a wrong validator. Different number of inputs and rules.");
		}

		$inputs_array = array();
		$rules_array = array();

		foreach($inputs AS $index=>$param) {
			$inputs_array[$param] = Input::get($param, FALSE);
			$rules_array[$param] = $rules[$index];
		}
		return Validator::make($inputs_array, $rules_array);
	}

	public function missingMethod($parameters = array())
	{
		App::abort('404');
	}

}
