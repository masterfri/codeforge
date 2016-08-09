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
require_once CF_LIB_DIR . '/FileTokenizer.php';

abstract class AbstractParser
{
	const TOK_NONE = 'TOK_NONE';
	const TOK_NUMBER = 'TOK_NUMBER';
	const TOK_DECIMAL_NUMBER = 'TOK_DECIMAL_NUMBER';
	const TOK_STRING = 'TOK_STRING';
	const TOK_TRUE = 'TOK_TRUE';
	const TOK_FALSE = 'TOK_FALSE';
	const TOK_IDENTIFIER = 'TOK_IDENTIFIER';
	const TOK_MODEL = 'TOK_MODEL';
	const TOK_SCHEME = 'TOK_SCHEME';
	const TOK_COMPOSITE_IDENTIFIER = 'TOK_COMPOSITE_IDENTIFIER';
	const TOK_ATTRIBUTE = 'TOK_ATTRIBUTE';
	const TOK_BEHAVIOR = 'TOK_BEHAVIOR';
	const TOK_UNSIGNED = 'TOK_UNSIGNED';
	const TOK_COLLECTION = 'TOK_COLLECTION';
	const TOK_INTEGER = 'TOK_INTEGER';
	const TOK_DECIMAL = 'TOK_DECIMAL';
	const TOK_CHAR = 'TOK_CHAR';
	const TOK_TEXT = 'TOK_TEXT';
	const TOK_BOOL = 'TOK_BOOL';
	const TOK_INTOPTION = 'TOK_INTOPTION';
	const TOK_STROPTION = 'TOK_STROPTION';
	const TOK_LBRACKET = '(';
	const TOK_RBRACKET = ')';
	const TOK_LFBRACKET = '{';
	const TOK_RFBRACKET = '}';
	const TOK_LSBRACKET = '[';
	const TOK_RSBRACKET = ']';
	const TOK_SEMICOLON = ';';
	const TOK_COLON = ':';
	const TOK_COMMA = ',';
	const TOK_ASSIGN = '=';
	const TOK_ENDSTREAM = 'TOK_ENDSTREAM';
	const TOK_UNDEFINED = 'TOK_UNDEFINED';
	const TOK_COMMENT = 'TOK_COMMENT';
	
	const ERR_SYNTAX = 'SyntaxErrorException';
	
	protected $tokenizer;

	protected function createTokenizer($text)
	{
		return new Tokenizer($text);
	}
	
	public function parseFile($infile)
	{
		$tokenizer = new FileTokenizer($infile);
		$this->initTokenizer($tokenizer);
		return $this->parse($tokenizer);
	}
	
	public function parseText($text)
	{
		$tokenizer = new Tokenizer($text);
		$this->initTokenizer($tokenizer);
		return $this->parse($tokenizer);
	}
	
	public function parse(Tokenizer $source)
	{
		$this->tokenizer = $source;
		try {
			return $this->start();
		} catch (ModelException $e) {
			throw new ErrException($e->getMessage(), $this->tokenizer->getLine());
		}
	}
	
	abstract protected function start();
	
	protected function unescapeString($str)
	{
		return $this->tokenizer->unescapeString($str);
	}
	
	protected function getToken(&$value=null, $skip_none=true)
	{
		return $this->tokenizer->getToken($value, $skip_none);
	}
	
	protected function lookAhead(&$value=null, $skip_none=true)
	{
		return $this->tokenizer->lookAhead($value, $skip_none);
	}

	protected function expect($token, &$value=null)
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
		throw new $exception($str, $this->tokenizer->getCurrentPlace());
	}
}

class ErrException extends \Exception
{
	public function __construct($message, $place)
	{
		$this->message = sprintf("Error in %s: %s", $place, $message);
	}
}

class SyntaxErrorException extends ErrException
{
	public function __construct($message, $place)
	{
		$this->message = sprintf("Syntax error in %s: %s", $place, $message);
	}
}

class ModelException extends \Exception
{
}

class AttributeException extends ModelException
{
}

class BehaviorException extends ModelException
{
}
