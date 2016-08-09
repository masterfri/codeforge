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

class AddCommand extends Command
{
	public $force;
	public $restore;
	public $name;
	public $template = 'blank.default';
	public $import;
	
	public function argsmap()
	{
		return array(
			'f' => 'force',
			'r' => 'restore',
			't' => 'template',
			'i' => 'import',
			0 => 'name',
		);
	}
	
	public function argrules()
	{
		return array(
			'template' => 's',
			't' => 's',
		);
	}
	
	public function printHelp()
	{
		printf("%s add [-f|-r] [-t template] <name>\n", CF_SCRIPT);
		printf("%s add -i\n", CF_SCRIPT);
		echo "Add a model to project.\nList of options:\n";
		echo "\t-f replace old model if exists.\n";
		echo "\t-r restore old model if exists.\n";
		echo "\t-i import models from `src` directory.\n";
	}
	
	public function run()
	{
		if ($this->import) {
			if (!empty($this->name)) {
				$this->say("Key -i can't be used with <name> argument");
				return;
			}
			
			$pattern = $this->getSrcDir() . '/*.model';
			foreach (glob($pattern) as $file) {
				if (!$this->isProjectHasFile($file)) {
					$this->addFileToProject($file);
					$this->say("Added model: %s", basename($file, '.model'));
				}
			}
		} else {
			if (empty($this->name)) {
				$this->name = $this->ask("Please specify a model name");
			}
			if (!preg_match(self::MODEL_NAME_PATTERN, $this->name)) {
				$this->say("Invalid model name: %s", $this->name);
				return;
			}
			
			$file = $this->getModelFile($this->name);
			if ($this->isProjectHasFile($file)) {
				$this->say("Model %s already added to project", $this->name);
				return;
			}
			
			if (is_file($file)) {
				if (!$this->force && !$this->restore) {
					$answer = strtolower($this->ask("Model file already presents. [o]verwrite/[r]estore/[S]kip?", true));
					if ($answer == 'o') {
						$this->force = true;
					} elseif ($answer == 'r') {
						$this->restore = true;
					} else {
						return;
					}
				}
				if ($this->force) {
					if (!$this->generateBlank($file, $this->name, $this->template)) {
						return;
					}
				}
			} else {
				if (!$this->generateBlank($file, $this->name, $this->template)) {
					return;
				}
			}
			$this->addFileToProject($file);
		}
		$this->say("Done");
	}
	
	protected function generateBlank($file, $name, $template)
	{
		$tmplfile = $this->getBlank($template);
		if (!$tmplfile) {
			$this->say("Unknow template: %s", $template);
			return false;
		}
		$content = file_get_contents($tmplfile);
		$content = strtr($content, array(
			'{{model}}' => $name,
		));
		if (!@file_put_contents($file, $content)) {
			throw new Exception("Can't write file `$file`");
		}
		return true;
	}
}
