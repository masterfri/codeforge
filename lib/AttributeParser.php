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
require_once CF_LIB_DIR . '/Attribute.php';

class AttributeParser extends AbstractParser
{
	protected function start()
	{
		$attribute = new Attribute();
		$this->expect(self::TOK_ATTRIBUTE);
		$this->expect(self::TOK_IDENTIFIER, $tok_value);
		$attribute->setName($tok_value);
		$this->parseAttributeType($attribute);
		if (!$attribute->getIsCollection()) {
			if (self::TOK_ASSIGN == $this->expect(array(self::TOK_SEMICOLON, self::TOK_ASSIGN))) {
				$this->parseAttributeDefaultValue($attribute);
				$this->expect(self::TOK_SEMICOLON);
			}
		} else {
			$this->expect(self::TOK_SEMICOLON);
		}
		return $attribute;
	}

	protected function parseAttributeType($attribute)
	{
		$token = $this->getToken($tok_value);
		if ($token == self::TOK_COLLECTION) {
			$attribute->setIsCollection();
			$token = $this->getToken($tok_value);
		}
		if ($token == self::TOK_UNSIGNED) {
			$attribute->setIsUnsigned();
			$token = $this->getToken($tok_value);
		}
		if ($token == self::TOK_IDENTIFIER) {
			$attribute->setCustomType($tok_value);
			return Attribute::TYPE_CUSTOM;
		}
		switch ($token) {
			case self::TOK_INTEGER:
				$type = Attribute::TYPE_INT;
				break;
			case self::TOK_DECIMAL:
				$type = Attribute::TYPE_DECIMAL;
				break;
			case self::TOK_CHAR:
				$type = Attribute::TYPE_CHAR;
				break;
			case self::TOK_TEXT:
				$type = Attribute::TYPE_TEXT;
				break;
			case self::TOK_BOOL:
				$type = Attribute::TYPE_BOOL;
				break;
			case self::TOK_INTOPTION:
				$type = Attribute::TYPE_INTOPTION;
				break;
			case self::TOK_STROPTION:
				$type = Attribute::TYPE_STROPTION;
				break;
			default:
				$this->unexpected($token);
		}
		if (Attribute::TYPE_INT == $type || Attribute::TYPE_CHAR == $type) {
			$size = $this->parseSize();
			$attribute->setType($type, $size);
		} elseif (Attribute::TYPE_DECIMAL == $type) {
			$size = $this->parseSizeDecimal();
			$attribute->setType($type, $size);
		} elseif (Attribute::TYPE_INTOPTION == $type) {
			$options = $this->parseIntOptions();
			$attribute->setType($type);
			$attribute->setOptions($options);
		} elseif (Attribute::TYPE_STROPTION == $type) {
			$options = $this->parseStrOptions();
			$attribute->setType($type);
			$attribute->setOptions($options);
		} else {
			$attribute->setType($type);
		}
		return $type;
	}
	
	protected function parseSize()
	{
		$token = $this->lookAhead();
		if (self::TOK_LBRACKET == $token) {
			$this->getToken();
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size = (int) $tok_value;
			$this->expect(self::TOK_RBRACKET);
			return $size;
		}
		return false;
	}
	
	protected function parseSizeDecimal()
	{
		$token = $this->lookAhead();
		if (self::TOK_LBRACKET == $token) {
			$this->getToken();
			$size = array();
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size[] = (int) $tok_value;
			$this->expect(self::TOK_COMMA);
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size[] = (int) $tok_value;
			$this->expect(self::TOK_RBRACKET);
			return $size;
		}
		return false;
	}
	
	protected function parseIntOptions()
	{
		$options = array();
		$this->expect(self::TOK_LBRACKET);
		while (true) {
			$token = $this->getToken($tok_value);
			if ($token == self::TOK_NUMBER) {
				$key = (int) $tok_value;
				$this->expect(self::TOK_ASSIGN);
				$this->expect(self::TOK_STRING, $tok_value);
				$options[$key] = $tok_value;
			} elseif ($token == self::TOK_STRING) {
				$options[] = $tok_value;
			} else {
				$this->unexpected($token, array(self::TOK_NUMBER, self::TOK_STRING));
			}
			if (self::TOK_RBRACKET == $this->expect(array(self::TOK_RBRACKET, self::TOK_COMMA))) {
				break;
			}
		}
		return $options;
	}
	
	protected function parseStrOptions()
	{
		$options = array();
		$this->expect(self::TOK_LBRACKET);
		while (true) {
			$this->expect(self::TOK_STRING, $tok_value);
			$options[$tok_value] = $tok_value;
			if (self::TOK_RBRACKET == $this->expect(array(self::TOK_RBRACKET, self::TOK_COMMA))) {
				break;
			}
		}
		return $options;
	}
	
	protected function parseAttributeDefaultValue($attribute)
	{
		$token = $this->getToken($tok_value);
		switch ($token) {
			case self::TOK_NUMBER:
				$attribute->setDefaultValue((int) $tok_value);
				break;
			case self::TOK_DECIMAL_NUMBER:
				$attribute->setDefaultValue((float) $tok_value);
				break;
			case self::TOK_STRING:
				$attribute->setDefaultValue($tok_value);
				break;
			case self::TOK_TRUE:
				$attribute->setDefaultValue(true);
				break;
			case self::TOK_FALSE:
				$attribute->setDefaultValue(false);
				break;
			case self::TOK_IDENTIFIER:
				$behavior = new Behavior();
				$behavior->setName($tok_value);
				if (self::TOK_LBRACKET == $this->lookAhead()) {
					$this->getToken();
					$sfp = new CfonParser();
					$params = $sfp->parse($this->tokenizer);
					$behavior->setParams($params);
					$this->expect(self::TOK_RBRACKET);
				}
				$attribute->setDefaultValue($behavior);
				break;
			default:
				$this->unexpected($token);
				break;
		}
	}
}
