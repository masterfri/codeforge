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

class BuildCommand extends Command
{
	protected $schemes = array();
	public $skip_compilation;
	
	public function argsmap()
	{
		return array(
			'skip-compilation' => 'skip_compilation',
			'k' => 'skip_compilation',
		);
	}
	
	public function printHelp()
	{
		printf("%s build [-k] <scheme1> [scheme2] ...\n", SCRIPT);
		echo "Build source files.\nList of options:\n";
		echo "\t-k - skip compilation\n";
	}
	
	public function acceptArg($name, $value)
	{
		if (is_int($name)) {
			$this->schemes[] = $value;
			return true;
		}
		return false;
	}
	
	public function run()
	{
		if (empty($this->schemes)) {
			$this->say("At least one scheme is required");
			return;
		}
		
		$options = $this->getConfigOption();
		
		if (!$this->skip_compilation) {
			$input = $this->getProjectFiles();
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
		}
		
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
		$generator->setMode('_build');
		$generator->setEnv($options);
		$generator->build($this->schemes, $this->getCompiledDir());
		$this->say("Done");
	}
}
