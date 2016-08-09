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

require_once CF_LIB_DIR . '/Tokenizer.php';

class FileTokenizer extends Tokenizer
{
	protected $file;
	
	public function __construct($file, $tokens=array(), $unescape=array())
	{
		$text = @file_get_contents($file);
		if (false === $text) {
			throw new \Exception("File not exists: $file");
		}
		$this->file = $file;
		parent::__construct($text, $tokens, $unescape);
	}
	
	public function getFile()
	{
		return $this->file;
	}
	
	public function getCurrentPlace()
	{
		return sprintf('%s at line %s', $this->getFile(), $this->getLine());
	}
}
