<div class="form-content">

	<?php $form = $this->beginWidget('CActiveForm', array(
		'action' => Yii::app()->createUrl($this->route),
		'htmlOptions' => array('class'=>'form-inline', 'id' => 'image-picker-dialog-search'),
		'method' => 'get',
	)); ?>
	
		<div class="row">
			<div class="col-xs-6">
				<?php echo $form->label($model, 'title', array('class'=>'sr-only')); ?>
				<?php echo $form->textField($model, 'title', array('class'=>'form-control', 'placeholder' => Yii::t('admin.crud', 'Search') . '...')); ?>
			</div>
			
			<div class="col-xs-6">
				<?php echo $form->labelEx($model, 'category_id', array('class'=>'sr-only')); ?>
				<?php echo $form->dropdownList($model, 'category_id', FileCategory::getList(), array(
					'class'=>'form-control',
					'prompt' => Yii::t('file', 'All categories'),
				)); ?>
			</div>
		</div>

	<?php $this->endWidget(); ?>

</div>
<?php

$this->widget('ListView', array(
	'dataProvider' => $provider,
	'id' => 'image-picker-dialog',
	'itemView' => '_imagePickerItem',
	'viewData' => array(
		'w' => $w,
		'h' => $h,
	),
));

?>
<script type="text/javascript">
$('#image-picker-dialog-search select').change(function(){
	$('#image-picker-dialog-search').trigger('submit');
});
$('#image-picker-dialog-search').submit(function(){
	$.fn.yiiListView.update('image-picker-dialog', {
		data: $(this).serialize()
	});
	return false;
});
$('#image-picker-dialog').yiiListView({
	'ajaxUpdate':['image-picker-dialog'],
	'ajaxVar':'ajax',
	'pagerClass':'pagination-wrapper',
	'loadingClass':'list-view-loading',
	'sorterClass':'sorter',
	'enableHistory':false
});
</script>
