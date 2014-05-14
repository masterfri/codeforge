<?php

class FileCategory extends CActiveRecord  
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function tableName()
	{
		return 'filecategory';
	}
	
	public function attributeLabels()
	{
		return array(
			'title' => Yii::t('fileCategory', 'Category'),
		);
	}
	
	public function rules()
	{
		return array(
			array('	title', 
					'length', 'max' => 100),
			array('	title', 
					'required'),
			array('	title', 
					'safe', 'on' => 'search'),
		);
	}
	
	public function relations()
	{
		return array(
		);
	}
	
	public function search($params=array())
	{
		$criteria = new CDbCriteria($params);
		$criteria->compare('t.title', $this->title, true);
		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
	
	public static function getList()
	{
		$criteria = new CDbCriteria();
		return CHtml::listData(self::model()->findAll($criteria), 'id', 'title');
	}
}
