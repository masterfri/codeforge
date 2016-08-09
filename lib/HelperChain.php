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

class HelperChain
{
	protected $links = array();
	protected $priorities = array();
	protected $sorted = true;
	
	public function add($item, $priority)
	{
		$minpriority = end($this->priorities);
		$this->links[] = $item;
		$this->priorities[] = $priority;
		if (count($this->links) > 1 && $minpriority < $priority) {
			$this->sorted = false;
		}
	}
	
	public function get()
	{
		if (! $this->sorted) {
			$this->sort();
		}
		return $this->links;
	}
	
	protected function sort()
	{
		array_multisort($this->priorities, SORT_NUMERIC, SORT_DESC, $this->links);
		$this->sorted = true;
	}
}
