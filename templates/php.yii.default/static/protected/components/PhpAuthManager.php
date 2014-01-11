<?

/**
 * This class is correspond to ACL
 */ 

class PhpAuthManager extends CPhpAuthManager
{
	public function init()
	{
		if ($this->authFile===null) {
			// Set path to file contains roles hierarchy
			$this->authFile = Yii::getPathOfAlias('application.config.auth').'.php';
		}

		parent::init();

		// Assign user role for non-guest users
		if (!Yii::app()->user->isGuest) {
			$this->assign(Yii::app()->user->role, Yii::app()->user->id);
		}
	}
	
	public function listRoles()
	{
		$list = array();
		foreach ($this->roles as $role) {
			if (!in_array($role->name, $this->defaultRoles)) {
				$list[$role->name] = $role->description;
			}
		}
		return $list;
	}
	
	public function getRoleDescription($name)
	{
		$item = $this->getAuthItem($name);
		return $item ? $item->description : '';
	}
}
