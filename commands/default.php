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

class DefaultCommand extends Command
{
	public $help;
	public $version;
	
	public function argsmap()
	{
		return array(
			'h' => 'help',
			'v' => 'version',
		);
	}
	
	public function run()
	{
		if ($this->version) {
			$this->printVersion();
		}
		if ($this->help) {
			$this->printHelp();
		}
		if (!$this->help && !$this->version) {
			$this->printVersion();
			echo "\n";
			$this->printHelp();
		}
	}
	
	public function printHelp()
	{
		echo "Usage:\n\n";
		printf("%s <command> [args]\n\n", CF_SCRIPT);
		printf("Type %s help <command> for more information on a specific command.\n", CF_SCRIPT);
	}
	
	public function printVersion()
	{
		printf("%s v%s\n", CF_PROGRAMM, CF_VERSION);
		echo "by Grigory Ponomar <http://masterfri.org.ua>.\n";
	}
}
