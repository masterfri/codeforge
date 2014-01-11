<?php

$this->registerType('phoneNumber', Attribute::TYPE_CHAR, 50);

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getType() == Attribute::TYPE_CUSTOM) {
		if ('phoneNumber' == $attribute->getCustomType()) {
			$rules = $invoker->referSuper();
			$rules[] = sprintf("'match', 'pattern' => '%s'", $invoker->getEnv('phoneNumberRegexp', '/^[0-9]{10}$/'));
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
