<?php

$this->registerHelper('view_path', function ($invoker, $model) 
{
	$name = is_string($model) ? $model : $model->getName();
	$name = preg_replace('/([a-z])([A-Z])/', '\1-\2', $name);
	return strtolower(implode('/', explode('_', $name)));
});

$this->registerHelper('route_name', function ($invoker, $model) 
{
	$parts = array_map(function($v) {
		return strtolower(substr($v, 0, 1)) . substr($v, 1);
	}, explode('_', is_string($model) ? $model : $model->getName()));
	return implode('/', $parts);
});

$this->registerHelper('attribute_getter', function ($invoker, $attribute, $context='') 
{
	if ($attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION) {
		return sprintf('{{%s%s.text}}', $context, $invoker->refer('attribute_id', $attribute));
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		return sprintf('{{#if %s%s}}Yes{{else}}No{{/if}}', $context, $invoker->refer('attribute_id', $attribute));
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ($relation == 'belongs-to-one') {
			return $invoker->refer('model_to_string', $attribute->getCustomType(), $context . $invoker->refer('attribute_name', $attribute) . '.');
		} else {
			return sprintf('{{#each %s%s}}%s{{#unless @last}}, {{/unless}}{{/each}}', $context, $invoker->refer('attribute_name', $attribute), $invoker->refer('model_to_string', $attribute->getCustomType()));
		}
	} else {
		return sprintf('{{%s%s}}', $context, $invoker->refer('attribute_id', $attribute));
	}
});

$this->registerHelper('model_to_string', function ($invoker, $model, $context='') 
{
	if (is_string($model)) {
		if (!($model = $invoker->getBuilder()->getModel($model))) {
			return '';
		}
	}
	if ($name_attr = $invoker->refer('name_attribute', $model)) {
		return sprintf('{{%s%s}}', $context, $invoker->refer('attribute_id', $name_attr));
	} else {
		return sprintf('%s #{{%sid}}', $invoker->refer('model_label', $model), $context);
	}
});

$this->registerHelper('form_control', function ($invoker, $attribute, $mode='')
{
	if($attribute->getBoolHint('hidden')) {
		return false;
	}
	if ('update' == $mode && $attribute->getBoolHint('readonly')) {
		return false;
	}
	$control = $attribute->getHint('formcontrol');
	if ($control) {
		return array(sprintf('form-control-%s', strtolower($control)), array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_TEXT) {
		return array('form-control-textarea', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION || $attribute->getType() == Codeforge\Attribute::TYPE_STROPTION) {
		return array('form-control-select', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		return array('form-control-checkbox', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ($relation == 'belongs-to-one') {
			return array('form-control-ajax-select', array(
				'attribute' => $attribute,
				'mode' => $mode,
			));
		} elseif ($relation == 'belongs-to-many') {
			return array('form-control-ajax-multiselect', array(
				'attribute' => $attribute,
				'mode' => $mode,
			));
		} else {
			return false;
		}
	} else {
		return array('form-control-text', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	}
});