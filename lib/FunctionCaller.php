<?php

class FunctionCaller
{
	protected $name;
	
	public function __construct($name)
	{
		$this->name = $name;
	}
	
	public function call($args)
	{
		return call_user_func_array($this->name, $args);
	}
}
