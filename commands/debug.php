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

require_once CF_LIB_DIR . '/Parser.php';
require_once CF_LIB_DIR . '/Builder.php';

class DebugCommand extends Command
{
	protected $names = array();
	
	public function printHelp()
	{
		printf("%s debug [model1] [model2] ...\n", CF_SCRIPT);
		echo "Print debugging output.\n";
	}
	
	public function acceptArg($name, $value)
	{
		if (is_int($name)) {
			$this->names[] = $value;
			return true;
		}
		return false;
	}
	
	public function run()
	{
		if (empty($this->names)) {
			$input = $this->getProjectFiles();
		} else {
			$input = array();
			foreach ($this->names as $name) {
				if (!preg_match(self::MODEL_NAME_PATTERN, $name)) {
					$this->say("Invalid model name: %s", $name);
					return;
				}
				$file = $this->getModelFile($name);
				if (!$this->isProjectHasFile($file)) {
					$this->say("Model %s is not in project", $name);
					return;
				}
				$input[] = $file;
			}
		}
		if (empty($input)) {
			$this->say("Nothing to debug");
			return;
		}
		foreach ($input as $infile) {
			$parser = new Parser();
			$parser->parseFile($infile);
			$this->say(print_r($parser->getModels(), true));
		}
		$this->say("Done");
	}
}
