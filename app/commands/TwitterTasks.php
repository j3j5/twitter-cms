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
		$this->allowed_people = '*';
		return $this->allowed_people;
	}

	private function get_last_processed($task, $user_id, $provider) {
		///TODO: Add storage for it
		$task = SocialTask::where('provider', '=', $provider)->where('user_id', '=', $user_id)->where('task', '=', $task	)->first();
		if($task) {
			$this->mention_last_proccesed = $task->last_processed;
		}
		return $task;
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

		///TODO: Implement proper admin list
		$this->get_allowed_users($user->id, $twitter_profile->provider);

		$task = $this->get_last_processed('mentions', $user->id, $twitter_profile->provider);
		if(!is_object($task)) {
			$task = new SocialTask;
			$task->provider = 'twitter';
			$task->user_id = $user->id;
			$task->task = 'mentions';
		}
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
				$this->process_twitter_message($mention, $first, $task, $twitter_profile);
			}
		}
	}

	protected function process_twitter_message(&$message, &$first, &$task, &$profile) {
		// Next call will retrieve from this last id on

		if($first) {
			$this->info("Latest ID: " . $message->id_str);
			$task->last_processed = $message->id_str;
// 			$task->save();
			$first = FALSE;
		}

		// If the owner decided to allow everybody $this->allowed_people will be '*'
		if(!is_string($this->allowed_people)) {
			// If the user is not on the allowd admins, ignore the mention
			if(isset($message->{$this->user_key}->screen_name)) {
				if(!in_array($message->{$this->user_key}->id_str, $this->allowed_people)) {
					$this->error('User wants to add an event but it\'s not allowed: ' . $message->{$this->user_key}->screen_name . ' from ' . $this->twitter_task);
					return FALSE;
				}
			} else {
				$this->info(__LINE__ . ': Weird!! no user owning the mention?: ' . print_r($message, TRUE));
				return FALSE;
			}
		} else if($this->allowed_people !== '*') {
			if(isset($message->{$this->user_key}->screen_name)) {
				if($message->{$this->user_key}->id_str !== $this->allowed_people) {
					$this->error('User wants to add an event but it\'s not allowed: ' . $message->{$this->user_key}->screen_name . ' from ' . $this->twitter_task);
					return FALSE;
				}
			} else {
				$this->info(__LINE__ . ': Weird!! no user owning the mention?: ' . print_r($message, TRUE));
				return FALSE;
			}
		}
		// Seems to be a request from a valid user, parse it
		$this->info('Tweet received: ' . $message->text);

// 		$this->info(print_r($message, TRUE));
		$data = $this->process_message_text($message, 'mentions', $profile);
// 		$this->info('Done!');
// 		exit;
	}


	private function process_message_text($message, $type, &$profile) {
		$matches = array();
		switch ($type) {
			case 'mentions':
				if(mb_strpos($message->text, '@'. $profile->social_username) !== 0) {
					$this->error('Incorrect format, @' . $profile->social_username . ' must be in front.');
					$data['error'] = 'Hey!! You MUST mentioned me with my name first in the tweet. Don\'t mess with me!';
					return $data;
				} else {
					$text = mb_substr($message->text, mb_strlen('@'. $profile->social_username));
					$this->info('Text to process: ' . $text);
// 					$this->info(print_r($message, TRUE));
				}
			case 'DMs':
				if(isset($message->entities->urls) && !empty($message->entities->urls)) {
					$this->info("There're URLs.");
					// URLS
					$url_ent = reset($message->entities->urls);
					$image = NULL;
					$link = $url_ent->expanded_url;
					$title = trim(str_replace($url_ent->url, '', $text));
				} elseif(isset($message->entities->media) && !empty($message->entities->media)) {
					$this->info("There's media.");
					// Pictures
					$url_ent = reset($message->entities->media);
					$image = NULL;
					if($url_ent->type == 'photo') {
						$image = $url_ent->media_url;
					}
					$link = $url_ent->expanded_url;
					$title = trim(str_replace($url_ent->url, '', $text));
				} else {
					$this->info('No entities to parse');
					// Rest or nothing
					$image = NULL;
					$link = NULL;
					$title = trim($text);
				}
				$this->info('Processed!!');

				$slug = str_replace(' ', '-', mb_substr($title, 0, 20)) . time();
				$post_info = array(
					'owner_id' => $profile->user_id,
					'author' => '@' . $message->user->screen_name,
					'slug' => $slug,
					'title' => $title,
					'link' => $link,
					'image' => $image,
					);
				try {
					$post =  Post::create($post_info);
				} catch(PDOException $pdo) {
				    var_dump($pdo->getMessage());
				    $post_info['slug'] .= microtime();
				    $post =  Post::create($post_info);
				    sleep(1);
				}

				$data = array('message' => 'Post ' . $post->id . ' added successfully.');
				break;
			default:
				$this->info('default');
				break;
		}
		return $data;
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
