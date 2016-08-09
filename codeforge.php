#!/usr/bin/php -q
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

$args = $_SERVER['argv'];
array_shift($args);

define('CF_SCRIPT', 'cf');
define('CF_PROGRAMM', 'CodeForge');
define('CF_VERSION', '2.0-dev');
define('CF_THISDIR', dirname(__FILE__));
define('CF_WORKDIR', getcwd());
define('CF_LIB_DIR', CF_THISDIR . '/lib');

require_once CF_LIB_DIR . '/Command.php';

try {
	Command::factory($args)->run();
} catch (Exception $e) {
	echo "[CF] Error: ";
	echo $e->getMessage();
	echo ".\n";
}
