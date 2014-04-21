<?php

Yii::import('zii.widgets.grid.CButtonColumn');

class ButtonColumn extends CButtonColumn
{
	public $viewButtonImageUrl = false;
	public $viewButtonLabel = '<i class="glyphicon glyphicon-eye-open"></i>';
	public $viewButtonOptions = array('title' => 'Просмотреть');
	
	public $updateButtonImageUrl = false;
	public $updateButtonLabel = '<i class="glyphicon glyphicon-pencil"></i>';
	public $updateButtonOptions = array('title' => 'Редактировать');
	
	public $deleteButtonImageUrl = false;
	public $deleteButtonLabel = '<i class="glyphicon glyphicon-trash"></i>';
	public $deleteButtonOptions = array('title' => 'Удалить');
}
