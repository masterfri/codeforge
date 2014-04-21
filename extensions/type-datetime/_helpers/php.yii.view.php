<?php

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$result = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$result[] = sprintf("'%s:date'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'time':
					$result[] = sprintf("'%s:time'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'datetime':
					$result[] = sprintf("'%s:datetime'", $invoker->refer('attribute_id', $attribute));
					break;
			}
			return $result;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('grid_view_attributes', function ($invoker, $attribute)
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			$result = array();
			switch ($attribute->getCustomType()) {
				case 'date':
					$result[] = sprintf("'%s:date'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'time':
					$result[] = sprintf("'%s:time'", $invoker->refer('attribute_id', $attribute));
					break;
				
				case 'datetime':
					$result[] = sprintf("'%s:datetime'", $invoker->refer('attribute_id', $attribute));
					break;
			}
			return $result;
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('form_control', function ($invoker, $attribute, $mode='')
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			if($attribute->getBoolHint('hidden')) {
				return false;
			}
			if ('update' == $mode && $attribute->getBoolHint('readonly')) {
				return false;
			}
			switch ($attribute->getCustomType()) {
				case 'date':
					return array('extensions.type-datetime.form-control-datepicker', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
				
				case 'time':
					// TODO masked field
					return array('form-control-text', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
				
				case 'datetime':
					// TODO masked field
					return array('form-control-text', array(
						'attribute' => $attribute,
						'mode' => $mode,
					));
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::view');
