<?php

$this->pageHeading = Yii::t('admin.crud', 'Manage File Categories');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'File Categories'), 
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-plus"></i> ' . Yii::t('admin.crud', 'Create File Category'), 
		'url' => array('create'),
		'visible' => Yii::app()->user->checkAccess('create_file_category'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-file"></i> ' . Yii::t('admin.crud', 'Files'), 
		'url' => array('file/index'),
		'visible' => Yii::app()->user->checkAccess('view_file'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-search"></i> ' . Yii::t('admin.crud', 'Search'), 
		'url' => '#',
		'linkOptions' => array(
			'class' => 'search-button',
			'data-toggle' => 'search-form',
		),
	),
);

?>

<div class="panel panel-default search-form" style="display: none;">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo Yii::t('admin.crud', 'Search'); ?></h3>
	</div>
	<div class="panel-body">
		<?php $this->renderPartial('_search',array(
			'model' => $model,
		)); ?>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo $this->pageHeading; ?></h3>
	</div>
	<?php $this->widget('GridView', array(
		'id' => 'filecategory-grid',
		'dataProvider' => $provider,
		'columns' => array(
			'title',
			array(
				'class' => 'ButtonColumn',
				'deleteConfirmation' => Yii::t('admin.crud', 'Are you sure you want to delete this file category?'),
				'template' => '{view}'.
					(Yii::app()->user->checkAccess('update_file_category') ? '{update}' : '').
					(Yii::app()->user->checkAccess('delete_file_category') ? '{delete}' : ''),
			),
		),
	)); ?>
</div>
