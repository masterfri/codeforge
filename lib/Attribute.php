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

require_once CF_LIB_DIR . '/Entity.php';
require_once CF_LIB_DIR . '/AttributeCustomType.php';

class Attribute extends Entity
{
	const TYPE_INT = 1;
	const TYPE_DECIMAL = 2;
	const TYPE_CHAR = 3;
	const TYPE_TEXT = 4;
	const TYPE_BOOL = 5;
	const TYPE_INTOPTION = 6;
	const TYPE_STROPTION = 7;
	const TYPE_CUSTOM = 8;
	
	protected $isCollection = false;
	protected $isUnsigned = false;
	protected $type;
	protected $size = false;
	protected $custom_type = false;
	protected $default_value = null;
	protected $options = null;
	
	protected static $customTypes = array();
	
	public function setIsUnsigned($flag=true)
	{
		$this->isUnsigned = $flag;
	}
	
	public function getIsUnsigned()
	{
		$base = $this->getTypeBase();
		return $base ? $base->getIsUnsigned() : $this->isUnsigned;
	}
	
	public function setIsCollection($flag=true)
	{
		$this->isCollection = $flag;
	}
	
	public function getIsCollection()
	{
		return $this->isCollection;
	}
	
	public function setCustomType($name)
	{
		$this->type = self::TYPE_CUSTOM;
		$this->custom_type = $name;
	}
	
	public function setType($type, $size=false)
	{
		$this->type = $type;
		$this->size = $size;
	}
	
	public function getType()
	{
		$base = $this->getTypeBase();
		return $base ? $base->getType() : $this->type;
	}
	
	public function getIsCustomType()
	{
		return $this->type == self::TYPE_CUSTOM;
	}
	
	public function getSize()
	{
		$base = $this->getTypeBase();
		return $base ? $base->getSize() : $this->size;
	}
	
	public function getCustomType()
	{
		return $this->custom_type;
	}
	
	public function setDefaultValue($val)
	{
		if (!($val instanceof Behavior)) {
			if (self::TYPE_BOOL == $this->type && !is_bool($val) ||
				self::TYPE_INT == $this->type && !is_int($val) ||
				self::TYPE_DECIMAL == $this->type && !(is_float($val) || is_int($val))) {
				throw new AttributeException("Invalid default value");
			}
			if ((self::TYPE_INTOPTION == $this->type || self::TYPE_STROPTION == $this->type) && 
				!in_array($val, $this->options)) {
				throw new AttributeException("Default value is not in range of enumeration");
			}
		}
		$this->default_value = $val;
	}
	
	public function getDefaultValue()
	{
		return $this->default_value;
	}
	
	public function setOptions(array $options)
	{
		$this->options = $options;
	}
	
	public function getOptions()
	{
		return $this->options;
	}

	public static function registerCustomType($name, $based_on=self::TYPE_CUSTOM, $size=false, $unsigned=false)
	{
		self::$customTypes[$name] = new AttributeCustomType($name, $based_on, $size, $unsigned);
	}
	
	public static function getCustomTypes()
	{
		return self::$customTypes;
	}
	
	protected function getTypeBase()
	{
		if ($this->getIsCustomType()) {
			$custom_name = $this->getCustomType();
			if (isset(self::$customTypes[$custom_name])) {
				return self::$customTypes[$custom_name];
			}
		}
		return false;
	}
}
