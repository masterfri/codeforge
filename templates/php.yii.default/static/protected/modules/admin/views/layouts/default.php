<?php $this->beginContent('admin.views.layouts.main'); ?>

	<?php $this->renderPartial('admin.views.layouts.include.navbar'); ?>
	
	<div class="container">
		<?php $this->renderPartial('admin.views.layouts.include.flash'); ?>
		<div class="pull-left">
			<?php $this->widget('zii.widgets.CBreadcrumbs', array(
				'homeLink' => '<li>' . CHtml::link('<i class="glyphicon glyphicon-home"></i>', array('/admin')) . '</li>',
				'links' => $this->breadcrumbs,
				'separator' => ' ',
				'tagName' => 'ol',
				'htmlOptions' => array('class' => 'breadcrumb'),
				'activeLinkTemplate' => '<li><a href="{url}">{label}</a></li>',
				'inactiveLinkTemplate' => '<li class="active">{label}</li>',
			)); ?>
		</div>
		<div class="pull-right">
			<?php $this->widget('zii.widgets.CMenu', array(
				'items' => $this->menu,
				'encodeLabel' => false,
				'activateItems' => true,
				'htmlOptions' => array(
					'class' => 'nav nav-pills',
				),
			)); ?>
		</div>
		<div class="clearfix"></div>
	</div>

	<div id="content" class="container">
		<?php echo $content; ?>
	</div>

<?php $this->endContent(); ?>
