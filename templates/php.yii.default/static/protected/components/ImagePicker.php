<?php

class ImagePicker extends CInputWidget
{
	public $placeholder;
	public $registerStyles = true;
	public $displayWidth = 150;
	public $displayHeight = 100;
	
	public function run()
	{
		list($name, $id) = $this->resolveNameID();
		
		if(isset($this->htmlOptions['id'])) {
			$id = $this->htmlOptions['id'];
		} else {
			$this->htmlOptions['id'] = $id;
		}
		
		if(isset($this->htmlOptions['name'])) {
			$name = $this->htmlOptions['name'];
		}

		$this->registerClientScript();
		
		$url = Yii::app()->createUrl('admin/file/imagePickerDialog', array(
			'w' => $this->displayWidth,
			'h' => $this->displayHeight,
		));
		
		if($this->hasModel()) {
			$value = $this->model->{$this->attribute};
		} else {
			$value = $this->value;
		}
		$field = CHtml::hiddenField($name, $value > 0 ? $value : '', $this->htmlOptions);
		
		if ($value > 0) {
			$image = File::model()->findByPk($value);
		} else {
			$image = false;
		}

		echo '<div class="image-picker ' . ($image ? 'non-empty' : 'empty') . '" id="image-picker-' . $this->id . '">';
		echo $field;
		echo '<a href="' . $url . '" class="thumbnail image-picker-ank" onclick="$(this).openImagePickerDialog(); return false;">';
			
		if ($image) {
			echo CHtml::image($image->getUrlResized($this->displayWidth, $this->displayHeight), '', array(
				'width' => $this->displayWidth > 0 ? $this->displayWidth : 'auto',
				'height' => $this->displayHeight > 0 ? $this->displayHeight : 'auto',
			));
		}
		$placeholder = $this->placeholder !== null ? $this->placeholder : Yii::t('admin.crud', 'No thumb');
		$w = $this->displayWidth > 0 ? "{$this->displayWidth}px" : 'auto';
		$h = $this->displayHeight > 0 ? "{$this->displayHeight}px" : 'auto';
		echo '<span class="image-picker-placeholder" style="width: ' . $w . '; height: ' . $h . '; line-height: ' . $h . ';">' . 
			CHtml::encode($placeholder) . 
			'</span>';
		echo '<a class="image-picker-clear" href="#" onclick="$(this).imagePickerClear(); return false;"><i class="glyphicon glyphicon-remove"></i></a>';
		echo '</a></div>';
	}
	
	protected function registerClientScript()
	{
		$cs = Yii::app()->clientScript;
		$am = Yii::app()->assetManager;
		$baseScriptUrl = $am->publish(Yii::getPathOfAlias('zii.widgets.assets')) . '/listview';
		$cs->registerCoreScript('jquery');
		$cs->registerCoreScript('bbq');
		$cs->registerScriptFile($baseScriptUrl . '/jquery.yiilistview.js');
		$cs->registerScriptFile('/rc/js/imagepicker.js', CClientScript::POS_END);
	}
}
