<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('twitter_settings', function (Blueprint $table)
		{
			$table->string('profile_id', 100);
			$table->boolean('user_tweets')->default(0);
			$table->boolean('user_rts')->default(0);
			$table->boolean('user_favs')->default(0);
			$table->boolean('mentions')->default(0);
			$table->binary('allowed_mentions')->nullable();

			$table->timestamps();
			$table->primary('profile_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('twitter_settings');
	}

}
