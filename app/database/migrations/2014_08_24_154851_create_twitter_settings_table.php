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
			$table->integer('user_id')->unsigned();
			$table->boolean('user_tweets')->default(0);
			$table->boolean('user_rts')->default(0);
			$table->boolean('user_favs')->default(0);
			$table->boolean('user_DMs')->default(0);
			$table->boolean('mentions')->default(0);
			$table->binary('allowed_mentions')->nullable();

			$table->timestamps();
			$table->primary('profile_id');
			$table->index('user_id');
			$table->index('user_tweets');
			$table->index('user_rts');
			$table->index('user_favs');
			$table->index('user_DMs');
			$table->index('mentions');
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
