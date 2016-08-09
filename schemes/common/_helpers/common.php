<?php

$this->registerHelper('nice_path', function ($invoker, $model, $extension) 
{
	$parst = explode('_', $model->getName());
	return implode(DIRECTORY_SEPARATOR, $parst) . '.' . $extension;
});

$this->registerHelper('attribute_label', function ($invoker, $attribute) 
{
	$comments = $attribute->getComments();
	if (isset($comments[0])) {
		return trim($comments[0]);
	} else {
		$words = array();
		foreach (explode('_', $attribute->getName()) as $word) {
			$words[] = ucfirst($word);
		}
		return implode(' ', $words);
	}
});

$this->registerHelper('model_label', function ($invoker, $model, $pluralize=false) 
{
	$comments = $model->getComments();
	if (isset($comments[0])) {
		return trim($comments[0]);
	} else {
		$words = preg_replace('/([a-z])([A-Z])/', '\1 \2', $model->getName());
		$label = array();
		foreach (explode(' ', $words) as $word) {
			$label[] = ucfirst($word);
		}
		return $pluralize ? $invoker->refer('pluralize', implode(' ', $label)) : implode(' ', $label);
	}
});

$this->registerHelper('pluralize', function ($invoker, $name) 
{
	$rules = array(
		'/move$/i' => 'moves',
		'/foot$/i' => 'feet',
		'/child$/i' => 'children',
		'/human$/i' => 'humans',
		'/man$/i' => 'men',
		'/tooth$/i' => 'teeth',
		'/person$/i' => 'people',
		'/([m|l])ouse$/i' => '\1ice',
		'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
		'/([^aeiouy]|qu)y$/i' => '\1ies',
		'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
		'/(shea|lea|loa|thie)f$/i' => '\1ves',
		'/([ti])um$/i' => '\1a',
		'/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
		'/(bu)s$/i' => '\1ses',
		'/(ax|test)is$/i' => '\1es',
		'/s$/' => 's',
	);
	foreach($rules as $rule=>$replacement) {
		if(preg_match($rule, $name)) {
			return preg_replace($rule, $replacement, $name);
		}
	}
	return $name . 's';
});

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Codeforge\Attribute::TYPE_INT:
			return 'int';
					
		case Codeforge\Attribute::TYPE_DECIMAL: 
			return 'decimal';
		
		case Codeforge\Attribute::TYPE_CHAR:
			return 'char';
					
		case Codeforge\Attribute::TYPE_TEXT: 
			return 'text';
					
		case Codeforge\Attribute::TYPE_BOOL: 
			return 'bool';
					
		case Codeforge\Attribute::TYPE_INTOPTION: 
			return 'option';
			
		case Codeforge\Attribute::TYPE_STROPTION: 
			return 'enum';
		
		case Codeforge\Attribute::TYPE_CUSTOM:
			return 'custom';
	}
});

$this->registerHelper('attribute_back_reference', function ($invoker, $attribute) 
{
	$references = $attribute->getOwner()->getReferences($attribute->getCustomType());
	foreach ($references as $attr) {
		if ($attr->getHint('backreference') == $attribute->getName()) {
			return $attr;
		}
	}
	return false;
});

$this->registerHelper('attribute_relation', function ($invoker, $attribute) 
{
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $attribute->getHint('relation');
		if ($relation) {
			return strtolower($relation);
		}
		$backreference = $invoker->refer('attribute_back_reference', $attribute);
		if ($backreference) {
			$backrelation = $backreference->getHint('relation');
			if ($backrelation) {
				switch (strtolower($backrelation)) {
					case 'many-to-many': return 'many-to-many';
					case 'one-to-many': return 'many-to-one';
					case 'many-to-one': return 'one-to-many';
					case 'one-to-one': return 'one-to-one';
				}
			}
		}
		if ($attribute->getIsCollection()) {
			if ($backreference && $backreference->getIsCollection()) {
				return 'many-to-many';
			} else {
				return 'one-to-many';
			}
		} else {
			if ($backreference && $backreference->getIsCollection()) {
				return 'many-to-one';
			} else {
				return 'one-to-one';
			}
		}
	}
	return false;
});
