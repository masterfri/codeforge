<?php

class StampBehavior extends CActiveRecordBehavior
{
	public $create_time_attribute;
	public $update_time_attribute;
	public $created_by_attribute;
	public $updated_by_attribute;
	public $time_format = 'Y-m-d H:i:s';
	
	public function beforeSave()
	{
		if ($this->owner->getIsNewRecord()) {
			if ($this->create_time_attribute) {
				$this->owner->{$this->create_time_attribute} = date($this->time_format);
			}
			if ($this->created_by_attribute) {
				$this->owner->{$this->created_by_attribute} = Yii::app()->user->id;
			}
		} else {
			if ($this->update_time_attribute) {
				$this->owner->{$this->update_time_attribute} = date($this->time_format);
			}
			if ($this->updated_by_attribute) {
				$this->owner->{$this->updated_by_attribute} = Yii::app()->user->id;
			}
		}
	}
}
