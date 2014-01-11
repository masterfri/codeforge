<?php

class UserLoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'username' => Yii::t('user', 'Username or E-mail'),
			'password' => Yii::t('user', 'Password'),
			'rememberMe' => Yii::t('user', 'Remember Me'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity = new UserIdentity($this->username, $this->password);
		if(! $this->_identity->authenticate()) {
			switch ($this->_identity->errorCode) {
				case UserIdentity::ERROR_USER_DISABLED:
					$this->addError('username', Yii::t('user', 'Account is not activated yet'));
					break;
				case UserIdentity::ERROR_USER_LOCKED:
					$this->addError('username', Yii::t('user', 'User is locked'));
					break;
				default:
					$this->addError('password', Yii::t('user', 'Incorrect login or password'));
					break;
			}
		}
	}

	public function login()
	{
		if($this->_identity === null) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$duration = Yii::app()->user->allowAutoLogin && $this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity, $duration);
			return true;
		} else {
			return false;
		}
	}
}
