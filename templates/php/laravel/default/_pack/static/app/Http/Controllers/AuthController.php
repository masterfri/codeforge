<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Session;

class AuthController extends Controller
{
	use ThrottlesLogins;

    public function login()
    {
		return jview('auth.login', [
			'token' => csrf_token(),
		], function($template, $data) {
			return view('auth.jview', [
				'template' => $template,
				'data' => $data,
			]);
		});
    }

    public function postLogin(Request $request)
    {
		$this->validate($request, [
			'email' => 'required', 
			'password' => 'required',
		]);
		if ($this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);
			return $this->sendLockoutResponse($request);
		}
		$credentials = $request->only('email', 'password');
		$remember = $request->has('remember');
		if ($this->guard()->attempt($credentials, $remember)) {
			return $this->sendLoginResponse($request);
		}
		$this->incrementLoginAttempts($request);
		return $this->sendFailedLoginResponse($request);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);
        return jsuccess([
			'event' => [
				'name' => 'login',
				'params' => [
					'intended' => Session::get('url.intended', url('/')),
				],
			],
        ], function($data) {
			return redirect()->intended('/');
		});
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return jfailure([
			'email' => ['Invalid login or password'],
        ], $request->only('email', 'remember'));
    }
    
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn($this->throttleKey($request));
        $message = sprintf('Too many login attempts, try again after %s seconds', $seconds);
		return jfailure([
			'email' => [$message],
        ], $request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        return jsuccess([
			'event' => 'logout',
        ], function($data) {
			return redirect('/');
		});
    }
    
    protected function username()
    {
		return 'email';
	}
	
	protected function guard()
    {
		return Auth::guard();
	}
}