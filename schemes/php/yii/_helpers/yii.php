<?php

$this->registerHelper('permission', function ($invoker, $action, $model) 
{
	return strtolower($action) . '_' . strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', is_string($model) ? $model : $model->getName()));
});

$this->registerHelper('attribute_id', function ($invoker, $attribute)
{
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM && 'many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
		return sprintf('%s_id', $attribute->getName());
	} else {
		return $attribute->getName();
	}
});

$this->registerHelper('searchable_attributes', function ($invoker, $model, $sorted=true) 
{
	$result = array();
	foreach ($model->getAttributes($sorted) as $attribute) {
		if ($attribute->getBoolHint('searchable')) {
			$result[] = $attribute;
		}
	}
	return $result;
});
