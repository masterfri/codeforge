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

require_once LIB_DIR . '/Model.php';

class Lex
{
	protected $tree = array();
	protected $text;
	protected $line;
	protected $models = array();
	
	const TOK_NONE = 'TOK_NONE';
	const TOK_NUMBER = 'TOK_NUMBER';
	const TOK_DECIMAL_NUMBER = 'TOK_DECIMAL_NUMBER';
	const TOK_STRING = 'TOK_STRING';
	const TOK_TRUE = 'TOK_TRUE';
	const TOK_FALSE = 'TOK_FALSE';
	const TOK_IDENTIFIER = 'TOK_IDENTIFIER';
	const TOK_MODEL = 'TOK_MODEL';
	const TOK_ABSTRACT = 'TOK_ABSTRACT';
	const TOK_SINGLE = 'TOK_SINGLE';
	const TOK_EXTENDS = 'TOK_EXTENDS';
	const TOK_IMPLEMENTS = 'TOK_IMPLEMENTS';
	const TOK_SCHEME = 'TOK_SCHEME';
	const TOK_COMPOSITE_IDENTIFIER = 'TOK_COMPOSITE_IDENTIFIER';
	const TOK_ATTRIBUTE = 'TOK_ATTRIBUTE';
	const TOK_PUBLIC = 'TOK_PUBLIC';
	const TOK_PROTECTED = 'TOK_PROTECTED';
	const TOK_PRIVATE = 'TOK_PRIVATE';
	const TOK_FINAL = 'TOK_FINAL';
	const TOK_OWN = 'TOK_OWN';
	const TOK_REQUIRED = 'TOK_REQUIRED';
	const TOK_UNSIGNED = 'TOK_UNSIGNED';
	const TOK_COLLECTION = 'TOK_COLLECTION';
	const TOK_READONLY = 'TOK_READONLY';
	const TOK_WRITEONLY = 'TOK_WRITEONLY';
	const TOK_NOACCESS = 'TOK_NOACCESS';
	const TOK_INTEGER = 'TOK_INTEGER';
	const TOK_DECIMAL = 'TOK_DECIMAL';
	const TOK_CHAR = 'TOK_CHAR';
	const TOK_TEXT = 'TOK_TEXT';
	const TOK_BOOL = 'TOK_BOOL';
	const TOK_INTOPTION = 'TOK_INTOPTION';
	const TOK_STROPTION = 'TOK_STROPTION';
	const TOK_LBRACKET = '(';
	const TOK_RBRACKET = ')';
	const TOK_SEMICOLON = ';';
	const TOK_COLON = ':';
	const TOK_COMMA = ',';
	const TOK_ASSIGN = '=';
	const TOK_ENDSTREAM = 'TOK_ENDSTREAM';
	const TOK_UNDEFINED = 'TOK_UNDEFINED';
	const TOK_COMMENT = 'TOK_COMMENT';
	
	const ERR_SYNTAX = 'SyntaxErrorException';
	
	protected $tokens = array(
		'model\b' => self::TOK_MODEL,
		'abstract\b' => self::TOK_ABSTRACT,
		'single\b' => self::TOK_SINGLE,
		'extends\b' => self::TOK_EXTENDS,
		'implements\b' => self::TOK_IMPLEMENTS,
		'scheme\b' => self::TOK_SCHEME,
		'attr\b' => self::TOK_ATTRIBUTE,
		'attribute\b' => self::TOK_ATTRIBUTE,
		'public\b' => self::TOK_PUBLIC,
		'protected\b' => self::TOK_PROTECTED,
		'private\b' => self::TOK_PRIVATE,
		'final\b' => self::TOK_FINAL,
		'own\b' => self::TOK_OWN,
		'required\b' => self::TOK_REQUIRED,
		'unsigned\b' => self::TOK_UNSIGNED,
		'collection\b' => self::TOK_COLLECTION,
		'readonly\b' => self::TOK_READONLY,
		'writeonly\b' => self::TOK_WRITEONLY,
		'noaccess\b' => self::TOK_NOACCESS,
		'int\b' => self::TOK_INTEGER,
		'decimal\b' => self::TOK_DECIMAL,
		'char\b' => self::TOK_CHAR,
		'text\b' => self::TOK_TEXT,
		'bool\b' => self::TOK_BOOL,
		'option\b' => self::TOK_INTOPTION,
		'enum\b' => self::TOK_STROPTION,
		'true\b' => self::TOK_TRUE,
		'false\b' => self::TOK_FALSE,
		'[a-z_][a-z0-9_]*([.][a-z_][a-z0-9_]*)+' => self::TOK_COMPOSITE_IDENTIFIER,
		'[a-z_][a-z0-9_]*' => self::TOK_IDENTIFIER,
		'`([a-z_][a-z0-9_]*)`' => array(self::TOK_IDENTIFIER, 2),
		'[0-9]+[.][0-9]+' => self::TOK_DECIMAL_NUMBER,
		'[0-9]+' => self::TOK_NUMBER,
		'"((\\\\"|[^"])+)"' => array(self::TOK_STRING, 2),
		'\/\/\/(.*)\n' => array(self::TOK_COMMENT, 2),
		'[,]' => self::TOK_COMMA,
		'[:]' => self::TOK_COLON,
		'[;]' => self::TOK_SEMICOLON,
		'[(]' => self::TOK_LBRACKET,
		'[)]' => self::TOK_RBRACKET,
		'[=]' => self::TOK_ASSIGN,
		'[\s\n]+' => self::TOK_NONE,
		'.' => self::TOK_UNDEFINED,
	);
	
	public function __construct()
	{
	}
	
	public function parse($infile)
	{
		$text = @file_get_contents($infile);
		if (false === $text) {
			throw new Exception("File not exists: $infile");
		}
		
		$this->text = $text;
		$this->line = 1;
		try {
			$this->start();
		} catch (ModelException $e) {
			throw new ErrException($e->getMessage(), $this->line);
		}
	}
	
	protected function start()
	{
		while (true) {
			$token = $this->lookAhead($tok_value);
			if (self::TOK_ENDSTREAM == $token) {
				break;
			}
			$this->scanModel();
		}
	}
	
	protected function scanModel()
	{
		$model = new Model();
		$this->scanComments($model);
		$this->scanModelAttributes($model);
		$this->expect(self::TOK_IDENTIFIER, $tok_value);
		$model->setName($tok_value);
		$token = $this->getToken($tok_value);
		if (self::TOK_EXTENDS == $token) {
			foreach ($this->getIdList() as $super) {
				$model->addSupermodel($super);
			}
			$token = $this->getToken($tok_value);
		}
		if (self::TOK_IMPLEMENTS == $token) {
			foreach ($this->getIdList() as $interface) {
				$model->addInterface($interface);
			}
			$token = $this->getToken($tok_value);
		}
		if (self::TOK_SCHEME != $token) {
			$this->unexpected($token, self::TOK_SCHEME);
		}
		while (true) {
			$this->expect(array(self::TOK_IDENTIFIER, self::TOK_COMPOSITE_IDENTIFIER), $tok_value);
			$model->addScheme($tok_value);
			$token = $this->expect(array(self::TOK_COMMA, self::TOK_COLON), $tok_value);
			if ($token == self::TOK_COLON) {
				break;
			}
		}
		
		while (true) {
			$token = $this->lookAhead($tok_value);
			if (in_array($token, array(self::TOK_MODEL, self::TOK_ENDSTREAM, self::TOK_ABSTRACT, self::TOK_SINGLE))) {
				break;
			}
			$this->scanAttribute($model);
		}
		
		$this->addModel($model);
	}
	
	protected function scanComments($object)
	{
		while (true) {
			$token = $this->lookAhead($tok_value);
			if ($token != self::TOK_COMMENT) {
				break;
			}
			$this->getToken($tok_value);
			$object->addComment($tok_value);
		}
	}
	
	protected function scanModelAttributes($model)
	{
		while (true) {
			$token = $this->getToken($tok_value);
			if (self::TOK_MODEL == $token) {
				break;
			}
			switch ($token) {
				case self::TOK_ABSTRACT:
					$model->setIsAbstract();
					break;
				
				case self::TOK_SINGLE:
					$model->setIsSingle();
					break;
					
				default:
					$this->unexpected($token, self::TOK_ATTRIBUTE);
			}
		}
	}
	
	protected function getIdList()
	{
		$result = array();
		while (true) {
			$this->expect(self::TOK_IDENTIFIER, $tok_value);
			$result[] = $tok_value;
			if (self::TOK_COMMA != $this->lookAhead($tok_value)) {
				break;
			}
			$this->getToken($tok_value);
		}
		return $result;
	}
	
	protected function scanAttribute($model)
	{
		$attribute = new Attribute();
		$this->scanComments($attribute);
		$this->scanAttributeAttributes($attribute);
		$this->expect(self::TOK_IDENTIFIER, $tok_value);
		$attribute->setName($tok_value);
		$this->scanAttributeType($attribute);
		if (!$attribute->getIsCollection()) {
			if (self::TOK_ASSIGN == $this->expect(array(self::TOK_SEMICOLON, self::TOK_ASSIGN), $tok_value)) {
				$this->scanAttributeDefaultVal($attribute);
				$this->expect(self::TOK_SEMICOLON, $tok_value);
			}
		} else {
			$this->expect(self::TOK_SEMICOLON, $tok_value);
		}
		$model->addAttribute($attribute);
	}
	
	protected function scanAttributeAttributes($attribute)
	{
		while (true) {
			$token = $this->getToken($tok_value);
			if (self::TOK_ATTRIBUTE == $token) {
				break;
			}
			switch ($token) {
				case self::TOK_PUBLIC:
					$attribute->setPrivacy(Attribute::PRIV_PUBLIC);
					break;
				
				case self::TOK_PROTECTED:
					$attribute->setPrivacy(Attribute::PRIV_PROTECTED);
					break;
					
				case self::TOK_PRIVATE:
					$attribute->setPrivacy(Attribute::PRIV_PRIVATE);
					break;
					
				case self::TOK_FINAL:
					$attribute->setIsFinal();
					break;
					
				case self::TOK_OWN:
					$attribute->setIsOwn();
					break;
					
				case self::TOK_REQUIRED:
					$attribute->setIsRequired();
					break;
				
				case self::TOK_UNSIGNED:
					$attribute->setIsUnsigned();
					break;
					
				case self::TOK_COLLECTION:
					$attribute->setIsCollection();
					break;
					
				case self::TOK_READONLY:
					$attribute->setMode(Attribute::MODE_READONLY);
					break;
					
				case self::TOK_WRITEONLY:
					$attribute->setMode(Attribute::MODE_WRITEONLY);
					break;
					
				case self::TOK_NOACCESS:
					$attribute->setMode(Attribute::MODE_NOACCESS);
					break;
				
				default:
					$this->unexpected($token, self::TOK_ATTRIBUTE);
			}
		}
	}
	
	protected function scanAttributeType($attribute)
	{
		$token = $this->getToken($tok_value);
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
			$size = $this->scanSize();
			$attribute->setType($type, $size);
		} elseif (Attribute::TYPE_DECIMAL == $type) {
			$size = $this->scanSizeDecimal();
			$attribute->setType($type, $size);
		} elseif (Attribute::TYPE_INTOPTION == $type) {
			$options = $this->scanIntOptions();
			$attribute->setType($type);
			$attribute->setOptions($options);
		} elseif (Attribute::TYPE_STROPTION == $type) {
			$options = $this->scanStrOptions();
			$attribute->setType($type);
			$attribute->setOptions($options);
		} else {
			$attribute->setType($type);
		}
		return $type;
	}
	
	protected function scanSize()
	{
		$token = $this->lookAhead($tok_value);
		if (self::TOK_LBRACKET == $token) {
			$this->getToken($tok_value);
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size = (int) $tok_value;
			$this->expect(self::TOK_RBRACKET, $tok_value);
			return $size;
		}
		return false;
	}
	
	protected function scanSizeDecimal()
	{
		$token = $this->lookAhead($tok_value);
		if (self::TOK_LBRACKET == $token) {
			$this->getToken($tok_value);
			$size = array();
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size[] = (int) $tok_value;
			$this->expect(self::TOK_COMMA, $tok_value);
			$this->expect(self::TOK_NUMBER, $tok_value);
			$size[] = (int) $tok_value;
			$this->expect(self::TOK_RBRACKET, $tok_value);
			return $size;
		}
		return false;
	}
	
	protected function scanIntOptions()
	{
		$options = array();
		$this->expect(self::TOK_LBRACKET, $tok_value);
		while (true) {
			$token = $this->getToken($tok_value);
			if ($token == self::TOK_NUMBER) {
				$key = (int) $tok_value;
				$this->expect(self::TOK_COLON, $tok_value);
				$this->expect(self::TOK_STRING, $tok_value);
				$options[$key] = $tok_value;
			} elseif ($token == self::TOK_STRING) {
				$options[] = $tok_value;
			} else {
				$this->unexpected($token, array(self::TOK_NUMBER, self::TOK_STRING));
			}
			$token = $this->getToken($tok_value);
			if ($token == self::TOK_RBRACKET) {
				break;
			} elseif ($token != self::TOK_COMMA) {
				$this->unexpected($token, array(self::TOK_RBRACKET, self::TOK_COMMA));
			}
		}
		return $options;
	}
	
	protected function scanStrOptions()
	{
		$options = array();
		$this->expect(self::TOK_LBRACKET, $tok_value);
		while (true) {
			$this->expect(self::TOK_STRING, $tok_value);
			$options[$tok_value] = $tok_value;
			$token = $this->getToken($tok_value);
			if ($token == self::TOK_RBRACKET) {
				break;
			} elseif ($token != self::TOK_COMMA) {
				$this->unexpected($token, array(self::TOK_RBRACKET, self::TOK_COMMA));
			}
		}
		return $options;
	}
	
	protected function scanAttributeDefaultVal($attribute)
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
				$attribute->setDefaultValue($this->unescapeString($tok_value));
				break;
			case self::TOK_TRUE:
				$attribute->setDefaultValue(true);
				break;
			case self::TOK_FALSE:
				$attribute->setDefaultValue(false);
				break;
			default:
				$this->unexpected($token);
				break;
		}
	}
	
	protected function unescapeString($str)
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
	
	protected function getToken(&$value, $skip_none=true)
	{
		while (true) {
			$token = $this->scanLex($this->text, $value, $scanned);
			$this->text = substr($this->text, strlen($scanned));
			$this->line += substr_count($scanned, "\n");
			if ($skip_none && self::TOK_NONE == $token) {
				continue;
			}
			return $token;
		}
	}
	
	protected function lookAhead(&$value, $skip_none=true)
	{
		$text = $this->text;
		while (true) {
			$token = $this->scanLex($text, $value, $scanned);
			$text = substr($text, strlen($scanned));
			if ($skip_none && self::TOK_NONE == $token) {
				continue;
			}
			return $token;
		}
	}
	
	protected function scanLex($text, &$value, &$scanned)
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
					return $token;
				}
			}
		}
		return self::TOK_ENDSTREAM;
	}
	
	protected function expect($token, &$value)
	{
		while (true) {
			$tok = $this->getToken($value);
			if (is_array($token) && in_array($tok, $token) || !is_array($token) && $tok === $token) {
				return $tok;
			}
			$this->unexpected($tok, $token);
		}
	}
	
	protected function unexpected($given, $expect=null)
	{
		if (null !== $expect) {
			if (is_array($expect)) {
				$expect = implode(' or ', $expect);
			}
			$this->error("unexpected token: $given, expected: $expect");
		} else {
			$this->error("unexpected token: $given");
		}
	}
	
	protected function error($str, $exception=self::ERR_SYNTAX)
	{
		throw new $exception($str, $this->line);
	}
	
	public function addModel(Model $model)
	{
		$this->models[$model->getName()] = $model;
	}
	
	public function removeModel($name=null)
	{
		if ($name) {
			unset($this->models[$name]);
		} else {
			$this->models = array();
		}
	}
	
	public function getModels()
	{
		return $this->models;
	}
}

class ErrException extends Exception
{
	public function __construct($message, $line=1)
	{
		$this->message = sprintf("Error on line %d : %s", $line, $message);
	}
}

class SyntaxErrorException extends ErrException
{
	public function __construct($message, $line=1)
	{
		$this->message = sprintf("Syntax error on line %d : %s", $line, $message);
	}
}

class ModelException extends Exception
{
}

class AttributeException extends ModelException
{
}
