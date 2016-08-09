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

class EasyConfig
{
	protected $data;
	
	/**
	 * @param $data array Initial data
	 */ 
	public function __construct(array $data=array())
	{
		$this->data = $data;
	}
	
	/**
	 * Read data from file
	 * @param $name string
	 * @return array
	 */ 
	public function readFile($name)
	{
		$f = @fopen($name, 'r');
		if (! is_resource($f)) {
			throw new EasyConfig_Exception("Can't open file $name");
		}
		
		$current_collection = &$this->data;
		$collection_stack = array(&$this->data);
		$last_entry = null;
		$prev_is_string = false;
		$prev_is_struct = false;
		$current_indent = false;
		$indent_stack = array();
		$stack_size = 1;
		$line_number = 0;
		
		while (! feof($f)) {
			$str = rtrim(fgets($f));
			$line_number++;
			
			// get indent size
			$indent = strspn($str, " \t");
			
			$str = substr($str, $indent);
			
			if ('' == $str || '#' == $str{0}) {
				// it's an empty line or a comment
				continue;
			} elseif ("'" == $str{0} || '+' == $str{0}) {
				if ($prev_is_string) {
					// continue previouse string
					$last_entry .= ('+' == $str{0} ? '' : "\n") . (strlen($str) > 1 ?  substr($str, 1) : '');
					continue;
				} else {
					throw new EasyConfig_Exception("Unexpected string in line $line_number");
				}
			}
			
			if ($indent !== $current_indent) {
				if ($current_indent === false) {
					// first time indent counted
					$current_indent = $indent;
					$indent_stack[] = $indent;
				} elseif ($indent > $current_indent) {
					if ($prev_is_struct) {
						// go into strucutre
						$indent_stack[] = $indent;
						$current_indent = $indent;
						$collection_stack[] = &$last_entry;
						$current_collection = &$collection_stack[$stack_size];
						$stack_size++;
					} else {
						throw new EasyConfig_Exception("Invalid indent strucure in line $line_number");
					}
				} else {
					// go out of struct
					do {
						array_pop($indent_stack);
						array_pop($collection_stack);
						$stack_size--;
						if ($stack_size < 1) {
							throw new EasyConfig_Exception("Invalid indent strucure in line $line_number");
						}
						if ($indent_stack[$stack_size - 1] == $indent) {
							$current_indent = $indent;
							$current_collection = &$collection_stack[$stack_size - 1];
							break;
						}
					} while (true);
				}
			}
			
			$prev_is_string = false;
			$prev_is_struct = false;
			
			// read identifier
			if ('`' == $str{0} || '"' == $str{0}) {
				// Composite identifier
				$identifier_len = strcspn($str, $str{0}, 1);
				$identifier = substr($str, 1, $identifier_len);
				$identifier_len += 2;
			} else {
				// Simple identifier
				$identifier_len = strcspn($str, " \t");
				$identifier = substr($str, 0, $identifier_len);
				if ('@' == $identifier) {
					// autoindex
					$current_collection[] = 0;
					end($current_collection);
					$identifier = key($current_collection);
				}
			}
			
			// read value
			$skip = strspn($str, " \t", $identifier_len);
			$value = substr($str, $skip + $identifier_len);
			
			// resolve value
			if ('' == $value) {
				$current_collection[$identifier] = array();
				$prev_is_struct = true;
			} elseif ("'" == $value{0}) {
				$current_collection[$identifier] = strlen($value) > 1 ?  substr($value, 1) : '';
				$prev_is_string = true;
			} else {
				$value = strtolower($value);
				if ('true' == $value) {
					// boolean TRUE
					$current_collection[$identifier] = true;
				} elseif ('false' == $value) {
					// boolean FALSE
					$current_collection[$identifier] = false;
				} elseif ('null' == $value) {
					// NULL
					$current_collection[$identifier] = null;
				} elseif (is_numeric($value)) {
					if (ctype_digit($value)) {
						// Integer
						$current_collection[$identifier] = intval($value);
					} else {
						// Double
						$current_collection[$identifier] = floatval($value);
					}
				} else {
					throw new EasyConfig_Exception("Invalid value `$value` in line $line_number");
				}
			}
			
			// remember last entry
			$last_entry = &$current_collection[$identifier];
		}
		
		fclose($f);
		
		return $this->data;
	}
	
	/**
	 * @param $path string
	 * @param $indent string
	 */
	public function writeToFile($path, $indent="\t")
	{
		if (@!file_put_contents($path, $this->toString($indent))) {
			throw new EasyConfig_Exception("Can't write to file $path");
		}
	}
	
	/**
	 * @return array
	 */ 
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * @param $indent string
	 * @return string
	 */ 
	public function toString($indent="\t")
	{
		return $this->toStr($this->data, $indent, 0);
	}
	
	/**
	 * @param $collection array
	 * @param $indent string
	 * @param $level integer
	 * @return string
	 */ 
	protected function toStr(&$collection, $indent, $level)
	{
		if (empty($collection)) {
			return str_repeat($indent, $level) . "# Empty \n";
		} else {
			$lines = '';
			foreach ($collection as $key => $value) {
				$key = $this->quoteKey($key);
				$line = str_repeat($indent, $level) . $key;
				if (is_array($value)) {
					$line .= "\n" . $this->toStr($value, $indent, $level + 1);
				} elseif (is_null($value)) {
					$line .= " null\n";
				} elseif (is_bool($value)) {
					$line .= $value ? " true\n" : " false\n";
				} elseif (is_string($value)) {
					if (strpos($value, "\n") === false) {
						$line .= " '$value\n";
					} else {
						$spaces = strlen($key);
						$parts = explode("\n", $value);
						$part = array_shift($parts);
						$line .= " '$part\n";
						foreach ($parts as $part) {
							$line .= str_repeat($indent, $level) . str_repeat(' ', $spaces) . " '$part\n";
						}
					}
				} else {
					$line .= " $value\n";
				}
				$lines .= $line;
			}
			return $lines;
		}
	}
	
	/**
	 * Quote the key
	 * @param $key string
	 * @return string
	 */ 
	protected function quoteKey($key)
	{
		if (strpos($key, ' ') !== false || strpos($key, "\t") !== false) {
			if (false === strpos($key, '"')) {
				return "\"$key\"";
			} else {
				return "`$key`";
			}
		} else {
			return $key;
		}
	}
}

class EasyConfig_Exception extends \Exception
{}

