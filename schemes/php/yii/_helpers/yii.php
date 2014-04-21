<?php

$this->registerHelper('attribute_id', function ($invoker, $attribute)
{
	if ($attribute->getType() == Attribute::TYPE_CUSTOM && 'many-to-one' == $invoker->refer('attribute_relation', $attribute)) {
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
