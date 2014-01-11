<?php

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT: 
			return 'INTEGER';
			
		case Attribute::TYPE_DECIMAL: 
			return 'DECIMAL' . (is_array($attribute->getSize()) ? sprintf('(%s)', implode(',', $attribute->getSize())) : '');
			
		case Attribute::TYPE_CHAR:
			return sprintf('VARCHAR(%d)', $attribute->getSize() ? $attribute->getSize() : 250);
			
		case Attribute::TYPE_TEXT: 
			return 'TEXT';
			
		case Attribute::TYPE_BOOL: 
			return 'TINYINT';
			
		case Attribute::TYPE_INTOPTION: 
			return $attribute->getIsCollection() ? 'TEXT' : 'INTEGER';
			
		case Attribute::TYPE_STROPTION: 
			return $attribute->getIsCollection() ? 'TEXT' : sprintf('VARCHAR(%d)', $invoker->refer('optimal_option_len', $attribute->getOptions(), 10));
			
		case Attribute::TYPE_CUSTOM: 
		default:
			return 'TEXT';
	}
});

$this->registerHelper('column_definition', function ($invoker, $attribute) 
{
	if ($attribute->getType() != Attribute::TYPE_CUSTOM) {
		return sprintf('`%s` %s DEFAULT %s,', $attribute->getName(), $invoker->refer('attribute_type', $attribute), $invoker->refer('escape_value', $attribute->getDefaultValue()));
	} elseif (!$attribute->getIsCollection() && !$attribute->getIsOwn()) {
		return sprintf('`%s_id` INTEGER DEFAULT 0,', $attribute->getName());
	}
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

$this->registerHelper('escape_value', function($invoker, $val) 
{
	if (null !== $val) {
		if (is_bool($val)) {
			return $val ? '1' : '0';
		} elseif (is_numeric($val)) {
			return '"' . strval($val) . '"';
		} else {
			return '"' . addslashes($val) . '"';
		}
	}
	return 'NULL';
});
