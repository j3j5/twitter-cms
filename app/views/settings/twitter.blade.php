Your {{ $profile->provider }} profile is {{ Twitter::linkify('@' . $profile->social_username); }}
<div>
	<h4>Settings</h4>
	@foreach($profile->twitterSettings()->get() AS $setting)
	<?php $setting = $setting->toArray();
	unset($setting['user_id']); unset($setting['created_at']); unset($setting['updated_at']); unset($setting['allowed_mentions']); ?>
	{{ Form::open( array('url' => '/settings', 'method' => 'POST', 'class' => 'form-horizontal') ) }}
			Twitter
		<fieldset>
			@foreach($setting AS $key=>$val)
				@if($key == 'profile_id')
					<input name="profile_id" type="hidden" value="{{{ $val }}}">
					<?php continue; ?>

				@endif
				<!-- settings field -->
				<div class="form-group">
				{{ Form::label($key, $key, array('class' => 'control-label col-xs-4', 'for' => $key)) }}
				<div class="col-xs-4">
						<div class="input-group">
							{{ Form::checkbox($key, 1, (bool)$val) }}
						</div>
					</div>
				</div>
			@endforeach

			<!-- csrf token	 -->
			{{ Form::token() }}

			<!-- submit button -->
			<div class="form-group">
				<div class="col-xs-offset-4 col-xs-4">
					<div class="input-group">
						{{ Form::submit('Save', array('class '=> "btn my-btn")) }}
					</div>
				</div>
			</div>
		</fieldset>
	{{ Form::close() }}
	@endforeach
</div>
