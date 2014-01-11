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

require_once LIB_DIR . '/Lex.php';
require_once LIB_DIR . '/Generator.php';

class CompileCommand extends Command
{
	protected $names = array();
	
	public function printHelp()
	{
		printf("%s compile [model1] [model2] ...\n", SCRIPT);
		echo "Compile source files.\n";
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
			$this->say("Nothing to compile");
			return;
		}
		$options = $this->getConfigOption();
		foreach ($input as $infile) {
			$lex = new Lex();
			$generator = new Generator($this);
			$generator->setSchemesDir(array(
				$this->getDefaultSchemeDir(),
				$this->getCustomSchemeDir(),
			));
			$generator->setExtensionsDir(array(
				$this->getDefaultExtensionsDir(),
				$this->getCustomExtensionsDir(),
			));
			$generator->setCacheDir($this->getCacheDir());
			$generator->setPartialDir($this->getPartialDir());
			$generator->setEnv($options);
			$lex->parse($infile);
			$generator->compile($lex, $this->getCompiledDir());
		}
		$this->say("Done");
	}
}
