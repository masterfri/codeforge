<?php

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
	$result = array();
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		if ($attribute->getIsCollection()) {
			$result[] = sprintf("'%s:array'", $attribute->getName());
		} else {
			$result[] = sprintf("'%s'", $attribute->getName());
		}
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		$result[] = sprintf("'%s:boolean'", $invoker->refer('attribute_id', $attribute));
	} else {
		$result[] = sprintf("'%s'", $invoker->refer('attribute_id', $attribute));
	}
	return $result;
});

$this->registerHelper('grid_view_attributes', function ($invoker, $attribute)
{
	$result = array();
	if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		if ($attribute->getIsCollection()) {
			$result[] = sprintf("'%s:array'", $attribute->getName());
		} else {
			$result[] = sprintf("'%s'", $attribute->getName());
		}
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		$result[] = sprintf("'%s:boolean'", $invoker->refer('attribute_id', $attribute));
	} else {
		$result[] = sprintf("'%s'", $invoker->refer('attribute_id', $attribute));
	}
	return $result;
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
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_BOOL) {
		return array('form-control-dropdown', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_INTOPTION) {
		return array('form-control-dropdown', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif($attribute->getType() == Codeforge\Attribute::TYPE_STROPTION) {
		return array('form-control-dropdown', array(
			'attribute' => $attribute,
			'mode' => $mode,
		));
	} elseif ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
		$relation = $invoker->refer('attribute_relation', $attribute);
		if ('many-to-one' == $relation) {
			return array('form-control-dropdown', array(
				'attribute' => $attribute,
				'mode' => $mode,
			));
		} elseif ('many-to-many' == $relation) {
			return array('form-control-checkbox', array(
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
