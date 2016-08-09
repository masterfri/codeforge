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

require_once CF_LIB_DIR . '/AbstractParser.php';

class CfonParser extends AbstractParser
{
	protected function start()
	{
		return $this->parseObject();
	}
	
	protected function parseObject()
	{
		$data = array();
		$this->expect(self::TOK_LFBRACKET);
		if (self::TOK_RFBRACKET == $this->lookAhead()) {
			$this->getToken();
		} else {
			while (true) {
				$this->expect(self::TOK_IDENTIFIER, $tok_value);
				$this->expect(self::TOK_ASSIGN);
				$data[$tok_value] = $this->parseValue();
				if (self::TOK_RFBRACKET == $this->expect(array(self::TOK_RFBRACKET, self::TOK_COMMA))) {
					break;
				}
			}
		}
		return $data;
	}
	
	protected function parseArray()
	{
		$data = array();
		$this->expect(self::TOK_LSBRACKET);
		if (self::TOK_RSBRACKET == $this->lookAhead()) {
			$this->getToken();
		} else {
			while (true) {
				$data[] = $this->parseValue();
				if (self::TOK_RSBRACKET == $this->expect(array(self::TOK_RSBRACKET, self::TOK_COMMA))) {
					break;
				}
			}
		}
		return $data;
	}
	
	protected function parseValue()
	{
		$tok = $this->lookAhead();
		if (self::TOK_LFBRACKET == $tok) {
			return $this->parseObject();
		} elseif (self::TOK_LSBRACKET == $tok) {
			return $this->parseArray();
		} else {
			$tok = $this->getToken($tok_value);
			switch ($tok) {
				case self::TOK_NUMBER:
					return (int) $tok_value;
				case self::TOK_DECIMAL_NUMBER:
					return (float) $tok_value;
				case self::TOK_STRING:
					return $tok_value;
				case self::TOK_TRUE:
					return true;
				case self::TOK_FALSE:
					return false;
				default:
					$this->unexpected($tok);
					break;
			}
		}
	}
}
