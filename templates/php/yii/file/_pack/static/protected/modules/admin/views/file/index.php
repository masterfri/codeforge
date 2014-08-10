<?php

$this->pageHeading = Yii::t('admin.crud', 'Manage Files');

$this->breadcrumbs = array(
	Yii::t('admin.crud', 'Files'),
);

$this->menu = array(
	array(
		'label' => '<i class="glyphicon glyphicon-upload"></i> ' . Yii::t('admin.crud', 'Upload'), 
		'url' => array('create'),
		'visible' => Yii::app()->user->checkAccess('create_file'),
	),
	array(
		'label' => '<i class="glyphicon glyphicon-list"></i> ' . Yii::t('admin.crud', 'File Categories'), 
		'url' => array('fileCategory/index'),
		'visible' => Yii::app()->user->checkAccess('view_file_category'),
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
			'id' => 'file-grid',
			'dataProvider' => $provider,
			'columns' => array(
				array(
					'header' => Yii::t('file', 'Preview'),
					'value' => 'CHtml::link($data->getIsImage() ? CHtml::image($data->getUrlResized(150, 100), "") : CHtml::tag("div", array("class" => "thumb-placeholder"), ""), $data->getUrl(), array("class" => "thumbnail", "target" => "_blank"))',
					'type' => 'raw',
					'headerHtmlOptions' => array('style' => 'width: 150px;'),
				),
				'title',
				array(
					'name' => 'category_id',
					'value' => 'CHtml::value($data, "category.title", Yii::t("file", "Without Category"))',
				),
				array(
					'name' => 'size',
					'value' => '$data->friendlySize',
				),
				'create_time:date',
				array(
					'class' => 'ButtonColumn',
					'deleteConfirmation' => Yii::t('admin.crud', 'Are you sure you want to delete this file?'),
					'template' => '{view}'.
						(Yii::app()->user->checkAccess('update_file') ? '{update}' : '').
						(Yii::app()->user->checkAccess('delete_file') ? '{delete}' : ''),
				),
			),
		)); ?>
	</div>
</div>
