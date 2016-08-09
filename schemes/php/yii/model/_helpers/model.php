<?php

$this->registerHelper('superclass', function ($invoker, $model)
{
	$super = $model->getHint('superclass', 'CActiveRecord');
	return sprintf('extends %s', $super);
});

$this->registerHelper('interfaces', function ($invoker, $model)
{
	$interfaces = $model->getHint('interface');
	$interfaces = preg_split('/\s*,\s*/', $interfaces, -1, PREG_SPLIT_NO_EMPTY);
	return count($interfaces) ? sprintf('implements %s', implode(', ', $interfaces)) : '';
});

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute)
{
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('many-to-one' == $relation || 'many-to-many' == $relation) {
			return $attribute->getBoolHint('required') ? array("'required'") : array("'safe'");
		} else {
			return array();
		}
	} else {
		$rules = array();
		switch ($attribute->getType()) {
			case Codeforge\Attribute::TYPE_INT:
				if ($attribute->getIsUnsigned()) {
					$rules[] = "'numerical', 'integerOnly' => true, 'min' => 0";
				} else {
					$rules[] = "'numerical', 'integerOnly' => true";
				}
				break;
				
			case Codeforge\Attribute::TYPE_DECIMAL:
				if ($attribute->getIsUnsigned()) {
					$rules[] = "'numerical', 'min' => 0";
				} else {
					$rules[] = "'numerical'";
				}
				break;
				
			case Codeforge\Attribute::TYPE_CHAR:
				$rules[] = sprintf("'length', 'max' => %d", $attribute->getSize() ? $attribute->getSize() : 250);
				break;
				
			case Codeforge\Attribute::TYPE_TEXT:
				$rules[] = "'length', 'max' => 16000";
				break;
				
			case Codeforge\Attribute::TYPE_BOOL:
				$rules[] = "'boolean'";
				break;
			
			case Codeforge\Attribute::TYPE_INTOPTION:
				$rules[] = sprintf("'in', 'range' => array(%s)", implode(', ', $invoker->arrayMap('escape_value', array_keys($attribute->getOptions()))));
				break;
				
			case Codeforge\Attribute::TYPE_STROPTION:
				$rules[] = sprintf("'in', 'range' => array(%s)", implode(', ', $invoker->arrayMap('escape_value', $attribute->getOptions())));
				break;
		}
		if ($attribute->getBoolHint('required')) {
			$rules[] = "'required'";
		}
		if ($pattern = $attribute->getHint('pattern')) {
			$rules[] = sprintf("'match', 'pattern' => '%s'", addslashes($pattern));
		}
		if (empty($rules)) {
			$rules[] = "'safe'";
		}
		return $rules;
	}
});

$this->registerHelper('validation_rules', function ($invoker, $model)
{
	$rules = array();
	foreach ($model->getAttributes() as $attribute) {
		if (!$attribute->getBoolHint('readonly')) {
			$attribute_id = $invoker->refer('attribute_id', $attribute);
			foreach ($invoker->refer('attribute_validation_rules', $attribute) as $rule) {
				$rules[$rule][] = $attribute_id;
			}
		}
	}
	return $rules;
});

$this->registerHelper('relations', function ($invoker, $attribute, $model)
{
	$relations = array();
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('many-to-one' == $relation) {
			$relations[] = sprintf("array(self::BELONGS_TO, '%s', '%s_id')", $attribute->getCustomType(), $attribute->getName());
		} elseif ('one-to-many' == $relation) {
			$backreference = $invoker->refer('attribute_back_reference', $attribute);
			if ($backreference) {
				$fk = sprintf('%s_id', $backreference->getName());
			} else {
				$fk = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
			}
			$relations[] = sprintf("array(self::HAS_MANY, '%s', '%s')", $attribute->getCustomType(), $fk);
		} elseif ('one-to-one' == $relation) {
			$backreference = $invoker->refer('attribute_back_reference', $attribute);
			if ($backreference) {
				$fk = sprintf('%s_id', $backreference);
			} else {
				$fk = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
			}
			$relations[] = sprintf("array(self::HAS_ONE, '%s', '%s')", $attribute->getCustomType(), $fk);
		} elseif ('many-to-many' == $relation) {
			$link = $attribute->getHint('manymanylink');
			if ($link && preg_match('#([a-z0-9_]+)\s*[(]\s*([a-z0-9_]+)\s*,\s*([a-z0-9_]+)\s*[)]#i', $link, $matches)) {
				$table_name = $invoker->refer('table_name', $matches[1]);
				$fk1 = sprintf('%s_id', $matches[2]);
				$fk2 = sprintf('%s_id', $matches[3]);
			} else {
				$table_name = sprintf('%s_%s_%s', $invoker->refer('table_name', $attribute->getOwner()), $invoker->refer('table_name', $attribute->getCustomType()), $attribute->getName());
				$fk1 = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
				$fk2 = sprintf('%s_id', $invoker->refer('table_name', $attribute->getCustomType()));
			}
			$relations[] = sprintf("array(self::MANY_MANY, '%s', '{{%s}}(%s,%s)')", $attribute->getCustomType(), $table_name, $fk1, $fk2);
		}
	}
	return $relations;
});

$this->registerHelper('behaviors', function ($invoker, $model)
{
	$result = array();
	foreach ($model->getBehaviors() as $behavior) {
		$params = $behavior->getParam();
		if (!isset($params['class'])) {
			$params = array_merge(
				array('class' => $behavior->getName()),
				$params
			);
		}
		$result[] = $params;
	}
	return $result;
});

$this->registerHelper('table_name', function ($invoker, $model) 
{
	return strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', is_string($model) ? $model : $model->getName()));
});
