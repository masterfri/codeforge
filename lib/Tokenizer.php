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

class Tokenizer
{
	protected $tokens = array();
	protected $text;
	protected $line;
	protected $unescape = array();
	
	public function __construct($text=null, $tokens=array(), $unescape=array())
	{
		$this->setText($text);
		$this->setTokens($tokens);
		$this->setUnescape($unescape);
	}
	
	public function setText($text)
	{
		$this->line = 1;
		$this->text = $text;
	}
	
	public function setTokens($tokens)
	{
		$this->tokens = $tokens;
	}
	
	public function setUnescape($unescape)
	{
		$this->unescape = $unescape;
	}
	
	public function getToken(&$value=null, $skip_none=true)
	{
		while (true) {
			$token = $this->getLex($this->text, $value, $scanned);
			$this->text = substr($this->text, strlen($scanned));
			$this->line += substr_count($scanned, "\n");
			if ($skip_none && AbstractParser::TOK_NONE == $token) {
				continue;
			}
			return $token;
		}
	}
	
	public function lookAhead(&$value=null, $skip_none=true)
	{
		$text = $this->text;
		while (true) {
			$token = $this->getLex($text, $value, $scanned);
			$text = substr($text, strlen($scanned));
			if ($skip_none && AbstractParser::TOK_NONE == $token) {
				continue;
			}
			return $token;
		}
	}
	
	protected function getLex($text, &$value, &$scanned)
	{
		$value = '';
		$scanned = '';
		if (! empty($text)) {
			foreach ($this->tokens as $pattern => $token) {
				$re = "/^($pattern)/i";
				if (preg_match($re, $text, $match)) {
					$scanned = $match[0];
					if (is_array($token)) {
						list($token, $index) = $token;
						$value = isset($match[$index]) ? $match[$index] : '';
					} else {
						$value = $match[0];
					}
					if (in_array($token, $this->unescape)) {
						$value = $this->unescapeString($value);
					}
					return $token;
				}
			}
		}
		return AbstractParser::TOK_ENDSTREAM;
	}
	
	public function unescapeString($str)
	{
		$result = '';
		$len = strlen($str);
		$i = 0;
		while ($i < $len) {
			if ('\\' == $str{$i}) {
				$i++;
				switch ($str{$i}) {
					case 'n': $result .= "\n"; break;
					case 'r': $result .= "\r"; break;
					case 't': $result .= "\t"; break;
					default: $result .= $str{$i};
				}
			} else {
				$result .= $str{$i};
			}
			$i++;
		}
		return $result;
	}
	
	public function getLine()
	{
		return $this->line;
	}
	
	public function getCurrentPlace()
	{
		return sprintf('raw code at line %s', $this->getLine());
	}
}
