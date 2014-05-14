<?php

class User extends CActiveRecord  
{
	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;
	const STATUS_LOCKED = 2;
	
	public $password_plain;
	public $password_confirm;
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'user';
	}
	
	public function attributeLabels()
	{
		return array(
			'email' => Yii::t('user', 'Email'),
			'username' => Yii::t('user', 'Username'),
			'role' => Yii::t('user', 'Role'),
			'roleName' => Yii::t('user', 'Role'),
			'status' => Yii::t('user', 'Status'),
			'statusName' => Yii::t('user', 'Status'),
			'password_plain' => Yii::t('user', 'Password'),
			'password_confirm' => Yii::t('user', 'Confirm Password'),
		);
	}
	
	public function rules()
	{
		return array(
			array('	email', 
					'email'),
			array('	email,
					role,
					username,
					status', 
					'required'),
			array(' username', 
					'match', 'pattern' => '/^[a-z0-9 _.-]+$/i', 'message' => 'В логине можно использовать только латинские буквы, цыфры, пробелы, тире, точки и символ подчеркивания'),
			array(' username', 
					'unique', 'on' => 'create'),
			array(' username', 
					'unique', 'on' => 'update', 'criteria' => array('condition' => 'id != :id', 'params' => array(':id' => $this->id))),
			array(' email', 
					'unique', 'on' => 'create'),
			array(' email', 
					'unique', 'on' => 'update', 'criteria' => array('condition' => 'id != :id', 'params' => array(':id' => $this->id))),
			array('	password_plain,
					password_confirm',
					'required', 'on' => 'create'),
			array(' password_plain', 
					'safe', 'on' => 'update'),
			array(' password_confirm', 
					'compare', 'compareAttribute' => 'password_plain', 'on' => 'create, update'),
			array('	email,
					username,
					role', 
					'safe', 'on' => 'search'),
		);
	}
	
	protected function beforeSave()
	{
		if(parent::beforeSave()) {
			if($this->isNewRecord) {
				$this->date_created = time();
			}
			if (! empty($this->password_plain)) {
				$this->salt = $this->generateSalt();
				$this->password = $this->hashPassword($this->password_plain, $this->salt);
			}
			return true;
		} else {
			return false;
		}
	}
	
	public function search($params=array())
	{
		$criteria = new CDbCriteria($params);
		$criteria->compare('t.username', $this->username, true);
		$criteria->compare('t.email', $this->email, true);
		$criteria->compare('t.role', $this->role, true);
		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
	
	public static function getList($params=array())
	{
		$criteria = new CDbCriteria($params);
		$criteria->order = 'username';
		return CHtml::listData(self::model()->findAll($criteria), 'id', 'username');
	}
	
	public function validatePassword($password)
	{
		return $this->hashPassword($password, $this->salt) === $this->password;
	}

	public function hashPassword($password, $salt)
	{
		return md5($salt . $password);
	}
	
	public function generateRandomString($len=10)
	{
		$alpha = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$l = strlen($alpha);
		$str = '';
		for ($i = 0; $i < $len; $i++) {
			$str .= $alpha[rand(0, $l - 1)];
		}
		return $str;
	}
	
	public function generateSalt()
	{
		return $this->generateRandomString(15);
	}
	
	public function getStatusName()
	{
		switch ($this->status) {
			case self::STATUS_LOCKED:
				return Yii::t('user', 'Locked');
				
			case self::STATUS_ENABLED:
				return Yii::t('user', 'Active');
				
			case self::STATUS_DISABLED:
				return Yii::t('user', 'Inactive');
				
			default:
				return Yii::t('user', 'Undefined');
		}
	}
	
	public static function listRoles()
	{
		return Yii::app()->authManager->listRoles();
	}
	
	public function getRoleName()
	{
		return Yii::app()->authManager->getRoleDescription($this->role);
	}
	
	public function __toString()
	{
		return $this->getDisplayName();
	}
	
	public function getDisplayName()
	{
		return $this->username;
	}
}
