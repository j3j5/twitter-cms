@extends('layout')

@section('content')
	<div class="container">
		Hello, this is the landing page.
		<p>
		@if($logged_in)
			You're logged in.
		@else
			You are not logged in.
		@endif
		</p>
		<p> New content coming soon...</p>
	</div>
@endsection
