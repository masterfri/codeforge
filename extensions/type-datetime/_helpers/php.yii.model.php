<?php

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$rules = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('type.datetime.format.date', 'yyyy-MM-dd'));
					break;
				
				case 'time':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('type.datetime.format.time', 'HH:mm:ss'));
					break;
				
				case 'datetime':
					$rules[] = sprintf("'date', 'format' => '%s'", $invoker->getEnv('type.datetime.format.datetime', 'yyyy-MM-dd HH:mm:ss'));
					break;
			}
			if ($attribute->getBoolHint('required')) {
				$rules[] = "'required'";
			}
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');

$this->registerHelper('current_time', function ($invoker, $attribute, $behavior) 
{
	$format = $invoker->refer('escape_value', $behavior->getParam('format', 'Y-m-d H:i:s'));
	return sprintf("\$this->%s = date(%s);", $attribute->getName(), $format);
}, 100, '::php::yii::model');
