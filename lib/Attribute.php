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

class Attribute
{
	const PRIV_PUBLIC = 1;
	const PRIV_PROTECTED = 2;
	const PRIV_PRIVATE = 3;
	
	const MODE_DEFAULT = 1;
	const MODE_READONLY = 2;
	const MODE_WRITEONLY = 3;
	const MODE_NOACCESS = 4;
	
	const TYPE_INT = 1;
	const TYPE_DECIMAL = 2;
	const TYPE_CHAR = 3;
	const TYPE_TEXT = 4;
	const TYPE_BOOL = 5;
	const TYPE_INTOPTION = 6;
	const TYPE_STROPTION = 7;
	const TYPE_CUSTOM = 8;
	
	protected $name;
	protected $privacy = self::PRIV_PROTECTED;
	protected $isFinal = false;
	protected $isRequired = false;
	protected $isCollection = false;
	protected $isUnsigned = false;
	protected $isOwn = false;
	protected $mode = self::MODE_DEFAULT;
	protected $type;
	protected $size = false;
	protected $custom_type = false;
	protected $default_value = null;
	protected $options = null;
	protected $comments = array();
	
	protected static $customTypes = array();
	
	public function __construct()
	{
	}
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setPrivacy($value)
	{
		$this->privacy = $value;
	}
	
	public function getPrivacy()
	{
		return $this->privacy;
	}
	
	public function setIsFinal($flag=true)
	{
		$this->isFinal = $flag;
	}
	
	public function getIsFinal()
	{
		return $this->isFinal;
	}
	
	public function setIsOwn($flag=true)
	{
		$this->isOwn = $flag;
	}
	
	public function getIsOwn()
	{
		return $this->isOwn;
	}
	
	public function setIsRequired($flag=true)
	{
		$this->isRequired = $flag;
	}
	
	public function getIsRequired()
	{
		return $this->isRequired;
	}
	
	public function setIsUnsigned($flag=true)
	{
		$this->isUnsigned = $flag;
	}
	
	public function getIsUnsigned()
	{
		$base = $this->getTypeBase();
		return $base && $base[2] ? true : $this->isUnsigned;
	}
	
	public function setIsCollection($flag=true)
	{
		$this->isCollection = $flag;
	}
	
	public function getIsCollection()
	{
		return $this->isCollection;
	}
	
	public function setMode($value)
	{
		$this->mode = $value;
	}
	
	public function getMode()
	{
		return $this->mode;
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
		return $base ? $base[0] : $this->type;
	}
	
	public function getIsCustomType()
	{
		return $this->type == self::TYPE_CUSTOM;
	}
	
	public function getSize()
	{
		$base = $this->getTypeBase();
		return $base ? $base[1] : $this->size;
	}
	
	public function getCustomType()
	{
		return $this->custom_type;
	}
	
	public function setDefaultValue($val)
	{
		if (self::TYPE_BOOL == $this->type && !is_bool($val) ||
			self::TYPE_INT == $this->type && !is_int($val) ||
			self::TYPE_DECIMAL == $this->type && !(is_float($val) || is_int($val))) {
			throw new AttributeException("Invalid default value");
		}
		if ((self::TYPE_INTOPTION == $this->type || self::TYPE_STROPTION == $this->type) && 
			!in_array($val, $this->options)) {
			throw new AttributeException("Default value is not in range of enumeration");
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
	
	public function addComment($comment)
	{
		if (preg_match('/^\s*@([a-zA-Z0-9]+)\s*(.*)$/', $comment, $m)) {
			$this->comments[$m[1]] = $m[2];
		} else {
			$this->comments[] = $comment;
		}
	}
	
	public function getComments()
	{
		return $this->comments;
	}
	
	public static function registerCustomType($name, $based_on=self::TYPE_CUSTOM, $size=false, $unsigned=false)
	{
		self::$customTypes[$name] = array($based_on, $size, $unsigned);
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
