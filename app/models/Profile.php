<?php

class Profile extends Eloquent {

	protected $table = 'social_profiles';
	protected $key = 'social_id';
	public $timestamps = TRUE;

	public function user() {
		return $this->belongsTo('User');
	}
}
