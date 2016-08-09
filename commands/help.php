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

class HelpCommand extends Command
{
	public $command;
	
	public function argsmap()
	{
		return array(
			0 => 'command',
		);
	}
	
	public function run()
	{
		if (empty($this->command)) {
			$this->say("Please specify a command");
		} else {
			$commands = array();
			foreach (preg_split('/\s+/', $this->command, -1, PREG_SPLIT_NO_EMPTY) as $command) {
				if (!preg_match(self::COMMAND_NAME_PATTERN, $command)) {
					break;
				}
				$commands[] = $command; 
			}
			try {
				
				$c = Command::factory($commands);
				$c->printHelp();
			} catch (Exception $e) {
				$this->say("Unknow or invalid command: %s", implode(' ', $commands));
			}
		}
	}
}
