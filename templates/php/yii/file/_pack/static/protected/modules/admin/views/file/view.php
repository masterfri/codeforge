<?php

$this->pageHeading = Yii::t('admin.crud', 'File Information');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'Files') => array('index'), 
	Yii::t('admin.crud', 'File Information'),
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-upload"></i> ' . Yii::t('admin.crud', 'Upload'), 
		'url' => array('create'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-pencil"></i> ' . Yii::t('admin.crud', 'Update File'), 
		'url' => array('update', 'id' => $model->id),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-trash"></i> ' . Yii::t('admin.crud', 'Delete File'), 
		'url' => '#', 
		'linkOptions' => array(
			'submit' => array('delete', 'id' => $model->id),
			'confirm' => Yii::t('admin.crud', 'Are you sure you want to delete this file?'),
		),
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
	<?php $this->widget('DetailView', array(
		'data' => $model,
		'attributes' => array(
			array(
				'label' => Yii::t('file', 'Preview'),
				'value' => CHtml::link($model->getIsImage() ? CHtml::image($model->getUrlResized(150, 100), '') : '', $model->getUrl(), array('class' => 'thumbnail', 'target' => '_blank')),
				'type' => 'raw',
				'visible' => $model->getIsImage(),
			),
			'title',
			array(
				'name' => 'category_id',
				'value' => CHtml::value($model, 'category.title', Yii::t('file', 'Without Category')),
			),
			array(
				'label' => Yii::t('file', 'URL'),
				'value' => $model->getUrl(),
				'type' => 'url',
			),
			'mime',
			array(
				'name' => 'size',
				'value' => $model->friendlySize,
			),
			array(
				'name' => 'width',
				'visible' => $model->getIsImage(),
			),
			array(
				'name' => 'height',
				'visible' => $model->getIsImage(),
			),
			'create_time:date',
		),
	)); ?>
</div>
