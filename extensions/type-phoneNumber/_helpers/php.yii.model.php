<?php

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if ('phoneNumber' == $attribute->getCustomType()) {
			$rules = $invoker->referSuper();
			$rules[] = sprintf("'match', 'pattern' => '%s'", $invoker->getEnv('type.phonenumber.pattern', '/^[0-9]{10}$/'));
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
