<?php

$this->registerHelper('permission', function ($invoker, $action, $model) 
{
	return strtolower($action) . '_' . strtolower(preg_replace('/([a-z])([A-Z])/', '\1_\2', is_string($model) ? $model : $model->getName()));
});

$this->registerHelper('attribute_id', function ($invoker, $attribute)
{
	if ($attribute->getType() == Attribute::TYPE_CUSTOM && 'many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
		return sprintf('%s_id', $attribute->getName());
	} else {
		return $attribute->getName();
	}
});

$this->registerHelper('model_name', function ($invoker, $attribute) {
	if ($attribute->getOwner()) {
		return $attribute->getOwner()->getName();
	} else {
		return null;
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
