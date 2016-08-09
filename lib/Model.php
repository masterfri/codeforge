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
require_once CF_LIB_DIR . '/Attribute.php';
require_once CF_LIB_DIR . '/Behavior.php';

class Model extends Entity
{
	protected $attribs = array();
	protected $behaviors = array();
	protected $attribs_natural_order = array();
	protected $schemes = array();
	protected $references = array();
	
	public function addScheme($name)
	{
		$this->schemes[$name] = true;
	}
	
	public function removeScheme($name=null)
	{
		if ($name) {
			unset($this->schemes[$name]);
		} else {
			$this->schemes = array();
		}
	}
	
	public function getSchemes()
	{
		return array_keys($this->schemes);
	}
	
	public function addAttribute(Attribute $attr)
	{
		$name = $attr->getName();
		if (isset($this->attribs[$name])) {
			throw new ModelException("attribute `$name` is defined more than once");
		}
		$this->attribs[$name] = $attr;
		ksort($this->attribs);
		$this->attribs_natural_order[] = $name;
		$attr->setOwner($this);
	}
	
	public function removeAttribute($name=null)
	{
		if ($name) {
			unset($this->attribs[$name]);
			$index = array_search($name, $this->attribs_natural_order);
			if (false !== $index) {
				unset($this->attribs_natural_order[$index]);
			}
		} else {
			$this->attribs = array();
			$this->attribs_natural_order = array();
		}
	}
	
	public function getAttributes($sorted=true)
	{
		if ($sorted) {
			return $this->attribs;
		} else {
			$result = array();
			foreach ($this->attribs_natural_order as $name) {
				$result[$name] = $this->attribs[$name];
			}
			return $result;
		}
	}
	
	public function getAttributeNames($sorted=true)
	{
		if ($sorted) {
			return array_keys($this->attribs);
		} else {
			return $this->attribs_natural_order;
		}
	}
	
	public function hasAttribute($name)
	{
		return isset($this->attribs[$name]);
	}
	
	public function addBehavior(Behavior $behavior)
	{
		$name = $behavior->getName();
		if (isset($this->behaviors[$name])) {
			throw new ModelException("behavior `$name` is defined more than once");
		}
		$this->behaviors[$name] = $behavior;
		$behavior->setOwner($this);
	}
	
	public function removeBehavior($name=null)
	{
		if ($name) {
			unset($this->behaviors[$name]);
		} else {
			$this->behaviors = array();
		}
	}
	
	public function getBehaviors()
	{
		return $this->behaviors;
	}
	
	public function hasBehavior($name)
	{
		return isset($this->behaviors[$name]);
	}
	
	public function setReferences(array $references)
	{
		$this->clearReferences();
		foreach ($references as $attribute) {
			$this->addReference($attribute);
		}
	}
	
	public function clearReferences()
	{
		$this->references = array();
	}
	
	public function addReference(Attribute $attribute)
	{
		if ($attribute->getType() != Attribute::TYPE_CUSTOM) {
			throw new ModelException('Only custom type can be a reference');
		}
		if ($attribute->getCustomType() != $this->getName()) {
			throw new ModelException(sprintf('Type of reference is mismatched (%s != %s)', $attribute->getCustomType(), $this->getName()));
		}
		$this->references[] = $attribute;
	}
	
	public function getReferences($from=null)
	{
		if (null === $from) {
			return $this->references;
		}
		if ($from instanceof Model) {
			$from = $from->getName();
		}
		$result = array();
		foreach ($this->references as $attribute) {
			if ($attribute->getOwner()->getName() == $from) {
				$result[] = $attribute;
			}
		}
		return $result;
	}
}
