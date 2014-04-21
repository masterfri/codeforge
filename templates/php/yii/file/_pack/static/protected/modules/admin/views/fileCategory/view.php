<?php

$this->pageHeading = Yii::t('admin.crud', 'File Category Information');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'File Categories') => array('index'),
	Yii::t('admin.crud', 'File Category Information'),
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('admin.crud', 'Create File Category'), 
		'url' => array('create'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::t('admin.crud', 'Update File Category'), 
		'url' => array('update', 'id' => $model->id),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-trash"></i> ' . Yii::t('admin.crud', 'Delete File Category'), 
		'url' => '#', 
		'linkOptions' => array(
			'submit' => array('delete', 'id' => $model->id),
			'confirm' => Yii::t('admin.crud', 'Are you sure you want to delete this file category?'),
		),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-wrench"></i> ' . Yii::t('admin.crud', 'Manage File Categories'), 
		'url' => array('index'),
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
				'title',
 
			),
		)); ?>
	</div>
</div>
