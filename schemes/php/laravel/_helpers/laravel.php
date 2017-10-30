<?php

$this->registerHelper('table_name', function ($invoker, $model, $pluralize=true) 
{
	$name = is_string($model) ? $model : $model->getName();
	$name = preg_replace('/([a-z])([A-Z])/', '\1_\2', $name);
	$name = preg_replace('/[_]{2,}/', '_', $name);
	if ($pluralize) {
		$parts = explode('_', strtolower($name));
		$last = $invoker->refer('pluralize', array_pop($parts));
		$parts[] = $last;
		return implode('_', $parts);
	} else {
		return strtolower($name);
	}
});

$this->registerHelper('foreign_key', function ($invoker, $model_or_attribute) 
{
	if ($model_or_attribute instanceof Codeforge\Attribute) {
		$attribute = $model_or_attribute;
		if ($backreference = $invoker->refer('attribute_back_reference', $attribute)) {
			return $invoker->refer('attribute_id', $backreference);
		}
		$model = $model_or_attribute->getOwner();
	} else {
		$model = $model_or_attribute;
	}
	$name = is_string($model) ? $model : $model->getName();
	$name = preg_replace('/([a-z])([A-Z])/', '\1_\2', $name);
	$name = preg_replace('/[_]{2,}/', '_', $name);
	return strtolower($name) . '_id';
});

$this->registerHelper('pivot_table', function ($invoker, $model1, $model2, $attribute=null) 
{
	if ($attribute) {
		$result = $attribute->getHint('linktable');
		if ($result) {
			return $result;
		}
		$suffix1 = $invoker->refer('attribute_name', $attribute);
		$attribute2 = $invoker->refer('attribute_back_reference', $attribute);
		if ($attribute2) {
			$result = $attribute2->getHint('linktable');
			if ($result) {
				return $result;
			}
			$suffix2 = $invoker->refer('attribute_name', $attribute2);
			if (strcmp($suffix1, $suffix2) < 0) {
				$suffix = sprintf('_%s_%s', $suffix1, $suffix2);
			} else {
				$suffix = sprintf('_%s_%s', $suffix2, $suffix1);
			}
		} else {
			$suffix = sprintf('_%s', $suffix1);
		}
	} else {
		$suffix = '';
	}
	
	$table1 = $invoker->refer('table_name', $model1, false);
	$table2 = $invoker->refer('table_name', $model2, false);
	
	if (strcmp($table1, $table2) < 0) {
		$base = sprintf('%s_%s', $table1, $table2);
	} else {
		$base = sprintf('%s_%s', $table2, $table1);
	}
	
	return $invoker->refer('shortify_id', sprintf('%s%s_links', $base, $suffix));
});

$this->registerHelper('view_name', function ($invoker, $model) 
{
	$name = is_string($model) ? $model : $model->getName();
	$name = preg_replace('/([a-z])([A-Z])/', '\1-\2', $name);
	return strtolower(implode('.', explode('_', $name)));
});

$this->registerHelper('route_name', function ($invoker, $model) 
{
	$parts = array_map(function($v) {
		return strtolower(substr($v, 0, 1)) . substr($v, 1);
	}, explode('_', is_string($model) ? $model : $model->getName()));
	return implode('/', $parts);
});

$this->registerHelper('route_path', function ($invoker, $model, $pluralize=false) 
{
	$name = is_string($model) ? $model : $model->getName();
	$name = preg_replace('/([a-z])([A-Z])/', '\1-\2', $name);
	$parts = explode('_', $name);
	if ($pluralize) {
		$last = $invoker->refer('pluralize', array_pop($parts));
		$parts[] = $last;
	}
	return strtolower(implode('/', $parts));
});

$this->registerHelper('request_rules', function ($invoker, $model) 
{
	$result = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getBoolHint('readonly')) {
			$rules = $invoker->refer('attribute_rules', $attribute);
			if (count($rules)) {
				$result[$invoker->refer('attribute_id', $attribute)] = $rules;
			}
		}
	}
	return $result;
});

$this->registerHelper('attribute_rules', function ($invoker, $attribute) 
{
	$rules = array();
	
	if ($attribute->getBoolHint('required')) {
		$rules[] = 'required';
	}
			
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		if ('belongs-to-one' == $invoker->refer('attribute_relation', $attribute)) {
			$rules[] = 'integer';
		}
	} else {
		switch ($attribute->getType()) {
			case Codeforge\Attribute::TYPE_INT:
				$rules[] = 'integer';
				if ($attribute->getIsUnsigned()) {
					$rules[] = 'min:0';
				}
				break;
				
			case Codeforge\Attribute::TYPE_DECIMAL:
				$rules[] = 'numeric';
				if ($attribute->getIsUnsigned()) {
					$rules[] = 'min:0';
				}
				break;
				
			case Codeforge\Attribute::TYPE_CHAR:
				$rules[] = sprintf('max:%d', $attribute->getSize() ?: 250);
				break;
				
			case Codeforge\Attribute::TYPE_TEXT:
				$rules[] = 'max:16000';
				break;
				
			case Codeforge\Attribute::TYPE_BOOL:
				$rules[] = 'boolean';
				break;
			
			case Codeforge\Attribute::TYPE_INTOPTION:
				$rules[] = sprintf('in:%s', implode(',', array_keys($attribute->getOptions())));
				break;
				
			case Codeforge\Attribute::TYPE_STROPTION:
				$rules[] = sprintf('in:%s', implode(',', $attribute->getOptions()));
				break;
		}
		if ($pattern = $attribute->getHint('pattern')) {
			$rules[] = sprintf('regex:%s', addslashes($pattern));
		}
	}
	
	return $rules;
});

$this->registerHelper('request_sanitizers', function ($invoker, $model) 
{
	$result = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getBoolHint('readonly')) {
			$sanitizes = $invoker->refer('attribute_sanitizers', $attribute);
			if (count($sanitizes)) {
				$result[$invoker->refer('attribute_id', $attribute)] = implode('|', $sanitizes);
			}
		}
	}
	return $result;
});

$this->registerHelper('attribute_sanitizers', function ($invoker, $attribute) 
{
	$sanitizes = array();
	if (!$attribute->getBoolHint('required')) {
		$sanitizes[] = 'nullable';
	}
	if ($attribute->getType() == Codeforge\Attribute::TYPE_DECIMAL) {
		$sanitizes[] = 'float';
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_INT || $attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION || 'belongs-to-one' == $invoker->refer('attribute_relation', $attribute)) {
		$sanitizes[] = 'integer';
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		$sanitizes[] = 'boolean';
	}
	return $sanitizes;
});
