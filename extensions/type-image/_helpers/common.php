<?php

$this->registerType('image');

$this->registerHelper('attribute_relation', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			if ($attribute->getIsCollection()) {
				return 'many-to-many';
			} else {
				return 'many-to-one';
			}
		}
	}
	return $invoker->referSuper();
}, 100);
