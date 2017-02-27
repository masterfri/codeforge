<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseAdminController extends Controller
{
	protected function listItems($view, $items, $vars=[])
	{
		return jview($view, array_merge([
			'table' => $items->toArray(),
			'token' => csrf_token(),
		], $vars), function($template, $data) {
			return view('admin.jview', [
				'template' => $template,
				'data' => $data,
			]);
		});
	}
	
	protected function displayItem($view, $item, $vars=[])
	{
		return jview($view, array_merge([
			'item' => $item,
			'token' => csrf_token(),
		], $vars), function($template, $data) {
			return view('admin.jview', [
				'template' => $template,
				'data' => $data,
			]);
		});
	}
	
	protected function displayItemForm($view, $item, $vars=[])
	{
		return jview($view, array_merge([
			'item' => $item,
			'token' => csrf_token(),
		], $vars), function($template, $data) {
			return view('admin.jview', [
				'template' => $template,
				'data' => $data,
			]);
		});
	}
}
