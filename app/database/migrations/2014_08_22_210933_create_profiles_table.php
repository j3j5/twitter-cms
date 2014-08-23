<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('social_profiles', function (Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('provider', 50);
			$table->string('social_id', 255);
			$table->text('access_token')->nullable();
			$table->string('secret', 500)->nullable();
			$table->string('refresh_token', 500)->nullable();
			$table->string('social_username', 100);
			$table->string('display_name', 100);
			$table->string('avatar', 255)->nullable();
			$table->integer('expires')->defaults(0)->nullable();

			$table->timestamps();
			$table->index('user_id');
			$table->unique(array('provider', 'social_id')); // Primary key
			$table->unique(array('provider', 'user_id')); // Max 1 profile per network and per user
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('social_profiles');
	}

}
