<?php

class Post extends Eloquent {

	public $timestamps = TRUE;

	protected $fillable = array('owner_id', 'author', 'slug', 'title', 'link', 'image', 'created_from_prov', 'created_from_msg', 'created_at');

	public function user() {
		return $this->belongsTo('User', 'owner_id');
	}
}
