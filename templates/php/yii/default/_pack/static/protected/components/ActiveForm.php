<?php

class ActiveForm extends CActiveForm
{
	public function checkBoxList($model, $attribute, $data, $htmlOptions=array())
	{
		CHtml::resolveNameID($model, $attribute, $htmlOptions);
		$selection = CHtml::resolveValue($model, $attribute);
		if($model->hasErrors($attribute) && !empty(CHtml::$errorCss)) {
			if(isset($htmlOptions['class'])) {
				$htmlOptions['class'] .= ' ' . CHtml::$errorCss;
			} else {
				$htmlOptions['class'] = CHtml::$errorCss;
			}
		}
		foreach ($selection as $n => $value) {
			if ($value instanceof CActiveRecord) {
				$selection[$n] = $value->getPrimaryKey();
			}
		}
		$name = $htmlOptions['name'];
		unset($htmlOptions['name']);
		if(array_key_exists('uncheckValue', $htmlOptions)) {
			$uncheck = $htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		} else {
			$uncheck = '';
		}
		$hiddenOptions = isset($htmlOptions['id']) ? 
			array('id' => CHtml::ID_PREFIX . $htmlOptions['id']) : 
			array('id' => false);
		$hidden = $uncheck !== null ? CHtml::hiddenField($name, $uncheck, $hiddenOptions) : '';
		return $hidden . CHtml::checkBoxList($name, $selection, $data, $htmlOptions);
	}
}
