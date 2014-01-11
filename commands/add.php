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

class AddCommand extends Command
{
	public $force;
	public $restore;
	public $name;
	
	public function argsmap()
	{
		return array(
			'f' => 'force',
			'r' => 'restore',
			0 => 'name',
		);
	}
	
	public function printHelp()
	{
		printf("%s add [-f|-r] <name>\n", SCRIPT);
		echo "Add a model to project.\nList of options:\n";
		echo "\t-f replace old model if exists.\n";
		echo "\t-r restore old model if exists.\n";
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
				$this->generateBlank($file, $this->name);
			}
		} else {
			$this->generateBlank($file, $this->name);
		}
		$this->addFileToProject($file);
		$this->say("Done");
	}
	
	protected function generateBlank($file, $name)
	{
		$scheme = $this->getConfigOption('schemes', '');
		
		$str  = sprintf("model %s", $name);
		if (!empty($scheme)) {
			$str .= sprintf(" scheme %s", $scheme);
		}
		$str .= ":\n\n";
		
		if (!@file_put_contents($file, $str)) {
			throw new Exception("Can't write file `$file`");
		}
	}
}
