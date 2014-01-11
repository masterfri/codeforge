<?php

$this->pageHeading = Yii::t('admin.crud', 'Updating File');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'Files') => array('index'), 
	Yii::t('admin.crud', 'Update'),
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-upload"></i> ' . Yii::t('admin.crud', 'Upload'), 
		'url' => array('create'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-eye-open"></i> ' . Yii::t('admin.crud', 'View File'), 
		'url' => array('view', 'id' => $model->id),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('admin.crud', 'Manage Files'), 
		'url'=>array('index'),
	),
);

?>


<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $this->pageHeading; ?></h3>
	</div>
	<div class="panel-body">
		<div class="form-content">
			<?php $this->renderPartial('_form', array(
				'model' => $model,
			)); ?>
		</div>
	</div>
</div>
