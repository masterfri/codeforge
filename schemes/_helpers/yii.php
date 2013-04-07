<?php

function yii_model_rules($model)
{
	$rules = array();
	$required = array();
	foreach ($model->getAttributes() as $attribute) {
		
		if ($attribute->getIsRequired()) {
			$required[] = yii_attr_id($attribute);
		}
		
		$rule = false;
		
		switch ($attribute->getType()) {
			case Attribute::TYPE_INT:
				if ($attribute->getIsUnsigned()) {
					$rule = "'numerical', 'integerOnly' => true, 'min' => 0";
				} else {
					$rule = "'numerical', 'integerOnly' => true";
				}
				break;
				
			case Attribute::TYPE_DECIMAL:
				if ($attribute->getIsUnsigned()) {
					$rule = "'numerical', 'min' => 0";
				} else {
					$rule = "'numerical'";
				}
				break;
				
			case Attribute::TYPE_CHAR:
				$rule = sprintf("'length', 'max' => %d", $attribute->getSize() ? $attribute->getSize() : 250);
				break;
				
			case Attribute::TYPE_TEXT:
				$rule = "'length', 'max' => 16000";
				break;
				
			case Attribute::TYPE_BOOL:
				$rule = "'boolean'";
				break;
			
			case Attribute::TYPE_INTOPTION:
				$rule = sprintf("'in', 'range' => array(%s)", implode(', ', array_keys($attribute->getOptions())));
				break;
				
			case Attribute::TYPE_STROPTION:
				$rule = sprintf("'in', 'range' => array(%s)", implode(', ', array_map('php_escape_value', $attribute->getOptions())));
				break;
			
			case Attribute::TYPE_CUSTOM:
				switch ($attribute->getCustomType()) {
					case 'email':
						$rule = "'email'";
						break;
						
					case 'date':
						$rule = sprintf("'date', 'format' => '%s'", env('dateFormat', 'dd-MM-yyyy'));
						break;
					
					case 'datetime':
						$rule = sprintf("'date', 'format' => '%s'", env('dateTimeFormat', 'dd-MM-yyyy HH:mm'));
						break;
						
					case 'time':
						$rule = sprintf("'date', 'format' => '%s'", env('timeFormat', 'HH:mm'));
						break;
						
					case 'phoneNumber':
						$rule = sprintf("'match', 'pattern' => '%s'", env('phoneNumberRegexp', '/^[0-9]{10}$/'));
						break;
					
					case 'file':
						$rule = "'file'";
						break;
						
					default:
						if (! $attribute->getIsRequired()) {
							$rule = "'safe'";
						}
						break;
				}
				break;
		}
		
		if ($rule) {
			$rules[$rule][] = yii_attr_id($attribute);
		}
	}
	
	if (!empty($required)) {
		$rules["'required'"] = $required;
	}
	
	return $rules;
}

function yii_attr_id($attribute)
{
	return $attribute->getName() . (is_predefined_type($attribute)?'':'_id');
}
