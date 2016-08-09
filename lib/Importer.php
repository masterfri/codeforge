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

abstract class Importer
{
	protected $_in;
	protected $_out;
	protected $_options;
	
	protected $_attribs = array();
	
	public function import($infile, $outfile, $model, $scheme='', $options=array())
	{
		$this->_in = @fopen($infile, 'r');
		if (!$this->_in) {
			throw new \Exception("Can't open file $infile");
		}
		if ($outfile == '-') {
			$this->_out = false;
		} else {
			$this->_out = @fopen($outfile, 'w');
			if (!$this->_out) {
				fclose($this->_in);
				throw new \Exception("Can't open file $outfile");
			}
		}
		$this->_options = $options;
		try {
			$this->putHeader($model, $scheme);
			$this->process();
			fclose($this->_in);
			if ($this->_out) {
				fclose($this->_out);
			}
		} catch (\Exception $e) {
			fclose($this->_in);
			if ($this->_out) {
				fclose($this->_out);
			}
			throw $e;
		}
	}
	
	abstract protected function process();
	
	protected function addAttribute($hints, $name, $type, $label)
	{
		if (null === $name) {
			$name = $this->generateName($label);
		}
		$str  = sprintf("\t /// %s\n", $label);
		foreach ($hints as $hint => $hintval) {
			$str .= sprintf("\t /// @%s %s\n", $hint, $hintval);
		}
		$str .= sprintf("\t attr %s %s;\n\n", $name, $type);
		
		if ($this->_out) {
			fwrite($this->_out, $str);
		} else {
			echo $str;
		}
	}
	
	protected function addSpacer()
	{
		if ($this->_out) {
			fwrite($this->_out, "\n");
		} else {
			echo "\n";
		}
	}
	
	protected function generateName($label)
	{
		$words = explode(' ', strtolower($label));
		$words = array_slice($words, 0, 5);
		$name = array();
		foreach ($words as $n => $word) {
			$word = preg_replace('/[^a-zA-Z0-9]/', '', $word);
			if (!empty($word)) {
				$name[] = $word;
			}
		}
		$name = implode('_', $name);
		if (isset($this->_attribs[$name])) {
			$this->_attribs[$name]++;
			$name .= $this->_attribs[$name];
		} else {
			$this->_attribs[$name] = 0;
		}
		return $name;
	}
	
	protected function putHeader($model, $scheme)
	{
		$str  = sprintf("model %s", $model);
		if (!empty($scheme)) {
			$str .= sprintf(" scheme %s", $scheme);
		}
		$str .= ":\n\n";
		if ($this->_out) {
			fwrite($this->_out, $str);
		} else {
			echo $str;
		}
	}
}
