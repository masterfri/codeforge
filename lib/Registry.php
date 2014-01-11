<?php

class Registry
{
	protected static $singleton;
	protected $data = array();
	
	public static function getSingleton()
	{
		if (null === self::$singleton) {
			self::$singleton = new self();
		}
		return self::$singleton;
	}
	
	public function getSubregistry($group)
	{
		$registry = $this->get($group);
		if (null === $registry) {
			$registry = new Registry();
			$this->set($group, $registry);
		}
		return $registry;
	}
	
	public function get($name)
	{
		return $this->has($name) ? $this->data[$name] : null;
	}
	
	public function set($name, $value)
	{
		$this->data[$name] = $value;
		return $this;
	}
	
	public function drop($name)
	{
		unset($this->data[$name]);
		return $this;
	}
	
	public function has($name)
	{
		return isset($this->data[$name]);
	}
	
	public function push($value)
	{
		$this->data[] = $value;
		return $this;
	}
	
	public function pushUnique($value)
	{
		if (! $this->contains($value)) {
			$this->data[] = $value;
		}
		return $this;
	}
	
	public function contains($value)
	{
		return in_array($value, $this->data);
	}
	
	public function getData()
	{
		return $this->data;
	}
}
