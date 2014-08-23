<?php

class BaseController extends Controller {

	public function __construct() {

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
