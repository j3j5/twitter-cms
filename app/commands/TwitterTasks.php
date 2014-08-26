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
	private $last_processed;

	/*
	 * Some keys to share code within Twitter
	 * tasks. We map the task to their function name,
	 * settings column name, user key on the API object...
	 */
	private $twitter_task;
	private $user_key;
	private $shard_tasks = array(
		'user_tl',
		'mentions',
		'favorites',
		'retweets',
		'DMs',
	);
	private $task_calls;
	private $task_settings;
	private $task_require_admin;

	private $modulo;
	private $shard_file;

	private $api_options;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->modulo = count($this->shard_tasks);
		$this->shard_file = storage_path() .'/logs/twitter_task_shard';
		$this->task_calls = array(
			$this->shard_tasks[0]	=> 'getUserTimeline',
			$this->shard_tasks[1]	=> 'getMentionsTimeline',
			$this->shard_tasks[2]	=> 'getFavorites',
			$this->shard_tasks[3]	=> 'getUserTimeline',
			$this->shard_tasks[4]	=> 'getDmsIn',
		);
		$this->task_settings = array(
			$this->shard_tasks[0]	=> 'user_tweets',
			$this->shard_tasks[1]	=> 'mentions',
			$this->shard_tasks[2]	=> 'user_favs',
			$this->shard_tasks[3]	=> 'user_rts',
			$this->shard_tasks[4]	=> 'user_DMs',
		);
		// Add the tasks which require a call to get_allowed_users()
		$this->task_require_admin = array(
			'mentions', 'DMs'
		);

		// Set of default options to use on the API calls
		$this->api_options = array(
			'count' => 200,
			'include_rts' => false,
			'contributor_details' => false,
			'include_entities' => true,
		);
	}

	private function get_last_processed($task, $user_id, $provider) {
		$task = SocialTask::where('provider', '=', $provider)->where('user_id', '=', $user_id)->where('task', '=', $task	)->first();
		if($task) {
			$this->last_processed = $task->last_processed;
		}
		return $task;
	}

	private function get_shard_twitter_task() {
		if(!is_file($this->shard_file)) {
			if(!is_writable(dirname($this->shard_file)) OR !touch($this->shard_file)) {
				$this->error(
					"Sorry, the folder " . dirname($this->shard_file) .
					' is not writable or the file could not be created.'
				);
				exit;
			}
		}
		$shard = trim(file_get_contents($this->shard_file));
		if ($shard === FALSE OR ($shard + 1) >= $this->modulo) {
			$shard = 0;
		} else {
			$shard++;
		}
		file_put_contents($this->shard_file, $shard);
		$this->twitter_task = $this->shard_tasks[$shard];
		$this->info("Shard: " . $shard . '; Task: ' . $this->twitter_task);
		return $this->shard_tasks[$shard];
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Enable sharding
		$this->get_shard_twitter_task();

		$setting_profiles = TwitterSetting::where($this->task_settings[$this->twitter_task], '=', 1)->get();
		if(empty($setting_profiles)) {
			$this->info('No users were found for ' . $this->twitter_task . '. Bye!');
			return;
		}

		$this->info("Processing " . count($setting_profiles) . " Twitter profiles for " . $this->twitter_task);

		foreach($setting_profiles AS $prof) {
			$user = $prof->user()->first();
			switch($this->twitter_task) {
				case "user_tl":
				case "mentions":
				case "favorites":
				case "retweets":
				case 'DMs':
					$this->{$this->twitter_task . '_task'}($user);
					break;
				default:
					$this->error("Unknown task.");
					break;
			}
		}
	}

	private function user_tl_task(&$user) {
		$this->user_key = 'user';
		$this->generic_task($user);
	}

	private function DMs_task(&$user) {
		$this->user_key = 'sender';
		$this->generic_task($user);
		return;
	}

	private function retweets_task(&$user) {
		$this->user_key = 'user';
		$this->api_options['include_rts'] = TRUE;
		$this->generic_task($user);
	}

	private function favorites_task(&$user) {
		$this->user_key = 'user';

		$this->generic_task($user);
	}

	private function mentions_task(&$user) {
		// Set the user key on mentions objects
		$this->user_key = 'user';

		$this->generic_task($user);
	}

	private function generic_task(&$user) {
		$twitter_profile = $user->profiles()->twitter()->first();
		if(!isset($twitter_profile->access_token) OR empty($twitter_profile->access_token)) {
			$this->error("The user " . $user->username . " doesn't have valid credentials or profiles.");
			return FALSE;
		} else {
			$this->info("Processing " . $user->username);
		}

		if(in_array($this->twitter_task, $this->task_require_admin)) {
			$this->get_allowed_users($user->id, $twitter_profile->provider);
		}

		$task = $this->get_last_processed($this->twitter_task, $user->id, $twitter_profile->provider);
		if(!is_object($task)) {
			$task = new SocialTask;
			$task->provider = 'twitter';
			$task->user_id = $user->id;
			$task->task = $this->twitter_task;
		}
		$options = $this->api_options;

		// Request from the last mention processed
		if(!empty($this->last_processed) && is_numeric($this->last_processed)) {
			$options['since_id'] = $this->last_processed;
		}

		$this->info('Retrieving ' . $this->twitter_task);
		$config = array(
			'token' => $twitter_profile->access_token,
			'secret' => $twitter_profile->secret,
			'format' => 'array',
		);
		Twitter::set_new_config($config);
		$endpoint_func = $this->task_calls[$this->twitter_task];
		$result = Twitter::{$endpoint_func}($options);
		if(is_array($result) && !empty($result)) {
			$first = TRUE;
			foreach($result AS $fav) {
				$this->process_twitter_message($fav, $first, $task, $twitter_profile);
			}
		}
	}

	private function process_twitter_message(&$message, &$first, &$task, &$profile) {
		// Next call will retrieve from this last id on
		if($first) {
			$this->info("Latest ID: " . $message->id_str);
			$task->last_processed = $message->id_str;
			$task->save();
			$first = FALSE;
		}

		// Does this task require to check a list of allowed people to process?
		if(in_array($this->twitter_task, $this->task_require_admin)) {
			if(!$this->is_author_allowed($message)) {
				$this->error(
					$message->{$this->user_key}->screen_name . ' tried to post to ' .
					$profile->social_username ."'s " . $this->twitter_task
				);
				return FALSE;
			}
		}

		// Seems to be a legit, proccess it
		$this->info('Tweet received: ' . $message->text);

		$data = $this->process_message_text($message, $this->twitter_task, $profile);
		$this->info('Done!');
	}

	private function process_message_text(&$message, $type, &$profile) {
		$matches = array();
		$text = $message->text;
		switch ($type) {
			case 'mentions':
				// On the mentions we need to remove the name from it, but after that, they get processed as the rest
				if(mb_strpos($message->text, '@'. $profile->social_username) === 0) {
					$text = mb_substr($text, mb_strlen('@'. $profile->social_username));
					$this->info('Text to process: ' . $text);
				}
			case 'retweets':
				if(isset($message->retweeted_status) && !empty($message->retweeted_status)) {
					$message = $message->retweeted_status;
				} else {
					// Ignore normal tweets from the timeline
					return;
				}
			case 'user_tl':
			case 'favorites':
			case 'DMs':
				$data = $this->process_tweet($message, $profile, $text);
				break;
			default:
				$this->info('default');
				exit;
		}
		return $data;
	}

	private function process_tweet(&$message, &$profile, $text) {
		var_dump($message);
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
		$title = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $title);
		$slug = str_replace(' ', '-', mb_substr($title, 0, 20)) . time();
		if(empty($slug)) {
			var_dump($message); exit;
		}
		$post_info = array(
			'owner_id' => $profile->user_id,
			'author' => '@' . $message->user->screen_name,
			'slug' => $slug,
			'title' => $title,
			'link' => $link,
			'image' => $image,
			'created_from_prov' => 'twitter',
			'created_from_msg' => $message->id_str,
			'created_at' => date('Y-m-d H:i:s', strtotime($message->created_at)),
		);
		try {
			$post =  Post::create($post_info);
		} catch(PDOException $pdo) {
			var_dump($pdo->getMessage());
			$post_info['slug'] .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
			$post =  Post::create($post_info);
			sleep(1);
		}

		return array('message' => 'Post ' . $post->id . ' added successfully.');
	}


	private function get_allowed_users($social_id, $provider) {
		///TODO: implement a proper admin list for users
		$this->allowed_people = array(
			'808653950', // @0001Julio
			'808800859', // @0002Julio
			);
			$this->allowed_people = '*';
			return $this->allowed_people;
	}

	private function is_author_allowed(&$message) {
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
		return TRUE;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
// 			array('taskName', InputArgument::REQUIRED, 'The name of the task to be run.'),
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
