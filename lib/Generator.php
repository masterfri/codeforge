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

require_once LIB_DIR . '/Registry.php';
require_once LIB_DIR . '/HelperInvoker.php';
require_once LIB_DIR . '/FunctionCaller.php';
require_once LIB_DIR . '/HelperChain.php';

class Generator
{
	protected $scheme_dir = array('schemes/');
	protected $extensions_dir = array('extensions/');
	protected $cache_dir = 'cache/';
	protected $work_dir;
	protected $partial_dir = 'partial/';
	protected $mode = '_compile';
	protected $current_file;
	protected $messages;
	protected $dirperms = 0755;
	protected $fileperms = 0644;
	protected $env = array();
	protected $active_namespace = '::';
	protected $command;
	
	protected static $schemes = array();
	protected static $loaded_helper_groups = array();
	protected static $helpers = array();
	protected static $loaded_extensions = array();
	
	public function __construct(Command $command)
	{
		$this->command = $command;
	}
	
	public function setMode($val)
	{
		$this->mode = $val;
	}
	
	public function getMode($val)
	{
		return $this->mode;
	}
	
	public function setDirPerms($val)
	{
		$this->dirperms = $val;
	}
	
	public function setFilePerms($val)
	{
		$this->fileperms = $val;
	}
	
	public function getDirPerms()
	{
		return $this->dirperms;
	}
	
	public function getFilePerms()
	{
		return $this->fileperms;
	}
	
	public function setEnv($name, $value='')
	{
		if (is_array($name)) {
			$this->env = $value;
		} else {
			$this->env[$name] = $value;
		}
	}
	
	public function getEnv($name=null, $default='')
	{
		if (! $name) {
			return $this->env;
		} else {
			return isset($this->env[$name]) ? $this->env[$name] : $default;
		}
	}
	
	public function compile(Lex $lex, $outdir)
	{
		Registry::getSingleton()->set('generator', $this);
		$this->messages = array();
		$this->loadCommonHelpers();
		$this->loadExtensions();
		foreach ($lex->getModels() as $model) {
			foreach ($model->getSchemes() as $scheme) {
				$this->applyScheme($model, $scheme, $outdir);
			}
		}
		if (! empty($this->messages)) {
			echo implode("\n", $this->messages);
			echo "\n";
		}
	}
	
	public function build($schemes, $outdir)
	{
		global $the_generator;
		$the_generator = $this;
		$this->messages = array();
		$this->loadCommonHelpers();
		$this->loadExtensions();
		if (!is_array($schemes)) {
			$schemes = array($schemes);
		}
		foreach ($schemes as $scheme) {
			$this->buildUsingScheme($scheme, $outdir);
		}
		if (! empty($this->messages)) {
			echo implode("\n", $this->messages);
			echo "\n";
		}
	}
	
	public function setSchemesDir($dirs)
	{
		if (!is_array($dirs)) {
			$dirs = array($dirs);
		}
		
		$this->scheme_dir = array();
		
		foreach ($dirs as $dir) {
			if (! is_dir($dir) || !is_readable($dir)) {
				throw new Exception("Directory `$dir` is not exists");
			}
			$this->scheme_dir[] = rtrim($dir, '/') . '/';
		}
	}
	
	public function getSchemesDir()
	{
		return $this->scheme_dir;
	}
	
	public function setExtensionsDir($dirs)
	{
		if (!is_array($dirs)) {
			$dirs = array($dirs);
		}
		
		$this->extensions_dir = array();
		
		foreach ($dirs as $dir) {
			if (! is_dir($dir) || !is_readable($dir)) {
				throw new Exception("Directory `$dir` is not exists");
			}
			$this->extensions_dir[] = rtrim($dir, '/') . '/';
		}
	}
	
	public function getExtensionsDir()
	{
		return $this->extensions_dir;
	}
	
	public function setCacheDir($dir)
	{
		if (! is_dir($dir) || !is_writable($dir)) {
			throw new Exception("Directory `$dir` is not exists or is not writable");
		}
		$this->cache_dir = rtrim($dir, '/') . '/';
	}
	
	public function getCacheDir()
	{
		return $this->cache_dir;
	}
	
	public function setPartialDir($dir)
	{
		if (! is_dir($dir) || !is_writable($dir)) {
			throw new Exception("Directory `$dir` is not exists or is not writable");
		}
		$this->partial_dir = rtrim($dir, '/') . '/';
	}
	
	public function getPartialDir()
	{
		return $this->partial_dir;
	}
	
	public function applyScheme(Model $model, $scheme, $outdir)
	{
		$this->work_dir = rtrim($outdir, '/') . '/';
		$templates = $this->getSchemeFiles($scheme);
		$this->switchNamespace($scheme);
		foreach ($templates as $template) {
			$this->applySchemeTemplate($model, $template);
			if ($this->current_file !== null) {
				$this->closeFile();
			}
		}
	}
	
	public function buildUsingScheme($scheme, $outdir)
	{
		$this->work_dir = rtrim($outdir, '/') . '/';
		$templates = $this->getSchemeFiles($scheme);
		$this->switchNamespace($scheme);
		foreach ($templates as $template) {
			$this->buildUsingSchemeTemplate($template);
			if ($this->current_file !== null) {
				$this->closeFile();
			}
		}
	}
	
	protected function applySchemeTemplate(Model $model, $template)
	{
		include $this->complieTemplate($template);
	}
	
	protected function buildUsingSchemeTemplate($template)
	{
		include $this->complieTemplate($template);
	}
	
	protected function complieTemplate($template)
	{
		$cachefile = $this->cache_dir . md5($template).'.ctmpl';
		if (!is_file($cachefile) || filemtime($cachefile) < filemtime($template)) {
			$lines = file($template);
			$prev_is_inline = false;
			ob_start();
			echo "<?php \n";
			foreach ($lines as $line) {
				if (preg_match('/^\s*{%%/', $line)) {
					continue;
				}
				$parts = preg_split('@({%.+%})@U', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				foreach ($parts as $part) {
					if (substr($part, 0, 2) == '{%') {
						$part = substr($part, 2, -3);
						$part = preg_replace('/^\s*start_attr_list_natural_order/', 'foreach($model->getAttributes(false) as $attribute):', $part);
						$part = preg_replace('/^\s*start_attr_list/', 'foreach($model->getAttributes() as $attribute):', $part);
						$part = preg_replace('/^\s*end_attr_list/', 'endforeach;', $part);
						$part = preg_replace('/^\s*=/', 'echo ', $part);
						$part = preg_replace_callback('/(?<!->|::)\b([a-zA-Z_][a-zA-Z0-9_]*)\s*[(]/', function($m) {
							$n = strtolower($m[1]);
							if (in_array($n, array('if', 'elseif', 'while', 'switch', 'foreach', 'for', 'function', 'array'))) {
								return $m[0];
							} elseif ('env' == $n) {
								return '$this->getEnv(';
							} elseif ('open_file' == $n) {
								return '$this->openFile(';
							} elseif ('open_partial' == $n) {
								return '$this->openPartial(';
							} elseif ('close_file' == $n) {
								return '$this->closeFile(';
							} elseif ('join_partial' == $n) {
								return '$this->joinPartial(';
							} elseif ('message' == $n) {
								return '$this->message(';
							} elseif ('array_map' == $n) {
								return '$this->arrayMap(';
							} elseif ('ask' == $n) {
								return '$this->askVar(';
							} else {
								return sprintf('$this->invokeHelper("%s")->call(', $m[1]);
							}
						}, $part);
						$part = trim($part);
						if (substr($part, -1) != ':') {
							$part = trim($part, ';') . ';';
						}
						echo "$part\n";
						$prev_is_inline = true;
					} else {
						if ($prev_is_inline) {
							$prev_is_inline = false;
							$part = ltrim($part, "\n");
							if (empty($part)) {
								continue;
							}
						}
						echo 'echo "' . $this->escapeString($part) . '";' . "\n";
					}
				}
			}
			file_put_contents($cachefile, ob_get_clean());
		}
		return $cachefile;
	}
	
	protected function escapeString($str)
	{
		return str_replace(array('\\', '"', "\t", "\n", '$'), array('\\\\', '\\"', '\t', '\n', '\$'), $str);
	}
	
	protected function openFile($name)
	{
		if ($this->current_file !== null) {
			throw new Exception("Only one file can be opened at same time");
		}
		$this->current_file = $this->work_dir . ltrim($name, '/');
		$dir = dirname($this->current_file);
		if (! is_dir($dir)) {
			if (! @mkdir($dir, $this->dirperms, true)) {
				throw new Exception("Can't create directory `$dir`");
			}
		} elseif (! is_writable($dir)) {
			throw new Exception("Directory `$dir` is not writable");
		}
		ob_start();
	}
	
	protected function openPartial($partial_id, $part_id)
	{
		if ($this->current_file !== null) {
			throw new Exception("Only one file can be opened at same time");
		}
		$this->current_file = $this->partial_dir . $partial_id . '/' . $part_id . '.part';
		$dir = dirname($this->current_file);
		if (! is_dir($dir)) {
			if (! @mkdir($dir, $this->dirperms, true)) {
				throw new Exception("Can't create directory `$dir`");
			}
		} elseif (! is_writable($dir)) {
			throw new Exception("Directory `$dir` is not writable");
		}
		ob_start();
	}
	
	protected function closeFile()
	{
		if ($this->current_file === null) {
			throw new Exception("Trying to close file, but file is not opened");
		}
		file_put_contents($this->current_file, ob_get_clean());
		if (!@chmod($this->current_file, $this->fileperms)) {
			throw new Exception("Can't change permissions for `{$this->current_file}`");
		}
		$this->current_file = null;
	}
	
	protected function joinPartial($partial_id)
	{
		$dir = $this->partial_dir . $partial_id;
		if (!is_dir($dir)) {
			$this->message("Partial not found `$partial_id`");
			return;
		}
		foreach (glob($dir . '/*.part') as $file) {
			readfile($file);
		}
	}
	
	protected function getSchemeFiles($scheme)
	{
		if (!isset(self::$schemes[$scheme][$this->mode])) {
			self::$schemes[$scheme][$this->mode] = $this->loadScheme($scheme);
		}
		return self::$schemes[$scheme][$this->mode];
	}
	
	protected function loadScheme($scheme)
	{
		$found = false;
		$result = array();
		
		foreach ($this->scheme_dir as $dir) {
			$fullpath = $dir;
			$components = explode('.', $scheme);
			$this->active_namespace = '';
			foreach ($components as $component) {
				$fullpath .= $component . '/';
				$this->active_namespace .= '::' . $component;
				$this->loadHelpers($fullpath);
			}
			$fullpath .= $this->mode . '/';
			if (is_dir($fullpath)) {
				$found = true;
				foreach (glob($fullpath . '*.tmpl') as $file) {
					$result[basename($file)] = $file;
				}
			}
		}
		
		if (!$found) {
			throw new Exception("Scheme `$scheme` not found");
		}
		
		ksort($result);
		return $result;
	}
	
	protected function loadCommonHelpers()
	{
		$this->active_namespace = '::';
		foreach ($this->scheme_dir as $dir) {
			$this->loadHelpers($dir . 'common/');
		}
	}
	
	protected function loadExtensions()
	{
		$this->active_namespace = '::';
		foreach ($this->extensions_dir as $dir) {
			foreach (glob($dir . '*.php') as $file) {
				$ext_name = basename($file, 'php');
				if (!in_array($ext_name, self::$loaded_extensions)) {
					require_once($file);
					self::$loaded_extensions[] = $ext_name;
				}
			}
		}
	}
	
	protected function loadHelpers($dir)
	{
		if (!in_array($dir, self::$loaded_helper_groups)) {
			self::$loaded_helper_groups[] = $dir;
			if (is_dir($dir . '_helpers')) {
				foreach (glob($dir . '_helpers/*.php') as $file) {
					require_once($file);
				}
			}
		}
	}
	
	protected function switchNamespace($scheme)
	{
		$this->active_namespace = '::' . implode('::', explode('.', $scheme));
	}
	
	public function message($message)
	{
		$this->messages[] = $message;
	}
	
	public function registerHelper($name, $function, $priority=0, $namespace=null)
	{
		if (null === $namespace) {
			$namespace = $this->active_namespace;
		}
		if (isset(self::$helpers[$namespace][$name])) {
			$chain = self::$helpers[$namespace][$name];
		} else {
			$chain = new HelperChain();
			self::$helpers[$namespace][$name] = $chain;
		}
		$chain->add($function, $priority);
	}
	
	public function invokeHelper($name, $reusable=false)
	{
		$candidates = array();
		$namespaces = explode('::', $this->active_namespace);
		do {
			$namespace = implode('::', $namespaces);
			if (empty($namespace)) {
				$namespace = '::';
			}
			if (isset(self::$helpers[$namespace][$name])) {
				foreach (self::$helpers[$namespace][$name]->get() as $func) {
					$candidates[] = $func;
				}
			}
			array_pop($namespaces);
		} while (count($namespaces));
		if (function_exists($name)) {
			$candidates[] = new FunctionCaller($name);
		}
		if (empty($candidates)) {
			$this->message("Unknow helper: $name");
		}
		return new HelperInvoker($this, $candidates, $reusable);
	}
	
	public function arrayMap($name, $array)
	{
		$result = array();
		$invoker = $this->invokeHelper($name, true);
		foreach ($array as $key => $item) {
			$result[$key] = $invoker->call($item);
			$invoker->reuse();
		}
		return $result;
	}
	
	public function askVar($name, $prompt, $accept_empty=false)
	{
		$val = $this->getEnv($name, null);
		if (null === $val) {
			$val = $this->command->askVar($name, $prompt, $accept_empty);
			$this->setEnv($name, $val);
		}
		return $val;
	}
	
	public function registerType($name, $based_on=Attribute::TYPE_CUSTOM, $size=false, $unsigned=false)
	{
		Attribute::registerCustomType($name, $based_on, $size, $unsigned);
	}
}
