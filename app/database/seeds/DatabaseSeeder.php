<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('UserTableSeeder');
		$this->command->info('User table seeded w/ a test user. u:test;p:test');
	}

}

class UserTableSeeder extends Seeder {

	public function run()
	{
		DB::table('users')->delete();

		$user = User::create(array(
			'username' => 'test',
			'password' => Hash::make('test'),
			'email' => 'foo@bar.com',
			'passwordEnabled' => 1,
		));

		DB::table('posts')->delete();

		Post::create(array(
			'owner_id' => $user->id,
			'author' => '@birrastoday',
			'slug' => '1st-post',
			'title' => 'This is the first post',
			'link' => 'http://birras.today',
			'image' => NULL,
			'created_from_prov' => 'twitter',
			'created_from_msg' => '500040015003783170',
			'created_at' => date('Y-m-d H:i:s'),
		));

		Post::create(array(
			'owner_id' => $user->id,
			'author' => '@birrastoday',
			'slug' => '2nd-post',
			'title' => 'This is the second post',
			'link' => 'http://reddit.com',
			'image' => NULL,
			'created_from_prov' => 'twitter',
			'created_from_msg' => '500040015003783170',
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

}
