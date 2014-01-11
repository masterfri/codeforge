<?php

$this->registerType('date', Attribute::TYPE_CHAR);
$this->registerType('time', Attribute::TYPE_CHAR);
$this->registerType('datetime', Attribute::TYPE_CHAR);

$this->registerHelper('column_definition', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$def = $attribute->getDefaultValue();
			if (strtoupper($def) != 'NOW()') {
				$def = $invoker->refer('escape_value', $def);
			}
			switch ($attribute->getCustomType()) {
				case 'date':
					return sprintf('`%s` DATE DEFAULT %s,', $attribute->getName(), $def);
				
				case 'time':
					return sprintf('`%s` TIME DEFAULT %s,', $attribute->getName(), $def);
				
				case 'datetime':
					return sprintf('`%s` DATETIME DEFAULT %s,', $attribute->getName(), $def);
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::mysql');

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$rules = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('dateFormat', 'yyyy-MM-dd'));
					break;
				
				case 'time':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('timeFormat', 'HH:mm:ss'));
					break;
				
				case 'datetime':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('dateTimeFormat', 'yyyy-MM-dd HH:mm:ss'));
					break;
			}
			if ($attribute->getIsRequired()) {
				$rules[] = "'required'";
			}
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$result = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$result[] = sprintf("'%s:date'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'time':
					$result[] = sprintf("'%s:time'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'datetime':
					$result[] = sprintf("'%s:datetime'", $invoker->refer('attribute_id', $attribute));
					break;
			}
			return $result;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('grid_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$result = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$result[] = sprintf("'%s:date'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'time':
					$result[] = sprintf("'%s:time'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'datetime':
					$result[] = sprintf("'%s:datetime'", $invoker->refer('attribute_id', $attribute));
					break;
			}
			return $result;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('form_control', function ($invoker, $attribute, $options=array())
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			switch ($attribute->getCustomType()) {
				case 'date':
					return $invoker->refer('form_control_datepicker', $attribute, $options);
				
				case 'time':
					// TODO masked field
					break;
				
				case 'datetime':
					// TODO masked field
					break;
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('form_control_datepicker', function ($invoker, $attribute, $options=array())
{
	$le = isset($options['line_ending']) ? $options['line_ending'] : "\n";
	
	return sprintf( "\$this->widget('zii.widgets.jui.CJuiDatePicker', array(".$le.
					"\t'attribute' => '%s',".$le.
					"\t'model' => \$model,".$le.
					"\t'options' => array(".$le.
					"\t\t'dateFormat' => '%s',".$le.
					"\t),".$le.
					"\t'htmlOptions' => array(".$le.
					"\t\t'class' => 'form-control datepicker-form-control',".$le.
					"\t\t'readonly' => true,".$le.
					"\t),".$le.
					"), true)", $invoker->refer('attribute_id', $attribute), $invoker->getEnv('datepickerFormat', 'yy-mm-dd'));
});
