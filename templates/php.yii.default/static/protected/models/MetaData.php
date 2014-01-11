<?php

class MetaData extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return '{{meta}}';
	}
	
	public function rules()
	{
		return array(
			array('key', 'required'),
		);
	}
}
