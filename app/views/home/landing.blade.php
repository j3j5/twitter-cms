@extends('layout')

@section('content')
<h1>{{{ Config::get('app.project_name') }}}</h1>

<div>
	This is the landing page!
</div>
@endsection
