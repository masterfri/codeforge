<?php

$this->registerType('date', Codeforge\Attribute::TYPE_CHAR);
$this->registerType('time', Codeforge\Attribute::TYPE_CHAR);
$this->registerType('datetime', Codeforge\Attribute::TYPE_CHAR);

$this->registerHelper('column_definitions', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$attribute_id = $invoker->refer('attribute_id', $attribute);
			$definitions = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$definition = sprintf('$table->date(\'%s\')', $attribute_id);
					break;
				case 'time':
					$definition = sprintf('$table->time(\'%s\')', $attribute_id);
					break;
				default:
					$definition = sprintf('$table->dateTime(\'%s\')', $attribute_id);
					break;
			}
			if(!$attribute->getBoolHint('required')) {
				$definition .= '->nullable()';
			}
			$definitions[] = $definition;
			if ($attribute->getBoolHint('searchable') || $attribute->getBoolHint('index')) {
				$definitions[] = sprintf('$table->index(\'%s\', \'idx_%s\')', $attribute_id, $attribute_id);
			}
			return $definitions;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::laravel::migration');

$this->registerHelper('attribute_cast', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			switch ($attribute->getCustomType()) {
				case 'date':
					return 'date';
				case 'datetime':
					return 'datetime';
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::php::laravel::model');

$this->registerHelper('attribute_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$rules = array();
			switch ($attribute->getCustomType()) {
				case 'time':
					$rules[] = 'time';
					break;
				default:
					$rules[] = 'date';
					break;
			}
			if ($attribute->getBoolHint('required')) {
				$rules[] = 'required';
			}
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::laravel');

$this->registerHelper('form_control', function ($invoker, $attribute, $mode='')
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			if($attribute->getBoolHint('hidden')) {
				return false;
			}
			if ('update' == $mode && $attribute->getBoolHint('readonly')) {
				return false;
			}
			switch ($attribute->getCustomType()) {
				case 'date':
					return array('extensions.datetime.form-control-datepicker', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
				
				case 'time':
					return array('extensions.datetime.form-control-timepicker', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
				
				case 'datetime':
					return array('extensions.datetime.form-control-datetimepicker', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::html::handlebars');