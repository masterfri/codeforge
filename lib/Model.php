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

require_once LIB_DIR . '/Attribute.php';

class Model
{
	protected $name;
	protected $isAbstract = false;
	protected $isSingle = false;
	protected $extends = array();
	protected $implements = array();
	protected $attribs = array();
	protected $attribs_natural_order = array();
	protected $schemes = array();
	protected $comments = array();
	
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
	
	public function setIsAbstract($flag=true)
	{
		$this->isAbstract = $flag;
	}
	
	public function getIsAbstract()
	{
		return $this->isAbstract;
	}
	
	public function setIsSingle($flag=true)
	{
		$this->isSingle = $flag;
	}
	
	public function getIsSingle()
	{
		return $this->isSingle;
	}
	
	public function addSupermodel($name)
	{
		$this->extends[$name] = true;
	}
	
	public function removeSupermodel($name)
	{
		if ($name) {
			unset($this->extends[$name]);
		} else {
			$this->extends = array();
		}
	}
	
	public function getSupermodels()
	{
		return array_keys($this->extends);
	}
	
	public function getIsSubclass()
	{
		return ! empty($this->extends);
	}
	
	public function addInterface($name)
	{
		$this->implements[$name] = true;
	}
	
	public function removeInterface($name)
	{
		if ($name) {
			unset($this->implements[$name]);
		} else {
			$this->implements = array();
		}
	}
	
	public function getInterfaces()
	{
		return array_keys($this->implements);
	}
	
	public function getIsImplementation()
	{
		return ! empty($this->implements);
	}
	
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
			throw new ModelException("attribute `$name` duplicated");
		}
		$this->attribs[$name] = $attr;
		ksort($this->attribs);
		$this->attribs_natural_order[] = $name;
	}
	
	public function removeAttribute($name=null)
	{
		if ($name) {
			unset($this->attribs[$name]);
		} else {
			$this->attribs = array();
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
}
