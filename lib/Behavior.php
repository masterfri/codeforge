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

class Behavior extends Entity
{
	protected $params = array();

	public function setParams(array $params)
	{
		foreach ($params as $key => $value) {
			$this->setParam($key, $value);
		}
	}
	
	public function setParam($name, $value)
	{
		if (null === $value) {
			unset($this->params[$name]);
		} else {
			$this->params[$name] = $value;
		}
	}
	
	public function getParam($name=null, $default=null)
	{
		if (null === $name) {
			return $this->params;
		}
		return isset($this->params[$name]) ? $this->params[$name] : $default;
	}
}
