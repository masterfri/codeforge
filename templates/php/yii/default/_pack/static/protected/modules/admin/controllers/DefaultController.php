<?php

class DefaultController extends AdminController
{
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionLogin()
	{
		$model = new UserLoginForm();
		
		if(isset($_POST['UserLoginForm'])) {
			$model->attributes = $_POST['UserLoginForm'];
			if($model->validate() && $model->login()) {
				$this->redirect(Yii::app()->createUrl('/admin'));
			}
		}
		
		$this->layout = 'admin.views.layouts.login';
		$this->render('login', array(
			'model' => $model,
		));
	}
	
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function accessRules()
	{
		return array(
			array('allow',
				'users' => array('@'),
			),
			array('allow',
				'actions' => array('login', 'logout'),
			),
			array('deny'),
		);
	}
}
