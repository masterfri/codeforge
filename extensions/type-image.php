<?php

$this->registerType('image');

$this->registerHelper('relations', function ($invoker, $attribute, $model)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			return array(
				sprintf("array(self::BELONGS_TO, '%s', '%s')", $invoker->getEnv('imageClass', 'File'), $invoker->refer('attribute_id', $attribute)),
			);
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');

$this->registerHelper('form_control', function ($invoker, $attribute, $options=array())
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			return $invoker->refer('form_control_imagepicker', $attribute, $options);
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			return array(
				sprintf("array('name' => '%s', 'type' => 'raw')", $attribute->getName()),
			);
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('grid_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			return array(
				sprintf("array('name' => '%s', 'type' => 'raw')", $attribute->getName()),
			);
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('form_control_imagepicker', function ($invoker, $attribute, $options=array())
{
	$le = isset($options['line_ending']) ? $options['line_ending'] : "\n";
	
	return sprintf( "\$this->widget('ImagePicker', array(".$le. 
					"\t'attribute' => '%s',".$le.
					"\t'model' => \$model,".$le.
					"), true)", $invoker->refer('attribute_id', $attribute));
});
