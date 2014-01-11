<?php

abstract class OptionRecord extends FuzzyRecord
{
	protected static $_ins = array();
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return '{{options}}';
	}
	
	public function rules()
	{
		return CMap::mergeArray(parent::rules(), array(
			array('optname', 'required'),
		));
	}
	
	public static function instance($class=__CLASS__)
	{
		if (! isset(self::$_ins[$class])) {
			$model = $class::model()->find('optname = ?', array($class));
			if (! $model) {
				$model = new $class();
				$model->optname = $class;
				$model->fillMetaWithEmptyValues();
			}
			self::$_ins[$class] = $model;
		}
		return self::$_ins[$class];
	}
}
