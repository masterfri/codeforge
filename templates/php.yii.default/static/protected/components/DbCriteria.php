<?php

class DbCriteria extends CDbCriteria
{
	public function applyFilterModel(CActiveRecord $model, $searchFields=array(), $defaultAttribs=array())
	{
		foreach($searchFields as $n => $field) {
			if (is_numeric($n)) {
				if ('' != trim($model->$field)) {
					$this->compare("`$field`", $model->$field);
				} elseif (isset($defaultAttribs[$field])) {
					if (is_array($defaultAttribs[$field])) {
						$this->addInCondition("`$field`", $defaultAttribs[$field]);
					} else {
						$this->compare("`$field`", $defaultAttribs[$field]);
					}
				}
			} else {
				if ('' != trim($model->$n)) {
					$this->compare("`$n`", $model->$n, $field);
				} elseif (isset($defaultAttribs[$n])) {
					if (is_array($defaultAttribs[$n])) {
						$this->addInCondition("`$n`", $defaultAttribs[$n]);
					} else {
						$this->compare("`$n`", $defaultAttribs[$n], $field);
					}
				}
			}
		}
	}
}
