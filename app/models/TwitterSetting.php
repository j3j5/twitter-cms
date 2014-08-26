<?php

class TwitterSetting extends Eloquent {

	protected $table = 'twitter_settings';
	public $primaryKey = 'profile_id';

	public $timestamps = TRUE;
	protected $fillable = array('profile_id', 'user_id');

	public function profile() {
		return $this->hasOne('Profile');
	}

	public function user() {
		return $this->hasOne('User', 'id', 'user_id' );
	}
}
