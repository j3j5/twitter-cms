<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function (Blueprint $table)
		{
			$table->increments('id');
			$table->integer('owner_id')->unsigned();
			$table->string('author', 100);
			$table->string('slug', 100);
			$table->string('title', 255);
			$table->text('content');
			$table->string('link', 255)->nullable();
			$table->string('image', 255)->nullable();
			$table->string('created_from_prov', 100);
			$table->string('created_from_msg', 255);

			$table->timestamps();
			$table->unique('slug');
			$table->index('owner_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('posts');
	}

}
