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

require_once CF_LIB_DIR . '/FileHelper.php';

class RemoveCommand extends Command
{
	public $soft;
	public $name;
	
	public function argsmap()
	{
		return array(
			's' => 'soft',
			0 => 'name',
		);
	}
	
	public function printHelp()
	{
		printf("%s remove [-s] <name>\n", CF_SCRIPT);
		echo "Remove a model from project.\nList of options:\n";
		echo "\t-s keep source file.\n";
	}
	
	public function run()
	{
		if (empty($this->name)) {
			$this->name = $this->ask("Please specify a model name");
		}
		if (!preg_match(self::MODEL_NAME_PATTERN, $this->name)) {
			$this->say("Invalid model name: %s", $this->name);
			return;
		}
		
		$file = $this->getModelFile($this->name);
		if (!$this->isProjectHasFile($file)) {
			$this->say("Model %s is not in project", $this->name);
			return;
		}
		
		$this->removeFileFromProject($file);
		if (!$this->soft && is_file($file)) {
			FileHelper::rm($file);
		}
		$this->say("Done");
	}
}
