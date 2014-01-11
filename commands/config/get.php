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

require_once dirname(__FILE__) . '/default.php';

class ConfigGetCommand extends ConfigDefaultCommand
{
	public $name;
	public $less;
	
	public function argsmap()
	{
		return array(
			0 => 'name',
			'l' => 'less',
		);
	}
	
	public function printHelp()
	{
		$this->printHelpGet();
	}
	
	public function run()
	{
		if (empty($this->name)) {
			$this->name = $this->ask("Please specify an option name");
		}
		
		$value = $this->getConfigOption($this->name);
		
		if (!is_null($value)) {
			if ($this->less) {
				echo $value;
			} else {
				echo "{$this->name} => {$value}\n";
			}
		} elseif (!$this->less) {
			echo "Not set\n";
		}
	}
} 
