@extends('layout')

@section('content')
	<h1>Login</h1>

	<a href="/auth/social/twitter"><button>Sign in with Twitter</button></a>

	{{ Form::open( array('url' => 'auth/login', 'method' => 'POST', 'class' => 'form-horizontal') ) }}
		<fieldset>
			<!-- username field -->
			<div class="form-group">
				{{ Form::label('username', 'Username', array('class' => 'control-label col-xs-2', 'for' => 'username')) }}
				<div class="col-xs-10">
					<div class="input-group">
						{{ Form::text('username', Input::old('username')) }}
					</div>
				</div>
			</div>
			<!-- password field -->
			<div class="form-group">
				{{ Form::label('password', 'Password', array('class' => 'control-label col-xs-2', 'for' => 'password')) }}
				<div class="col-xs-10">
					<div class="input-group">
						{{ Form::password('password') }}
					</div>
				</div>
			</div>

			<!-- password field -->
			<div class="form-group">
			{{ Form::label('remember_me', 'Remember me', array('class' => 'control-label col-xs-offset-2', 'for' => 'remember_me')) }}
			<div class="col-xs-10">
					<div class="input-group">
						{{ Form::checkbox('remember_me', '1', false) }}
					</div>
				</div>
			</div>

			<!-- csrf token	 -->
			{{ Form::token() }}

			<!-- submit button -->
			<div class="form-group">
				<div class="col-xs-offset-2 col-xs-10">
					<div class="input-group">
						{{ Form::submit('Login', array('class '=> "btn my-btn")) }}
					</div>
				</div>
			</div>
		</fieldset>
	{{ Form::close() }}
@endsection
