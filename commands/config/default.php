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

class ConfigDefaultCommand extends Command
{
	public function printHelp()
	{
		$this->printHelpGet();
		echo "\n";
		$this->printHelpSet();
		echo "\n";
		$this->printHelpUnset();
		echo "\n";
		$this->printHelpShow();
	}
	
	public function printHelpGet()
	{
		printf("%s config get <name>\n", SCRIPT);
		echo "Display project option.\n";
	}
	
	public function printHelpSet()
	{
		printf("%s config set <name> <value>\n", SCRIPT);
		echo "Set project option.\n";
	}
	
	public function printHelpUnset()
	{
		printf("%s config unset <name>\n", SCRIPT);
		echo "Unset project option.\n";
	}
	
	public function printHelpShow()
	{
		printf("%s config show\n", SCRIPT);
		echo "Display all project options.\n";
	}
	
	public function run()
	{
		$this->printHelp();
	}
}
