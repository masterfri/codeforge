<?php

$this->registerType('email', Attribute::TYPE_CHAR, 100);

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		if ('email' == $attribute->getCustomType()) {
			$rules = $invoker->referSuper();
			$rules[] = "'email'";
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
