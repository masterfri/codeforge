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
require_once CF_LIB_DIR . '/CfonParser.php';
require_once CF_LIB_DIR . '/Behavior.php';

class BehaviorParser extends AbstractParser
{
	protected function start()
	{
		$behavior = new Behavior();
		$this->expect(self::TOK_BEHAVIOR);
		$this->expect(self::TOK_IDENTIFIER, $tok_value);
		$behavior->setName($tok_value);
		if (self::TOK_LBRACKET == $this->expect(array(self::TOK_LBRACKET, self::TOK_SEMICOLON))) {
			$sfp = new CfonParser();
			$params = $sfp->parse($this->tokenizer);
			$behavior->setParams($params);
			$this->expect(self::TOK_RBRACKET);
			$this->expect(self::TOK_SEMICOLON);
		}
		return $behavior;
	}
}
