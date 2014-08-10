<?php

$this->pageHeading = Yii::t('admin.crud', 'User Information');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'Users') => Yii::app()->user->checkAccess('view_user') ? array('index') : false, 
	Yii::t('admin.crud', 'User Information')
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('admin.crud', 'Create User'), 
		'url' => array('create'),
		'visible' => Yii::app()->user->checkAccess('create_user'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::t('admin.crud', 'Update User'), 
		'url' => array('update', 'id' => $model->id),
		'visible' => Yii::app()->user->checkAccess('update_user'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-trash"></i> ' . Yii::t('admin.crud', 'Delete User'), 
		'url' => '#', 
		'linkOptions' => array(
			'submit' => array('delete', 'id' => $model->id),
			'confirm' => Yii::t('admin.crud', 'Are you sure you want to delete this user?'),
		),
		'visible' => Yii::app()->user->checkAccess('delete_user'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('admin.crud', 'Manage Users'), 
		'url' => array('index'),
		'visible' => Yii::app()->user->checkAccess('view_user'),
	),
);

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $this->pageHeading; ?></h3>
	</div>
	<?php $this->widget('DetailView', array(
		'data' => $model,
		'attributes' => array(
			'username',
			'email',
			'roleName',
			'statusName',
		),
	)); ?>
</div>
