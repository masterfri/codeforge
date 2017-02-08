<?php

$this->registerType('email', Codeforge\Attribute::TYPE_CHAR);

$this->registerHelper('attribute_rules', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if ($attribute->getCustomType() == 'email') {
			$rules = array();
			$rules[] = 'email';
			if ($attribute->getBoolHint('required')) {
				$rules[] = 'required';
			}
			return $rules;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::laravel');