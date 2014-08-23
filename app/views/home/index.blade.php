@extends('layout')

@section('content')
	<div class="container">
		Hello, this is the landing page.
		<ul>
		@foreach($posts AS $post)
			<li>

			@if(!empty($post->link))
				<a href="{{{ $post->link }}}" target="_blank">
			@endif
				<p>{{ $post->title }}</p>
			@if(!empty($post->link))
				</a>
			@endif

			@if(!empty($post->content))
				{{{ $post->content }}}
			@endif

			@if(!empty($post->image))
				<img src="{{{ $post->image }}}">
			@endif
			</li>
		@endforeach
		</ul>
	</div>
@endsection
