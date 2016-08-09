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

class BuildCommand extends Command
{
	protected $schemes = array();
	
	public function acceptArg($name, $value)
	{
		if (is_int($name)) {
			$this->schemes[] = $value;
			return true;
		}
		return false;
	}
	
	public function printHelp()
	{
		printf("%s build [scheme] ... \n", CF_SCRIPT);
		echo "Build project.\n";
	}

	public function run()
	{
		$input = $this->getProjectFiles();
		if (empty($input)) {
			$this->say("Nothing to compile");
			return;
		}
		$options = $this->getConfigOption();
		$generator = new Builder($this);
		$generator->setSchemesDir($this->filterDirs(array(
			$this->getDefaultSchemeDir(),
			$this->getUserSchemeDir(),
			$this->getCustomSchemeDir(),
		)));
		$generator->setExtensionsDir($this->filterDirs(array(
			$this->getDefaultExtensionsDir(),
			$this->getUserExtensionsDir(),
			$this->getCustomExtensionsDir(),
		)));
		$generator->setCacheDir($this->getCacheDir());
		$generator->setPartialDir($this->getPartialDir());
		$generator->setStaticPartialDir($this->getStaticPartialDir());
		$generator->setEnv($options);
		$models = array();
		foreach ($input as $infile) {
			$parser = new Parser();
			$parser->parseFile($infile);
			foreach ($parser->getModels() as $model) {
				$models[] = $model;
			}
		}
		$generator->setModels($models);
		$generator->compile($this->getCompiledDir());
		if (count($this->schemes)) {
			$generator->setMode('_build');
			$generator->build($this->schemes, $this->getCompiledDir());
		}
		$this->say("Done");
	}
}
