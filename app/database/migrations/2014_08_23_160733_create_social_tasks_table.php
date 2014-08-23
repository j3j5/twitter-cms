<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialTasksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('social_tasks', function (Blueprint $table)
		{
			$table->string('provider', 100);
			$table->string('user_id', 255);
			$table->string('task', 100);
			$table->string('last_processed', 255);

			$table->timestamps();
			$table->primary(array('provider', 'task', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('social_tasks');
	}

}
