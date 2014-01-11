<?php 

abstract class FuzzyRecord extends CActiveRecord
{
	protected $_md = array();
	
	protected function metaRecordClass()
	{
		return 'MetaData';
	}
	
	abstract protected function metaFields();
	
	protected function createMetaRecord($name, $value=null)
	{
		$class = $this->metaRecordClass();
		$r = new $class();
		$r->key = $name;
		$r->value = $value;
		return $r;
	}
	
	public function fillMetaWithEmptyValues()
	{
		foreach ($this->metaFields() as $name) {
			$this->_md[$name] = $this->createMetaRecord($name);
		}
	}
	
	protected function afterFind()
	{
		$this->loadMeta();
		parent::afterFind();
	}
	
	protected function loadMeta()
	{
		$fields = $this->metaFields();
		foreach ($this->metavalues as $value) {
			if (in_array($value->key, $fields)) {
				$this->_md[$value->key] = $value;
			}
		}
	}
	
	protected function afterDelete()
	{
		foreach ($this->metavalues as $value) {
			$value->delete();
		}
		parent::afterDelete();
	}
	
	protected function afterSave()
	{
		foreach ($this->_md as $value) {
			$value->parent_id = $this->id;
			$value->save();
		}
	}
	
	public function relations()
	{
		return array(
			'metavalues' => array(self::HAS_MANY, $this->metaRecordClass(), 'parent_id'),
		);
	}
	
	public function attributeNames() 
	{ 
		return CMap::mergeArray(parent::attributeNames(), $this->metaFields());
	}
	
	public function __get($name)
	{
		if (isset($this->_md[$name])) {
			return $this->_md[$name]->value;
		} elseif (in_array($name, $this->metaFields())) {
			$this->_md[$name] = $this->createMetaRecord($name);
			return '';
		} else {
			return parent::__get($name);
		}
	}
	
	public function __isset($name)
	{
		return in_array($name, $this->metaFields()) || parent::__isset($name);
	}
	
	public function __set($name, $value)
	{
		if (isset($this->_md[$name])) {
			$this->_md[$name]->value = $value;
		} else if (in_array($name, $this->metaFields())) {
			$this->_md[$name] = $this->createMetaRecord($name, $value);
		} else {
			parent::__set($name, $value);
		}
	}
	
	public function __unset($name)
	{
		if (isset($this->_md[$name])) {
			throw new CException("Attribute `$name` can't be unset");
		} else {
			parent::__unset($name);
		}
	}
}
