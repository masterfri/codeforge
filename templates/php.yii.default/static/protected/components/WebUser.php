<?php

/**
 * This class owerride default `user` component
 */ 

class WebUser extends CWebUser
{
	public $guestRole = 'guest';
	protected $_model;
	
	public function getRole()
	{
		if (! $this->isGuest) {
			$model = $this->getModel();
			if ($model) {
				return $model->role;
			}
		}
		return $this->guestRole;
	}
	
	public function isAdmin()
	{
		return 'admin' == $this->getRole();
	}
	
	public function getModel()
	{
		if (null === $this->_model) {
			$this->_model = false;
			if (! $this->isGuest) {
				if ($model = User::model()->findByPk($this->id)) {
					$this->_model = $model;
				}
			}
		}
		return $this->_model;
	}
}
