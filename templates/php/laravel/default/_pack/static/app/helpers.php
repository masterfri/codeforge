<?php

function jview($template, $data=[], $nonajax=null) 
{
	if (request()->expectsJson()) {
		return response()->json([
			'template' => [
				'name' => $template,
				'data' => $data,
			],
		]);
	} elseif (is_callable($nonajax)) {
		return call_user_func($nonajax, $template, $data);
	} else {
		abort(400, 'Invalid request');
	}
}

function jredirect($url, $message=null) 
{
	if (request()->expectsJson()) {
		return response()->json([
			'redirect' => $url,
		] + ($message ? ['message' => $message]: []));
	} else {
		if ($message !== null) {
			if (is_array($message)) {
				Session::flash('success', $message);
			} else {
				Session::flash($message['type'], $message['text']);
			}
		}
		return redirect(url);
	}
}

function jfailure($errors, $input=null) 
{
	if (request()->expectsJson()) {
		return response()->json($errors, 422);
	} else {
		return redirect()->back()->withInput($input)->withErrors($errors);
	}
}

function jsuccess($data=null, $nonajax=null) 
{
	if (request()->expectsJson()) {
		return response()->json($data);
	} elseif (is_callable($nonajax)) {
		return call_user_func($nonajax, $data);
	} else {
		abort(400, 'Invalid request');
	}
}

function jroutes()
{
	$routes = [];
	foreach (Route::getRoutes() as $route) {
		if ($route->getName()) {
			$routes[$route->getName()] = $route->uri();
		}
	}
	return json_encode($routes);
}

function joptions($array) 
{
	$result = [];
	foreach ($array as $key => $value) {
		$result[] = [
			'key' => $key,
			'text' => $value,
		];
	}
	return $result;
}

function array_key_value($array, $key, $default=null)
{
	return array_key_exists($key, $array) ? $array[$key] : $default;
}