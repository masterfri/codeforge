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

require_once dirname(__FILE__) . '/default.php';

class ConfigSetCommand extends ConfigDefaultCommand
{
	public $name;
	public $value;
	public $global;
	
	public function argsmap()
	{
		return array(
			0 => 'name',
			1 => 'value',
			'g' => 'global',
		);
	}
	
	public function printHelp()
	{
		$this->printHelpSet();
	}
	
	public function run()
	{
		if (empty($this->name)) {
			$this->name = $this->ask("Please specify an option name");
		}
		
		if (is_null($this->value)) {
			$this->value = $this->ask("Please specify an option value", true);
		}
		
		if ($this->global) {
			$this->setGlobalConfigOption($this->name, $this->value);
		} else {
			$this->setConfigOption($this->name, $this->value);
		}
	}
} 
