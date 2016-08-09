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

class AttributeCustomType
{
	protected $name;
	protected $base;
	protected $size;
	protected $unsigned;
	
	public function __construct($name, $base=Attribute::TYPE_CUSTOM, $size=false, $unsigned=false)
	{
		$this->name = $name;
		$this->base = $base;
		$this->size = $size;
		$this->unsigned = $unsigned;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getIsUnsigned()
	{
		return $this->unsigned;
	}

	public function getType()
	{
		return $this->base;
	}

	public function getSize()
	{
		return $this->size;
	}
}
