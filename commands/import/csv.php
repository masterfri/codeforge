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

require_once CF_LIB_DIR . '/ImporterCsv.php';

class ImportCsvCommand extends Command
{
	public $force;
	public $name;
	public $file;
	
	public function argsmap()
	{
		return array(
			'f' => 'force',
			0 => 'name',
			1 => 'file',
		);
	}
	
	public function printHelp()
	{
		printf("%s import csv <name> <file>\n", CF_SCRIPT);
		echo "Import model from csv file.\n";
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
		
		if (empty($this->file)) {
			$this->file = $this->ask("Please specify file name to import from");
		}
		if (!is_file($this->file) || !is_readable($this->file)) {
			$this->say("Can't open import file: `%s`", $this->file);
			return;
		} 
		
		$file = $this->getModelFile($this->name);
		if (!$this->force && $this->isProjectHasFile($file)) {
			if (!$this->confirm(sprintf("Model %s already added to project. Overwrite it?", $this->name))) {
				return;
			}
		}
		
		$scheme = $this->getConfigOption('schemes', '');
		$importer = new ImporterCsv();
		$importer->import($this->file, $file, $this->name, $scheme);
		$this->addFileToProject($file);
		$this->say("Done");
	}
}
