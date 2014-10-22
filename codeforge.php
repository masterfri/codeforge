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

$args = $_SERVER['argv'];
array_shift($args);

define('SCRIPT', 'cf');
define('PROGRAMM', 'CodeForge');
define('VERSION', '2.0-dev');
define('THISDIR', dirname(__FILE__));
define('WORKDIR', getcwd());
define('LIB_DIR', THISDIR . '/lib');
define('IS_WINDOWS', strstr($_SERVER['OS'], 'Windows') !== false);
define('DIR_SEPARATOR', IS_WINDOWS ? '\\' : '/');

require_once LIB_DIR . '/Command.php';

try {
	Command::factory($args)->run();
} catch (Exception $e) {
	echo "[CF] Error: ";
	echo $e->getMessage();
	echo ".\n";
}
