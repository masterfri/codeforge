<?php

class AdminModule extends CWebModule
{
	public function init()
	{
		Yii::import('admin.components.*');
		Yii::import('admin.models.*');
		
		Yii::app()->messages->extensionPaths['admin'] = 'admin.messages';
	}
}
