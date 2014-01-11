<?php

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
