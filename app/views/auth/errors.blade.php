@include('layout')

@section('content')
	<h1> Error while authenticating.</h1>
	<div class="container">
		Provider: {{ $provider }}
		Error: {{ $e }}
	</div>
@endsection
