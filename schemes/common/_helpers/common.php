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

$this->registerHelper('optimal_option_len', function ($invoker, $options, $divisible=1) 
{
	$max = 0;
	foreach ($options as $option) {
		$max = max($max, strlen($option));
	}
	if ($divisible > 1 && $max % $divisible) {
		$max += $divisible - $max % $divisible;
	}
	return $max;
});

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT:
			return 'int';
					
		case Attribute::TYPE_DECIMAL: 
			return 'decimal';
		
		case Attribute::TYPE_CHAR:
			return 'char';
					
		case Attribute::TYPE_TEXT: 
			return 'text';
					
		case Attribute::TYPE_BOOL: 
			return 'bool';
					
		case Attribute::TYPE_INTOPTION: 
			return 'option';
			
		case Attribute::TYPE_STROPTION: 
			return 'enum';
		
		case Attribute::TYPE_CUSTOM:
			return 'custom';
	}
});
