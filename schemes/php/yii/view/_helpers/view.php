<?php

$this->registerHelper('form_control_text', function ($invoker, $attribute, $options=array())
{
	$le = isset($options['line_ending']) ? $options['line_ending'] : "\n";
	
	return sprintf( "\$form->textField(\$model, '%s', array(".$le. 
					"\t'class' => 'form-control',".$le.
					"))", $invoker->refer('attribute_id', $attribute));
});

$this->registerHelper('form_control_textarea', function ($invoker, $attribute, $options=array())
{
	$le = isset($options['line_ending']) ? $options['line_ending'] : "\n";
	
	return sprintf( "\$form->textarea(\$model, '%s', array(".$le. 
					"\t'class' => 'form-control',".$le.
					"))", $invoker->refer('attribute_id', $attribute));
});

$this->registerHelper('form_control_dropdown', function ($invoker, $attribute, $options=array())
{
	$le = isset($options['line_ending']) ? $options['line_ending'] : "\n";
	$prompt = isset($options['prompt']) ? $options['prompt'] : false;
	
	if($attribute->getType() == Attribute::TYPE_BOOL) {
		$source =   "array(".$le."\t1 => Yii::t('admin.crud', 'Yes'),".$le."\t0 => Yii::t('admin.crud', 'No'),".$le.")";
	} elseif($attribute->getType() == Attribute::TYPE_INTOPTION || $attribute->getType() == Attribute::TYPE_STROPTION) {
		$options = array();
		foreach ($attribute->getOptions() as $key => $value) {
			$options[] = sprintf("\t%s => %s,", $invoker->refer('escape_value', $key), $invoker->refer('escape_value', $value)).$le;
		}
		$source = "array(".$le.implode($options).")";
	} elseif ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		$source = sprintf("%s::getList()", $attribute->getCustomType());
	} else {
		$source = "array()";
	}
	
	return sprintf( "\$form->dropdownList(\$model, '%s', %s, array(".$le. 
					"\t'class' => 'form-control',".$le.
					($attribute->getIsCollection() ? ("\t'multiple' => true,".$le) : "").
					($prompt === false ? '' : sprintf("\t'prompt' => %s,".$le, $prompt)) .
					"))", $invoker->refer('attribute_id', $attribute), $source);
});

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
	$result = array();
	if ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		if (!$attribute->getIsCollection()) {
			$result[] = sprintf("'%s'", $attribute->getName());
		}
	} elseif ($attribute->getType() == Attribute::TYPE_BOOL) {
		$result[] = sprintf("'%s:boolean'", $invoker->refer('attribute_id', $attribute));
	} else {
		$result[] = sprintf("'%s'", $invoker->refer('attribute_id', $attribute));
	}
	return $result;
});

$this->registerHelper('grid_view_attributes', function ($invoker, $attribute)
{
	$result = array();
	if ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		if (!$attribute->getIsCollection()) {
			$result[] = sprintf("'%s'", $attribute->getName());
		}
	} elseif ($attribute->getType() == Attribute::TYPE_BOOL) {
		$result[] = sprintf("'%s:boolean'", $invoker->refer('attribute_id', $attribute));
	} else {
		$result[] = sprintf("'%s'", $invoker->refer('attribute_id', $attribute));
	}
	return $result;
});

$this->registerHelper('form_control', function ($invoker, $attribute, $options=array())
{
	if($attribute->getType() == Attribute::TYPE_TEXT) {
		return $invoker->refer('form_control_textarea', $attribute, $options);
	} elseif($attribute->getType() == Attribute::TYPE_BOOL) {
		return $invoker->refer('form_control_dropdown', $attribute, $options);
	} elseif($attribute->getType() == Attribute::TYPE_INTOPTION) {
		return $invoker->refer('form_control_dropdown', $attribute, $options);
	} elseif($attribute->getType() == Attribute::TYPE_STROPTION) {
		return $invoker->refer('form_control_dropdown', $attribute, $options);
	} elseif ($attribute->getType() == Attribute::TYPE_CUSTOM && !$attribute->getIsCollection()) {
		return $invoker->refer('form_control_dropdown', $attribute, $options);
	} else {
		return $invoker->refer('form_control_text', $attribute, $options);
	}
});
