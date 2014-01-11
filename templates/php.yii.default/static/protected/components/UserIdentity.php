<?php

class UserIdentity extends CUserIdentity
{
	const ERROR_USER_DISABLED = 100;
	const ERROR_USER_LOCKED = 101;
	
	private $_id;

	public function setUser(User $user)
	{
		$this->_id = $user->id;
		$this->username = $user->username;
	}

	public function authenticate()
	{
		if (strpos($this->username, '@') !== false) {
			$user = User::model()->find('email = ?', array($this->username));
		} else {
			$user = User::model()->find('username = ?', array($this->username));
		}
		if($user === null) {
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} elseif (!$user->validatePassword($this->password)) {
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		} elseif ($user->status == User::STATUS_DISABLED) {
			$this->errorCode = self::ERROR_USER_DISABLED;
		} elseif ($user->status == User::STATUS_LOCKED) {
			$this->errorCode = self::ERROR_USER_LOCKED;
		} else {
			$this->setUser($user);
			$this->errorCode = self::ERROR_NONE;
		}
		return $this->errorCode == self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}
}
