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

require_once LIB_DIR . '/FileHelper.php';

class InitCommand extends Command
{
	public $force;
	public $template;
	
	public function argsmap()
	{
		return array(
			'f' => 'force',
			0 => 'template',
		);
	}
	
	public function printHelp()
	{
		printf("%s init [-f] <template>\n", SCRIPT);
		echo "Initialize an empty project.\nList of options:\n";
		echo "\t-f cleanup old project if exists.\n";
	}
	
	public function run()
	{
		if (empty($this->template)) {
			$this->template = $this->ask("Please specify template");
		}
		$templatedir = $this->getTemplateDir($this->template);
		if (!is_dir($templatedir)) {
			$this->say("Unknow template: %s", $this->template);
			return;
		}
		
		$dir = $this->getProjectDir();
		if (is_dir($dir)) {
			if (!$this->force) {
				if (!$this->confirm(sprintf("Project already found in `%s`. Cleanup old project?", WORKDIR))) {
					return;
				}
			}
			FileHelper::cleanup($dir, true, false);
		} else {
			FileHelper::mkdir($dir);
		}
		
		FileHelper::copyContents($templatedir, $dir);
		FileHelper::checkdir($this->getCacheDir());
		FileHelper::checkdir($this->getCompiledDir());
		FileHelper::checkdir($this->getStaticDir());
		FileHelper::checkdir($this->getPartialDir());
		FileHelper::checkdir($this->getSrcDir());
		FileHelper::checkdir($this->getCustomSchemeDir());
		FileHelper::checkdir($this->getCustomExtensionsDir());
		
		$this->say("Done");
	}
}
