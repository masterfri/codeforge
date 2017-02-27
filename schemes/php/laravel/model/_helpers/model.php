<?php

$this->registerHelper('attribute_casts', function ($invoker, $model, $sorted=true)
{
	$result = array();
	foreach ($model->getAttributes($sorted) as $attribute) {
		$name = $invoker->refer('attribute_id', $attribute);
		$cast = $invoker->refer('attribute_cast', $attribute);
		if ($cast !== false) {
			$result[$name] = $cast;
		}
	}
	return $result;
});

$this->registerHelper('attribute_cast', function ($invoker, $attribute)
{
	if ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		return 'boolean';
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_DECIMAL) {
		return 'double';
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_INT) {
		return 'integer';
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION) {
		return 'integer';
	}
	return false;
});

$this->registerHelper('attribute_setter', function ($invoker, $attribute)
{
	return sprintf('set%sAttribute', implode('', array_map('ucfirst', explode('_', is_string($attribute) ? $attribute : $attribute->getName()))));
});
