<?php

abstract class AttributeBehavior extends CComponent
{
	protected $targets;
	
	public function __construct($attributes, array $options=array())
	{
		$this->targets = new CList($attributes);
		foreach ($options as $opt => $val) {
			$this->$opt = $val;
		}
	}
	
	abstract public function getNiceValue($value, $attribute, array $args=array());
	
	public function hasAttribute($attribute)
	{
		return $this->targets->contains($attribute);
	}
	
	public function getAttributes()
	{
		return $this->targets->toArray();
	}
	
	public function addAttribute($name)
	{
		$this->targets->add($name);
	}
	
	public function removeAttribute($name)
	{
		$this->targets->remove($name);
	}
	
	public function setAttributes(array $list)
	{
		$this->targets->copyFrom($list);
	}
}
