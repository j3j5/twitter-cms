<?php

class BaseController extends Controller {

	public function __construct() {
	}

	public function missingMethod($parameters = array())
	{
		var_dump($parameters); exit;
		App::abort('404');
	}

}
