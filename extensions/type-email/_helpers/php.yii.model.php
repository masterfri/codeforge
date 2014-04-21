<?php

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if ('email' == $attribute->getCustomType()) {
			$rules = $invoker->referSuper();
			$rules[] = "'email'";
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
