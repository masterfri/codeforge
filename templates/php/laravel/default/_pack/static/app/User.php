<?php

namespace App;

use App\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	const ROLE_USER = 1;
	const ROLE_ADMIN = 2;
	
	use Authenticatable, Authorizable, CanResetPassword, Notifiable;

	protected $fillable = [
		'name', 
		'email', 
		'password',
		'role',
	];

	protected $hidden = [
		'password', 
		'remember_token',
	];
	
	public function isAdmin()
	{
		return $this->role == self::ROLE_ADMIN;
	}
}
