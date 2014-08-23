<?php

class SocialTask extends Eloquent {

	protected $table = 'social_tasks';
	protected $key = 'task';
	public $timestamps = TRUE;

	public function user() {
		return $this->belongsTo('User');
	}
}
