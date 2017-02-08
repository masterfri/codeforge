@extends('admin.layouts.default')

@section('footjs')

app.ajaxik.init().populateTemplate($('#maincontainer'), {!! json_encode([
	'name' => $template,
	'data' => $data,
]) !!});

@endsection