<?php

$this->registerHelper('nice_path', function ($invoker, $model, $extension) 
{
	$parst = explode('_', is_string($model) ? $model : $model->getName());
	return implode(DIRECTORY_SEPARATOR, $parst) . '.' . $extension;
});

$this->registerHelper('attribute_name', function ($invoker, $attribute) 
{
	return $attribute->getName();
});

$this->registerHelper('attribute_id', function ($invoker, $attribute)
{
	if ('belongs-to-one' == $invoker->refer('attribute_relation', $attribute)) {
		return sprintf('%s_id', $invoker->refer('attribute_name', $attribute));
	} else {
		return $invoker->refer('attribute_name', $attribute);
	}
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

$this->registerHelper('model_name', function ($invoker, $model) 
{
	return $model->getName();
});

$this->registerHelper('remove_namespace', function ($invoker, $model) 
{
	$parts = explode('_', is_string($model) ? $model : $model->getName());
	return end($parts);
});

$this->registerHelper('get_namespace', function ($invoker, $model) 
{
	$parts = explode('_', is_string($model) ? $model : $model->getName());
	array_pop($parts);
	return implode('_', $parts);
});

$this->registerHelper('model_label', function ($invoker, $model, $pluralize=false) 
{
	$comments = $model->getComments();
	if (isset($comments[0])) {
		$label = trim($comments[0]);
	} else {
		$parts = explode('_', $model->getName());
		$words = preg_replace('/([a-z])([A-Z])/', '\1 \2', array_pop($parts));
		$label = array();
		foreach (explode(' ', $words) as $word) {
			$label[] = ucfirst($word);
		}
		$label = implode(' ', $label);
	}
	return $pluralize ? $invoker->refer('pluralize', $label) : $label;
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
		if ($attr->getHint('backreference') == $attribute->getName() || $attribute->getHint('backreference') == $attr->getName()) {
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
					case 'belongs-to-many': return 'belongs-to-many';
					case 'has-many': return 'belongs-to-one';
					case 'belongs-to-one': return $attribute->getIsCollection() ? 'has-many' : 'has-one';
					case 'has-one': return 'belongs-to-one';
				}
			}
		}
		if ($attribute->getIsCollection()) {
			if ($backreference && $backreference->getIsCollection()) {
				return 'belongs-to-many';
			} else {
				return 'has-many';
			}
		} else {
			if ($backreference && $backreference->getIsCollection()) {
				return 'belongs-to-one';
			}
		}
	}
	return false;
});

$this->registerHelper('searchable_attributes', function ($invoker, $model, $sorted=true) 
{
	$result = array();
	foreach ($model->getAttributes($sorted) as $attribute) {
		if ($attribute->getBoolHint('searchable')) {
			$result[] = $attribute;
		}
	}
	return $result;
});

$this->registerHelper('writable_attributes', function ($invoker, $model, $sorted=true) 
{
	$result = array();
	foreach ($model->getAttributes($sorted) as $attribute) {
		if (!$attribute->getBoolHint('readonly')) {
			$result[] = $attribute;
		}
	}
	return $result;
});

$this->registerHelper('date_attributes', function ($invoker, $model, $sorted=true) 
{
	return array(); // extension
});

$this->registerHelper('attributes_of_type', function ($invoker, $model, $type, $sorted=true) 
{
	$result = array();
	$types = is_array($type) ? $type : array($type);
	foreach ($model->getAttributes($sorted) as $attribute) {
		foreach ($types as $type) {
			if (is_int($type)) {
				if ($attribute->getType() === $type) {
					$result[] = $attribute;
					break;
				}
			} elseif ($attribute->getIsCustomType() && $attribute->getCustomType() === $type) {
				$result[] = $attribute;
				break;
			}
		}
	}
	return $result;
});

$this->registerHelper('name_attribute', function ($invoker, $model) 
{
	foreach ($model->getAttributes() as $attribute) {
		if ($attribute->getHint('role') == 'name' || in_array($attribute->getName(), array('name', 'title'))) {
			return $attribute;
		}
	}
	return false;
});

$this->registerHelper('name_attribute_id', function ($invoker, $model, $default=false) 
{
	$attribute = $invoker->refer('name_attribute', $model);
	return $attribute ? $invoker->refer('attribute_id', $attribute) : $default;
});