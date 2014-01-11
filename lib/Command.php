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

require_once LIB_DIR . '/EasyConfig.php';

abstract class Command
{
	const DEFAULT_COMMAND = 'default';
	const COMMAND_NAME_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
	const PROJECT_DIR_NAME = 'codeforge';
	const MODEL_NAME_PATTERN = '/^[a-z_][a-z0-9_]*$/i';
	
	protected $_config;
	
	abstract public function run();
	
	public function printHelp()
	{
		$this->say("Sorry, this command has no help");
	}
	
	public function init()
	{
	}
	
	public function argsmap()
	{
		return array();
	}
	
	public function argrules()
	{
		return array();
	}
	
	public function acceptArg($name, $value)
	{
		return false;
	}
	
	public static function factory($args)
	{
		$path = THISDIR . '/commands';
		
		$commands = array();
		do {
			$command = array_shift($args);
			if (empty($command)) {
				$command = self::DEFAULT_COMMAND;
			} elseif (substr($command, 0, 1) == '-') {
				array_unshift($args, $command);
				$command = self::DEFAULT_COMMAND;
			}
			$commands[] = $command;
			if (!preg_match(self::COMMAND_NAME_PATTERN, $command)) {
				throw new Exception("Invalid command: " . implode(' ', $commands));
			}
			$path = $path . '/' . $command;
		} while (is_dir($path));
		
		$path = $path . '.php';
		if (!is_file($path) || !is_readable($path)) {
			throw new Exception("Invalid command: " . implode(' ', $commands));
		}
		require_once $path;
		$className = implode(array_map('ucfirst', $commands)).'Command';
		if (!class_exists($className, false)) {
			throw new Exception("Invalid command: " . implode(' ', $commands));
		}
		
		$command = new $className();
		$argsmap = $command->argsmap();
		$rules = $command->argrules();
		foreach (self::parseArgs($args, $rules) as $argname => $value) {
			if (isset($argsmap[$argname])) {
				$propname = $argsmap[$argname];
			} else {
				$propname = $argname;
			}
			if (property_exists($command, $propname)) {
				$command->$propname = $value;
			} elseif (method_exists($command, $propname)) {
				$command->$propname($value);
			} elseif (!$command->acceptArg($propname, $value)) {
				throw new Exception("Invalid argument: " . $argname);
			}
		}
		$command->init();
		return $command;
	}
	
	protected static function parseArgs($args, $rules)
	{
		$invokeargs = array();
		$empty = array();
		
		while ($argv = array_shift($args)) {
			if (substr($argv, 0, 2) == '--') {
				$argv = substr($argv, 2);
				$invokeargs[$argv] = self::parseArg($argv, $rules, $args);
			} elseif (substr($argv, 0, 1) == '-') {
				$argvv = substr($argv, 1);
				$len = strlen($argvv);
				for ($i = 0; $i < $len; $i++) {
					$argv = $argvv{$i};
					if ($i == $len - 1) {
						$invokeargs[$argv] = self::parseArg($argv, $rules, $args);
					} else {
						$invokeargs[$argv] = self::parseArg($argv, $rules, $empty);
					}
				}
			} else {
				$invokeargs[] = $argv;
			}
		}
		
		return $invokeargs;
	}
	
	protected static function parseArg($argv, $rules, &$args)
	{
		if (isset($rules[$argv])) {
			if (empty($args) || substr(current($args), 0, 1) == '-') {
				throw new Exception("Option $argv requires an argument");
			}
			$rule = $rules[$argv];
			$value = array_shift($args);
			if ('i' == $rule) {
				if (!preg_match('/^[0-9]+$/', $value)) {
					throw new Exception("Option $argv requires an integer value as argument");
				}
			} elseif ('n' == $rule) {
				if (!is_numeric($value)) {
					throw new Exception("Option $argv requires numerical argument");
				}
			} elseif ('s' != $rule) {
				throw new Exception("Invalid option rule: $rule");
			}
			return $value;
		} else {
			return true;
		}
	}
	
	protected function say()
	{
		$message = call_user_func_array('sprintf', func_get_args());
		echo "[CF] $message.\n";
	}
	
	protected function getProjectDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME;
	}
	
	protected function getCacheDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/cache';
	}
	
	protected function getCompiledDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/compiled';
	}
	
	protected function getStaticDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/static';
	}
	
	protected function getPartialDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/partial';
	}
	
	protected function getSrcDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/src';
	}
	
	protected function getDefaultSchemeDir()
	{
		return THISDIR . '/schemes';
	}
	
	protected function getCustomSchemeDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/schemes';
	}
	
	protected function getDefaultExtensionsDir()
	{
		return THISDIR . '/extensions';
	}
	
	protected function getCustomExtensionsDir()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/extensions';
	}
	
	protected function getConfigFile()
	{
		return WORKDIR . '/' . self::PROJECT_DIR_NAME . '/project.manifest';
	}
	
	protected function getTemplatesDir()
	{
		return THISDIR . '/templates';
	}
	
	protected function getTemplateDir($name)
	{
		return $this->getTemplatesDir() . '/' . $name;
	}
	
	protected function getModelFile($name)
	{
		return $this->getSrcDir() . '/' . $name . '.model';
	}
	
	protected function getConfig($reload=false)
	{
		if (null === $this->_config || $reload) {
			$condif = new EasyConfig();
			$condif->readFile($this->getConfigFile());
			$this->_config = $condif->getData();
			$version = isset($data['version']) ? $data['version'] : '1.0';
			if (version_compare($version, VERSION, '>')) {
				throw new Exception(PROGRAMM . ' version (' . VERSION . ') is less than project version (' . $version . ')');
			}
		}
		return $this->_config;
	}
	
	protected function updateConfig()
	{
		if (is_array($this->_config)) {
			$config = new EasyConfig($this->_config);
			$config->writeToFile($this->getConfigFile());
		}
	}
	
	protected function getConfigOption($name=null, $default=null)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		if ($name) {
			return isset($this->_config['options'][$name]) ? $this->_config['options'][$name] : $default;
		} else {
			return isset($this->_config['options']) ? $this->_config['options'] : array();
		}
	}
	
	protected function setConfigOption($name, $value, $update=true)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		$this->_config['options'][$name] = $value;
		if ($update) {
			$this->updateConfig();
		}
	}
	
	protected function unsetConfigOption($name, $update=true)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		unset($this->_config['options'][$name]);
		if ($update) {
			$this->updateConfig();
		}
	}
	
	protected function addFileToProject($file, $update=true)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		if (!isset($this->_config['src'])) {
			$this->_config['src'] = array();
		}
		if (!in_array($file, $this->_config['src'])) {
			$this->_config['src'][] = $file;
			if ($update) {
				$this->updateConfig();
			}
		}
	}
	
	protected function removeFileFromProject($file, $update=true)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		if (isset($this->_config['src'])) {
			$key = array_search($file, $this->_config['src']);
			if (false !== $key) {
				unset($this->_config['src'][$key]);
				$this->_config['src'] = array_values($this->_config['src']);
				if ($update) {
					$this->updateConfig();
				}
			}
		}
	}
	
	protected function isProjectHasFile($file)
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		if (!isset($this->_config['src'])) {
			return false;
		}
		return in_array($file, $this->_config['src']);
	}
	
	protected function getProjectFiles()
	{
		if (null === $this->_config) {
			$this->getConfig();
		}
		if (!isset($this->_config['src'])) {
			return array();
		}
		return $this->_config['src'];
	}
	
	protected function ask($message, $accept_empty=false)
	{
		do {
			echo "[CF] $message \n>> ";
			$answer = trim(fgets(STDIN));
		} while(!$accept_empty && '' == $answer);
		return $answer;
	}
	
	protected function confirm($message)
	{
		$answer = strtolower($this->ask("$message (y/N)", true));
		return in_array($answer, array('y', 'yes', 'yep'));
	}
	
	public function askVar($name, $prompt, $accept_empty=false)
	{
		$val = $this->getConfigOption($name);
		if (null === $val) {
			$val = $this->ask($prompt, $accept_empty);
			$this->setConfigOption($name, $val);
		}
		return $val;
	}
}
