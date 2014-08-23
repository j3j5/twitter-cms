<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TwitterTasks extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'twitter:tasks';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run any of the Twitter tasks (mentions, DMs...).';

	private $allowed_people;
	private $access_token;
	private $access_secret;
	private $api;
	private $twitter_task;
	private $last_processed;
	private $user_key;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	private function get_allowed_users($social_id, $provider) {

		$this->allowed_people = array(
			'808653950', // @0001Julio
			'808800859', // @0002Julio
		);
		return $this->allowed_people;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$users = User::all();
		foreach($users AS $user) {
			switch($this->argument('taskName')) {
				case "mentions":
					$this->mentions_task($user);
					break;
				default:
					$this->error("Unknown task.");
					break;
			}
		}
	}

	private function get_last_processed($social_profile) {
		///TODO: Add storage for it
		$this->mention_last_proccesed = FALSE;
		return $this->mention_last_proccesed;
	}

	private function mentions_task($user) {
		// Set the user key on mentions objects
		$this->user_key = 'user';
		$this->twitter_task = 'mentions';

		$twitter_profile = $user->profiles()->where('provider', '=', 'twitter')->first();
		if(!isset($twitter_profile->access_token) OR empty($twitter_profile->access_token)) {
			$this->error("The user " . $user->username . " doesn't have valid credentials or profiles.");
			return FALSE;
		} else {
			$this->info("Processing " . $user->username);
		}

		$this->get_allowed_users($twitter_profile->social_id, $twitter_profile->provider);

		$task = $this->get_last_processed($twitter_profile->social_id, $twitter_profile->provider);
		$options = array(
			'count' => 200,
			'include_rts' => false,
			'contributor_details' => false,
			'include_entities' => true,
		);

		// Request from the last mention processed
		if(!empty($this->last_processed) && is_numeric($this->last_processed)) {
			$options['since_id'] = $this->last_processed;
		}

		$this->info('Retrieving mentions');
		$config = array(
			'token' => $twitter_profile->access_token,
			'secret' => $twitter_profile->secret,
			'format' => 'array',
		);
		Twitter::set_new_config($config);
		$result = Twitter::query('statuses/mentions_timeline', 'GET', $options);
		if(is_array($result) && !empty($result)) {
			$first = TRUE;
			foreach($result AS $mention) {
				$this->process_message($mention, $first, $task);
			}
		}
	}

	protected function process_message(&$message, &$first, &$task) {
		// Next call will retrieve from this last id on

		if($first) {
// 			$task->last_id = $message['id_str'];
// 			$this->info(print_r($message, TRUE)); exit;
			$this->info("Latest ID: " . $message->id_str);
// 			$task->save();
			$first = FALSE;
		}

		// If the user is not on the allowd admins, ignore the mention
		if(isset($message->{$this->user_key}->screen_name)) {
			if(!in_array($message->{$this->user_key}->id_str, $this->allowed_people)) {
				$this->info('User wants to add an event but it\'s not allowed: ' . $message->{$this->user_key}->screen_name . ' from ' . $this->twitter_task);
				return FALSE;
			}
		} else {
			$this->info('Weird!! no user owning the mention?: ' . print_r($message, TRUE));
			return FALSE;
		}

		// Seems to be a request from a valid user, parse it
		$this->info('Tweet received: ' . $message->text);
	}












	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('taskName', InputArgument::REQUIRED, 'The name of the task to be run.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
// 			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
