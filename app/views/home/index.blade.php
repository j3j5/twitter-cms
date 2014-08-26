@extends('layout')

@section('content')
	<div class="container">
		<div class="posts">
		@if($posts->isEmpty())
			<div> Sorry, there are no posts to show. </div>
		@else
			@foreach($posts AS $post)
				<div class="post clearfix">
					<div class="title">
						@if(!empty($post->link))
							<a href="{{{ $post->link }}}" target="_blank"> {{ $post->title }} </a>
						@else
							{{ $post->title }}
						@endif
					</div>
				@if(!empty($post->content))
					{{{ $post->content }}}
				@endif

				@if(!empty($post->image))
					<img src="{{{ $post->image }}}">
				@endif

				@if(!empty($post->author))
					<div class="author"> By
					@if($post->created_from_prov == 'twitter')
						{{ Twitter::linkify($post->author) }}
					@else
						{{{ $post->author }}}
					@endif
					<span class="glyphicon glyphicon-hand-right"></span>
					@if($post->created_from_prov == 'twitter')
						<a href="{{ Twitter::linkUser($post->author) . '/status/' . $post->created_from_msg }}" target="_blank"><span class="date"> {{{ Twitter::ago(strtotime($post->created_at)) }}} </span></a>
					@else
					<span class="date"> {{{ Twitter::ago(strtotime($post->created_at)) }}} </span>
					@endif
					</div>
				@endif
				</div>
			@endforeach
		@endif
		</div> <!-- 	posts	 -->
		<div class="page">
			{{ $posts->links() }}
		</div>
	</div>
@endsection
