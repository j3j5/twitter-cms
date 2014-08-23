<?php

class Post extends Eloquent {

	public $timestamps = TRUE;

	protected $fillable = array('owner_id', 'author', 'slug', 'title', 'link', 'image');

	public function user() {
		return $this->belongsTo('User', 'owner_id');
	}
}
