@extends('layout')

@section('content')
	<div class="content">
		<h2>Welcome "{{ Auth::user()->username }}" to the protected page!</h2>
		<div>Your user ID is: {{ Auth::user()->id }}</div>
		<div>Your email: {{ Auth::user()->email }}</div>
		<div>
			@foreach(Auth::user()->profiles()->get() AS $profile)
				<div class="profile">
					@include('settings.' . $profile->provider)
				</div>
			@endforeach
		</div>
	</div>
@endsection
