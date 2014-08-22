@extends('layout')

@section('content')
	<div class="content">
		<h2>Welcome "{{ Auth::user()->username }}" to the protected page!</h2>
		<p>Your user ID is: {{ Auth::user()->id }}</p>
		<p>Your email: {{ Auth::user()->email }}</p>
		@foreach(Auth::user()->profiles()->get() AS $profile)
		<p>Your {{ $profile->provider }} profile is {{ Twitter::linkify('@' . $profile->social_username); }}</p>
		@endforeach
	</div>
@endsection
