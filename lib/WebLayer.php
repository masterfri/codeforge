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

require_once CF_LIB_DIR . '/Builder.php';
require_once CF_LIB_DIR . '/Attribute.php';
require_once CF_LIB_DIR . '/EasyConfig.php';

class WebLayer extends Builder
{
	protected $preloaded = false;
	protected $_listSchemes;
	protected $_listPkg;
	
	public function __construct()
	{
	}
	
	protected function preload()
	{
		if (!$this->preloaded) {
			$this->loadCommonHelpers();
			$this->loadExtensions();
			$this->preloaded = true;
		}
	}
	
	public function getCustomTypes()
	{
		$this->preload();
		return Attribute::getCustomTypes();
	}
	
	public function getSchemesList()
	{
		if (null === $this->_listSchemes) {
			$this->_listSchemes = array(
				'_compile' => array(),
				'_build' => array(),
			);
			foreach ($this->getSchemesDir() as $dir) {
				$this->collectSchemes($dir);
			}
		}
		return $this->_listSchemes;
	}
	
	public function getPkgList()
	{
		if (null === $this->_listPkg) {
			$this->_listPkg = array();
			$this->collectPkg(CF_THISDIR . '/templates/');
		}
		return $this->_listPkg;
	}
	
	public function askVar($name, $prompt, $accept_empty=false)
	{
		$val = $this->getEnv($name, null);
		if (null === $val) {
			if (!$accept_empty) {
				echo "Option '$name' is required. Please specify this option and rebuild app.\n";
			}
		}
		return $val;
	}
	
	protected function collectSchemes($dir, $prefix='')
	{
		foreach (glob($dir . '*') as $file) {
			$base = basename($file);
			if ('_' == substr($base, 0, 1) || '.' == substr($base, 0, 1)) {
				continue;
			}
			if (is_dir($file)) {
				if (is_dir($file . '/_compile')) {
					$this->_listSchemes['_compile'][] = $prefix . $base;
				}
				if (is_dir($file . '/_build')) {
					$this->_listSchemes['_build'][] = $prefix . $base;
				}
				$this->collectSchemes($file . '/', $prefix . $base . '.');
			}
		}
	}
	
	protected function collectPkg($dir, $prefix='')
	{
		foreach (glob($dir . '*') as $file) {
			$base = basename($file);
			if ('_' == substr($base, 0, 1) || '.' == substr($base, 0, 1)) {
				continue;
			}
			if (is_dir($file)) {
				$packdir = $file . '/_pack';
				if (is_dir($packdir)) {
					$deps = array();
					$opts = array();
					$requirements = $file . '/_deps.list';
					if (is_file($requirements)) {
						$list = new EasyConfig();
						$list->readFile($requirements);
						$data = $list->getData();
						if (isset($data['strict']) && is_array($data['strict'])) {
							$deps = $data['strict'];
						}
						if (isset($data['optional']) && is_array($data['optional'])) {
							$opts = $data['optional'];
						}
					}
					$this->_listPkg[$prefix . $base] = array(
						'deps' => $deps,
						'opts' => $opts,
						'dir' => $packdir,
					);
				}
				$this->collectPkg($file . '/', $prefix . $base . '.');
			}
		}
	}
}
