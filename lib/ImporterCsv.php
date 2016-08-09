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

require_once CF_LIB_DIR . '/Importer.php';

class ImporterCsv extends Importer
{
	protected function process()
	{
		while (!feof($this->_in)) {
			$data = fgetcsv($this->_in);
			$label = trim($data[0]);
			if (empty($label)) {
				$this->addSpacer();
			} else {
				$attribs = array();
				if (trim(strtolower($data[1])) == 'y') {
					$attribs['required'] = 'true';
				}
				$type = trim($data[2]);
				if (substr($type, 0, 1) == '*') {
					$opts = array();
					foreach (explode("\n", $type) as $opt) {
						$opts[] = addslashes(trim($opt, " \t\r*"));
					}
					$type = sprintf('enum("%s")', implode('","', $opts));
				}
				$this->addAttribute($attribs, null, $type, $label);
			}
		}
	}
}
