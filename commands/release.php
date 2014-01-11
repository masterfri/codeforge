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

class ReleaseCommand extends Command
{
	public $output = WORKDIR;
	
	public function argsmap()
	{
		return array(
			'o' => 'output',
		);
	}
	
	public function argrules()
	{
		return array(
			'output' => 's',
			'o' => 's',
		);
	}
	
	public function printHelp()
	{
		printf("%s release [-o <dir>]\n", SCRIPT);
		echo "Release project files.\nList of options:\n";
		echo "\t-o - output directory\n";
	}
	
	public function run()
	{
		FileHelper::copyContents($this->getCompiledDir(), $this->output);
		FileHelper::copyContents($this->getStaticDir(), $this->output);
		$this->say("Done");
	}
}
