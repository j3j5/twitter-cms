<?php

class TwitterSetting extends Eloquent {

	protected $table = 'twitter_settings';
	public $primaryKey = 'profile_id';

	public $timestamps = TRUE;
	protected $fillable = array('profile_id');

	public function profile() {
		return $this->hasOne('Profile');
	}
}
