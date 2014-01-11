<?php

$this->registerHelper('superclass', function ($invoker, $model)
{
	return sprintf('extends %s', $model->getIsSubclass() ? current($model->getSupermodels()) : 'CActiveRecord');
});

$this->registerHelper('interfaces', function ($invoker, $model)
{
	return $model->getIsImplementation() ? sprintf('implements %s', implode(', ', $model->getInterfaces())) : '';
});

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute)
{
	$rules = array();
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT:
			if ($attribute->getIsUnsigned()) {
				$rules[] = "'numerical', 'integerOnly' => true, 'min' => 0";
			} else {
				$rules[] = "'numerical', 'integerOnly' => true";
			}
			break;
			
		case Attribute::TYPE_DECIMAL:
			if ($attribute->getIsUnsigned()) {
				$rules[] = "'numerical', 'min' => 0";
			} else {
				$rules[] = "'numerical'";
			}
			break;
			
		case Attribute::TYPE_CHAR:
			$rules[] = sprintf("'length', 'max' => %d", $attribute->getSize() ? $attribute->getSize() : 250);
			break;
			
		case Attribute::TYPE_TEXT:
			$rules[] = "'length', 'max' => 16000";
			break;
			
		case Attribute::TYPE_BOOL:
			$rules[] = "'boolean'";
			break;
		
		case Attribute::TYPE_INTOPTION:
			$rules[] = sprintf("'in', 'range' => array(%s)", implode(', ', $invoker->arrayMap('escape_value', array_keys($attribute->getOptions()))));
			break;
			
		case Attribute::TYPE_STROPTION:
			$rules[] = sprintf("'in', 'range' => array(%s)", implode(', ', $invoker->arrayMap('escape_value', $attribute->getOptions())));
			break;
		
		default:
			if (!$attribute->getIsRequired()) {
				$rules[] = "'safe'";
			}
			break;
	}
	
	if ($attribute->getIsRequired()) {
		$rules[] = "'required'";
	}
	
	return $rules;
});

$this->registerHelper('validation_rules', function ($invoker, $model)
{
	$rules = array();
	foreach ($model->getAttributes() as $attribute) {
		$attribute_id = $invoker->refer('attribute_id', $attribute);
		foreach ($invoker->refer('attribute_validation_rules', $attribute) as $rule) {
			$rules[$rule][] = $attribute_id;
		}
	}
	return $rules;
});

$this->registerHelper('relations', function ($invoker, $attribute, $model)
{
	$relations = array();
	if ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		if ($attribute->getIsCollection()) {
			if ($attribute->getIsOwn()) {
				$relations[] = sprintf("array(self::HAS_MANY, '%s', '%s_id')", $attribute->getCustomType(), strtolower($model->getName()));
			} else {
				$table = $invoker->refer('many_many_table', $model, $attribute);
				$fk1 = $invoker->refer('many_many_fk1', $model, $attribute);
				$fk2 = $invoker->refer('many_many_fk2', $model, $attribute);
				$relations[] = sprintf("array(self::MANY_MANY, '%s', '{{%s}}(%s,%s)')", $attribute->getCustomType(), $table, $fk1, $fk2);
			}
		} else {
			if ($attribute->getIsOwn()) {
				$relations[] = sprintf("array(self::HAS_ONE, '%s', '%s')", $attribute->getCustomType(), sprintf('%s_id', strtolower($model->getName())));
			} else {
				$relations[] = sprintf("array(self::BELONGS_TO, '%s', '%s')", $attribute->getCustomType(), $invoker->refer('attribute_id', $attribute));
			}
		}
	}
	return $relations;
});

$this->registerHelper('many_many_table', function ($invoker, $model, $attribute) 
{
	return implode('_', array_map('strtolower', array(
		$model->getName(),
		$attribute->getCustomType(),
		$attribute->getName(),
	)));
});

$this->registerHelper('many_many_fk1', function ($invoker, $model, $attribute) 
{
	return sprintf('%s_id', strtolower($model->getName()));
});

$this->registerHelper('many_many_fk2', function ($invoker, $model, $attribute) 
{
	return sprintf('%s_id', strtolower($attribute->getCustomType()));
});
