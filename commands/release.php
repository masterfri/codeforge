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

require_once CF_LIB_DIR . '/FileHelper.php';

class ReleaseCommand extends Command
{
	public $output = CF_WORKDIR;
	public $skipall = false;
	public $skipcheck = false;
	public $nostatic = false;

	protected $_ignorelist;
	protected $_checksumlist;
	protected $_update_ignorelist = false;
	
	public function argsmap()
	{
		return array(
			'o' => 'output',
		);
	}
	
	public function argrules()
	{
		return array(
			'output' => 's',
			'o' => 's',
		);
	}
	
	public function printHelp()
	{
		printf("%s release [--skipall] [--skipcheck] [--nostatic] [-o <dir>]\n", CF_SCRIPT);
		echo "Release project files.\nList of options:\n";
		echo "\t-o 			- output directory\n";
		echo "\t--skipall	- skip all modified files\n";
		echo "\t--skipcheck	- skip files checking\n";
		echo "\t--nostatic	- don't copy static files\n";
	}
	
	public function run()
	{
		$this->output = rtrim($this->output, '/');
		$this->getChecksumList();
		$ignored = $this->getIgnoreList();
		FileHelper::lsr($this->getCompiledDir(), $compiled);
		$compiled = array_diff($compiled, $ignored);
		if ($this->nostatic) {
			$static = array();
		} else {
			FileHelper::lsr($this->getStaticDir(), $static);
			$static = array_diff($static, $compiled);
			$static = array_diff($static, $ignored);
		}
		
		if (!$this->skipcheck) {
			$this->checkSafeCopy($compiled, $this->getCompiledDir());
			$this->checkSafeCopy($static, $this->getStaticDir());
		}
		
		$this->copyFiles($compiled, $this->getCompiledDir());
		$this->copyFiles($static, $this->getStaticDir());
		
		$this->updateChecksumList();
		if ($this->_update_ignorelist) {
			$this->updateIgnoreList();
		}
		
		$this->say("Done");
	}
	
	protected function checkSafeCopy(&$filelist, $srcdir)
	{
		$checksumlist = $this->getChecksumList();
		foreach ($filelist as $n => $file) {
			if (!is_file($srcdir . '/' . $file)) {
				continue;
			}
			$fullpath = $this->output . '/' . $file;
			$safe = true;
			if (is_file($fullpath)) {
				if (!isset($checksumlist[$file])) {
					$safe = false;
				} else {
					$checksum = md5_file($fullpath);
					if ($checksum != $checksumlist[$file]) {
						$safe = false;
					}
				}
			}
			if (!$safe) {
				if ($this->skipall) {
					unset($filelist[$n]);
					$this->say("Skipped: %s", $file);
				} else {
					$answer = strtolower($this->ask(sprintf("File `%s` was modified externally. [o]verwrite/[m]erge/[S]kip/add to [i]gnore-list/skip [a]ll", $file), true));
					if ('m' == $answer) {
						unset($filelist[$n]);
						$tool = $this->askVar("release.mergeTool", "Please, specify merge tool", true);
						if (empty($tool)) {
							$this->say("Mergetool is not set");
							$this->say("Skipped: %s", $file);
						} else {
							$frompath = $srcdir . '/' . $file;
							$cmd = strtr($tool, array(
								':original' => escapeshellarg($frompath),
								':mine' => escapeshellarg($fullpath),
							));
							exec($cmd, $output, $return_value);
							$this->say("Returned: (%s) %s", $return_value, implode("\n", $output));
						}
					} elseif ('s' == $answer) {
						unset($filelist[$n]);
					} elseif ('i' == $answer) {
						unset($filelist[$n]);
						$this->_update_ignorelist = true;
						$this->_ignorelist[] = $file;
					} elseif ('a' == $answer) {
						unset($filelist[$n]);
						$this->skipall = true;
					}
				}
			}
		}
	}

	protected function getIgnoreList()
	{
		if (null === $this->_ignorelist) {
			$file = $this->getIgnoreListFile();
			if (is_file($file)) {
				$list = new EasyConfig();
				$list->readFile($file);
				$this->_ignorelist = $list->getData();
			} else {
				$this->_ignorelist = array();
			}
		}
		return $this->_ignorelist;
	}
	
	protected function updateIgnoreList()
	{
		$list = new EasyConfig($this->_ignorelist);
		$list->writeToFile($this->getIgnoreListFile());
	}
	
	protected function getChecksumList()
	{
		if (null === $this->_checksumlist) {
			$file = $this->getChecksumListFile();
			if (is_file($file)) {
				$list = new EasyConfig();
				$list->readFile($file);
				$this->_checksumlist = $list->getData();
			} else {
				$this->_checksumlist = array();
			}
		}
		return $this->_checksumlist;
	}
	
	protected function updateChecksumList()
	{
		$list = new EasyConfig($this->_checksumlist);
		$list->writeToFile($this->getChecksumListFile());
	}
	
	protected function copyFiles(&$list, $srcdir)
	{
		foreach ($list as $file) {
			$from = $srcdir . '/' . $file;
			$to = $this->output . '/' . $file;
			if (is_dir($from)) {
				FileHelper::checkdir($to, fileperms($from) & 0777);
			} else {
				FileHelper::copy($from, $to);
			}
			$this->_checksumlist[$file] = md5_file($to);
		}
	}
}
