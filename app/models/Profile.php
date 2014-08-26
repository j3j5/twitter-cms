<?php

class Profile extends Eloquent {

	protected $table = 'social_profiles';
	protected $primaryKey = 'social_id';
	public $timestamps = TRUE;

	public function user() {
		return $this->belongsTo('User');
	}

	public function twitterSettings() {
		return $this->hasOne('TwitterSetting', 'profile_id', 'social_id');
	}
}
