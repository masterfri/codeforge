#!/usr/bin/php -q
<?php

define('VERSION', '1.0');
define('THISDIR', dirname(__FILE__));

function octperms($v)
{
	if (!preg_match('@^[0-7]{3}$@', $v)) {
		usage();
		echo "Permission value should by an octal digit, ex. 755.";
		exit();
	}
	return octdec($v);
}

function parse_args()
{
	$options = array(
		'infile' => array(),
		'outdir' => THISDIR . '/output',
		'env' => array(),
	);
	
	$args = $_SERVER['argv'];
	array_shift($args);
	
	while ($argv = array_shift($args)) {
		switch ($argv) {
			case '-o':
				$options['outdir'] = array_shift($args);
				break;
				
			case '-dm':
				$options['dirperms'] = octperms(array_shift($args));
				break;
				
			case '-fm':
				$options['fileperms'] = octperms(array_shift($args));
				break;
				
			case '-e':
				$var = explode('=', array_shift($args), 2);
				if (count($var) == 2) {
					$options['env'][$var[0]] = $var[1];
				} else {
					$options['env'][$var[0]] = true;
				}
				break;
				
			case '-ef':
				$file = array_shift($args);
				if (!is_readable($file)) {
					printf("Can't open env-file: %s.\n", $file);
					exit;
				} else {
					$options['env'] = array_merge(require $file, $options['env']);
				}
				break;
			
			default:
				if ($argv{0} == '-') {
					printf("Unrecognized option: %s.\n", $argv);
					exit;
				} else {
					$options['infile'][] = $argv;
				}
				break;
		}
	}
	
	return $options;
}

function usage()
{
	echo "Usage: {$_SERVER['argv'][0]} [-o <outputdir>] [-dm <dir_perms>] [-fm <file_perms>] [(-e <variable1>=<value1> ...  -e <variableN>=<valueN>| -ef <envfile>)] <infile_1> [... <infile_N>] \n";
}

function hello()
{
	echo "CodeForge v".VERSION."\n";
	echo "by Grigory Ponomar <http://masterfri.org.ua>.\n";
}

hello();

$options = parse_args();

if (empty($options['infile'])) {
	usage();
	exit();
}

foreach ($options['infile'] as $file) {
	if (!is_file($file)) {
		printf("No such file: %s.\n", $file);
		exit;
	} elseif (!is_readable($file)) {
		printf("Can't open file: %s.\n", $file);
		exit;
	}
}

if (!is_dir($options['outdir'])) {
	printf("No such directory: %s.\n", $options['outdir']);
	exit;
} elseif (!is_writable($options['outdir'])) {
	printf("Directory is not writable: %s.\n", $options['outdir']);
	exit;
}

define('LIB_DIR', THISDIR . '/lib');

require_once LIB_DIR . '/Lex.php';
require_once LIB_DIR . '/Generator.php';

try {
	foreach ($options['infile'] as $infile) {
		$lex = new Lex();
		$generator = new Generator();
		if (isset($options['dirperms'])) {
			$generator->setDirPerms($options['dirperms']);
		}
		if (isset($options['fileperms'])) {
			$generator->setFilePerms($options['fileperms']);
		}
		$generator->setEnv($options['env']);
		$lex->parse($infile);
		$generator->output($lex, $options['outdir']);
	}
	echo "[CF] Done.\n";
} catch (Exception $e) {
	echo "[CF] Error: ";
	echo $e->getMessage() . ".\n";
}
