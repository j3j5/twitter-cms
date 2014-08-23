<?php

class Post extends Eloquent {

	public $timestamps = TRUE;

	public function user() {
		return $this->belongsTo('User', 'owner_id');
	}
}
