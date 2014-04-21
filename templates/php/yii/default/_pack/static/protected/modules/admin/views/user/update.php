<?php

$this->pageHeading = Yii::t('admin.crud', 'Updating User');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'Users') => array('index'),
	Yii::t('admin.crud', 'Update'),
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('admin.crud', 'Create User'), 
		'url' => array('create'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-eye-open"></i> ' . Yii::t('admin.crud', 'View User'), 
		'url' => array('view', 'id' => $model->id),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('admin.crud', 'Manage Users'), 
		'url'=>array('index'),
	),
);

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $this->pageHeading; ?></h3>
	</div>
	<div class="panel-body">
		<?php $this->renderPartial('_form', array(
			'model' => $model,
		)); ?>
	</div>
</div>
