<?php

function nice_path($model, $extension)
{
	$parst = explode('_', $model->getName());
	return implode(DIRECTORY_SEPARATOR, $parst) . '.' . $extension;
}

function attribute_label($attribute)
{
	$words = array();
	foreach (explode('_', $attribute->getName()) as $word) {
		$words[] = ucfirst($word);
	}
	return implode(' ', $words);
}

function pluralize($name)
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
}

function max_option_len($options, $divisible=1)
{
	$max = 0;
	foreach ($options as $option) {
		$max = max($max, strlen($option));
	}
	if ($divisible > 1 && $max % $divisible) {
		$max += $divisible - $max % $divisible;
	}
	return $max;
}

function get_predefined_custom_types()
{
	return array('email', 'date', 'time', 'datetime', 'phoneNumber', 'file');
}

function is_predefined_type($attribute)
{
	return $attribute->getType() != Attribute::TYPE_CUSTOM || in_array($attribute->getCustomType(), get_predefined_custom_types());
}

function env($name, $default='')
{
	global $the_generator;
	return $the_generator->getEnv($name, $default);
}
