<?php

namespace App\Http\Controllers\Admin;

class DashboardController extends BaseAdminController
{
	public function index()
	{
		return jview('admin.dashboard', [], function($template, $data) {
			return view('admin.jview', [
				'template' => $template,
				'data' => $data,
			]);
		});
	}
}