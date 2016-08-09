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

class ConfigShowCommand extends ConfigDefaultCommand
{
	public $global;
	
	public function argsmap()
	{
		return array(
			'g' => 'global',
		);
	}
	
	public function printHelp()
	{
		$this->printHelpShow();
	}
	
	public function run()
	{
		if ($this->global) {
			$opts = $this->getGlobalConfigOption();
		} else {
			$opts = $this->getConfigOption();
		}
		foreach ($opts as $name => $value) {
			echo "$name => $value\n";
		}
	}
} 
