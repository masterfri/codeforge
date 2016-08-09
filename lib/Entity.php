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

abstract class Entity
{
	protected $owner;
	protected $name;
	protected $comments = array();
	
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
	
	public function getOwner()
	{
		return $this->owner;
	}

	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function addComments(array $comments)
	{
		foreach ($comments as $comment) {
			$this->addComment($comment);
		}
	}
	
	public function addComment($comment)
	{
		if (preg_match('/^\s*@([a-zA-Z0-9_]+)\s*(.*)$/', $comment, $m)) {
			$this->comments[strtolower($m[1])] = $m[2];
		} else {
			$this->comments[] = $comment;
		}
	}
	
	public function getComments()
	{
		return $this->comments;
	}
	
	public function getHint($name, $default=null)
	{
		return isset($this->comments[$name]) ? $this->comments[$name] : $default;
	}
	
	public function getBoolHint($name, $default=false)
	{
		$hintval = $this->getHint($name);
		if (null === $hintval) {
			return $default;
		}
		return in_array(strtolower(trim($hintval)), array('', '1', 'yes', 'true'));
	}
}
