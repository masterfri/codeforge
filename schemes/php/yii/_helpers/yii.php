<?php

$this->registerHelper('attribute_id', function ($invoker, $attribute)
{
	return $attribute->getName() . ($attribute->getType() == Attribute::TYPE_CUSTOM ? '_id' : '');
});
