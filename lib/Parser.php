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
require_once CF_LIB_DIR . '/AttributeParser.php';
require_once CF_LIB_DIR . '/BehaviorParser.php';
require_once CF_LIB_DIR . '/Model.php';

class Parser extends AbstractParser
{
	protected $models = array();
	protected $comment_buffer = array();
	
	protected function initTokenizer($tokenizer)
	{
		$tokenizer->setTokens(array(
			'model\b' => self::TOK_MODEL,
			'scheme\b' => self::TOK_SCHEME,
			'attr\b' => self::TOK_ATTRIBUTE,
			'attribute\b' => self::TOK_ATTRIBUTE,
			'behavior\b' => self::TOK_BEHAVIOR,
			'unsigned\b' => self::TOK_UNSIGNED,
			'collection\b' => self::TOK_COLLECTION,
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
			'[{]' => self::TOK_LFBRACKET,
			'[}]' => self::TOK_RFBRACKET,
			'[\[]' => self::TOK_LSBRACKET,
			'[\]]' => self::TOK_RSBRACKET,
			'[=]' => self::TOK_ASSIGN,
			'[\s\n]+' => self::TOK_NONE,
			'.' => self::TOK_UNDEFINED,
		));
		$tokenizer->setUnescape(array(
			self::TOK_STRING,
		));
	}

	protected function start()
	{
		while (true) {
			$token = $this->lookAhead($tok_value);
			if (self::TOK_ENDSTREAM == $token) {
				break;
			}
			$this->parseModel();
		}
	}
	
	protected function parseModel()
	{
		$model = new Model();
		$this->parseComments();
		$model->addComments($this->getCommentsBuffer());
		$this->expect(self::TOK_MODEL);
		$this->expect(self::TOK_IDENTIFIER, $tok_value);
		$model->setName($tok_value);
		$this->parseSchemasList($model);
		
		$loop = true;
		while ($loop) {
			$this->parseComments();
			$token = $this->lookAhead($tok_value);
			switch ($token) {
				case self::TOK_ATTRIBUTE:
					$this->parseAttribute($model);
					break;
				case self::TOK_BEHAVIOR:
					$this->parseBehavior($model);
					break;
				default:
					$loop = false;
					break;
			}
		}
		
		$this->addModel($model);
	}
	
	protected function parseSchemasList($model)
	{
		$this->expect(self::TOK_SCHEME);
		while (true) {
			$this->expect(array(self::TOK_IDENTIFIER, self::TOK_COMPOSITE_IDENTIFIER), $tok_value);
			$model->addScheme($tok_value);
			$token = $this->expect(array(self::TOK_COMMA, self::TOK_COLON), $tok_value);
			if ($token == self::TOK_COLON) {
				break;
			}
		}
	}
	
	protected function parseComments()
	{
		while (true) {
			$token = $this->lookAhead($tok_value);
			if ($token != self::TOK_COMMENT) {
				break;
			}
			$this->getToken($tok_value);
			$this->comment_buffer[] = $tok_value;
		}
	}
	
	protected function getCommentsBuffer($clean=true)
	{
		$result = $this->comment_buffer;
		if ($clean) {
			$this->comment_buffer = array();
		}
		return $result;
	}
	
	protected function parseAttribute($model)
	{
		$parser = new AttributeParser();
		$attribute = $parser->parse($this->tokenizer);
		$attribute->addComments($this->getCommentsBuffer());
		$model->addAttribute($attribute);
	}
	
	protected function parseBehavior($model)
	{
		$parser = new BehaviorParser();
		$behavior = $parser->parse($this->tokenizer);
		$behavior->addComments($this->getCommentsBuffer());
		$model->addBehavior($behavior);
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
