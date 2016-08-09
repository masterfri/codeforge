<?php

/**
	Copyright (c) 2012 Grigory Ponomar

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details (http://www.gnu.org).
*/

namespace Codeforge;

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
