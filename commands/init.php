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

class InitCommand extends Command
{
	public $force;
	protected $templates = array();
	protected $excluded = array();
	
	protected $_requirements = array();
	
	public function argsmap()
	{
		return array(
			'f' => 'force',
		);
	}
	
	public function argrules()
	{
		return array(
			'e' => 's',
		);
	}
	
	public function acceptArg($name, $value)
	{
		if (is_int($name)) {
			$this->templates[] = $value;
			return true;
		}
		if ('e' == $name) {
			$this->excluded[] = $value;
			return true;
		}
		return false;
	}
	
	public function printHelp()
	{
		printf("%s init [-f] <template> ... <template> [-e <feature> ... -e <feature>] \n", CF_SCRIPT);
		echo "Initialize an empty project.\nList of options:\n";
		echo "\t-f cleanup old project if exists;\n";
		echo "\t-e exclude feature.\n";
	}
	
	public function run()
	{
		if (empty($this->templates)) {
			$templates = $this->ask("Please specify template");
			$this->templates = preg_split('/\s+/', $templates, -1, PREG_SPLIT_NO_EMPTY);
		}
		
		foreach ($this->templates as $template) {
			if (!$this->requireFeature($template)) {
				return;
			}
		}
		
		$dir = $this->getProjectDir();
		if (is_dir($dir)) {
			if (!$this->force) {
				if (!$this->confirm(sprintf("Project already found in `%s`. Cleanup old project?", CF_WORKDIR))) {
					return;
				}
			}
			FileHelper::cleanup($dir, true, false);
		} else {
			FileHelper::mkdir($dir);
		}
		
		foreach ($this->_requirements as $feature) {
			FileHelper::copyContents($feature, $dir);
		}
		
		FileHelper::checkdir($this->getCacheDir());
		FileHelper::checkdir($this->getCompiledDir());
		FileHelper::checkdir($this->getStaticDir());
		FileHelper::checkdir($this->getPartialDir());
		FileHelper::checkdir($this->getStaticPartialDir());
		FileHelper::checkdir($this->getSrcDir());
		FileHelper::checkdir($this->getCustomSchemeDir());
		FileHelper::checkdir($this->getCustomExtensionsDir());
		
		$pattern = $this->getSrcDir() . '/*.model';
		foreach (glob($pattern) as $file) {
			$this->addFileToProject($file);
		}
		
		$this->say("Done");
	}
	
	protected function requireFeature($feature)
	{
		if (in_array($feature, $this->excluded)) {
			$this->say("Feature is in exclude list, but it is required: %s", $feature);
			return false;
		}
		if (!isset($this->_requirements[$feature])) {
			$basedirs = $this->getTemplateDirs($feature);
			$has_pack = false;
			foreach ($basedirs as $basedir) {
				$dir =  $basedir . '/_pack';
				if (!is_dir($dir)) {
					continue;
				}
				$has_pack = true;
				$this->_requirements[$feature] = $dir;
				$requirements = $basedir . '/_deps.list';
				if (is_file($requirements)) {
					$list = new EasyConfig();
					$list->readFile($requirements);
					$data = $list->getData();
					if (isset($data['strict']) && is_array($data['strict'])) {
						foreach ($data['strict'] as $dep) {
							if (!$this->requireFeature($dep)) {
								return false;
							}
						}
					}
					if (isset($data['optional']) && is_array($data['optional'])) {
						foreach ($data['optional'] as $opt) {
							if (!in_array($opt, $this->excluded)) {
								if (!$this->requireFeature($opt)) {
									return false;
								}
							}
						}
					}
				}
			}
			if (!$has_pack) {
				$this->say("Unknow feature: %s", $feature);
				return false;
			}
		}
		return true;
	}
}
