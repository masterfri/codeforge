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

class FileHelper
{
	public static function realpart($path, &$unreal=null)
	{
		$unreal = array();
		$components = explode('/', rtrim(self::realpath($path), '/'));
		while (! empty($components)) {
			$part = implode('/', $components);
			if ('' === $part) {
				break;
			}
			if (is_file($part) || is_dir($part)) {
				return $part;
			}
			array_unshift($unreal, array_pop($components));
		}
		return '/';
	}
	
	public static function mkdir($path, $permissions=0700, $recursive=false) 
	{
		if (is_dir($path)) {
			throw new \Exception("Directory `$path` already exists");
		}
		if (is_file($path)) {
			throw new \Exception("The path `$path` is a file");
		}
		if ($recursive) {
			$realpart = self::realpart($path, $need_create);
			if (is_file($realpart)) {
				throw new \Exception("The path `$realpart` is a file");
			}
			if (! is_writable($realpart)) {
				throw new \Exception("Can't create directory: path `$realpart` is not writable");
			}
			// create missing components
			foreach ($need_create as $dir) {
				$realpart .= "/$dir";
				if (! @mkdir($realpart)) {
					throw new \Exception("Error while creating directory `$realpart`");
				}
				self::chmod($realpart, $permissions);
			}
		} else {
			$dirname = dirname($path);
			if (is_file($dirname)) {
				throw new \Exception("The path `$dirname` is a file");
			}
			if (! is_writable($dirname)) {
				throw new \Exception("Can't create directory: path `$dirname` is not writable");
			}
			if (! @mkdir($path)) {
				throw new \Exception("Error while creating directory `$path`");
			}
			self::chmod($path, $permissions);
		}		
	}
	
	public static function checkdir($path, $permissions=0700)
	{
		if (! is_dir($path)) {
			self::mkdir($path, $permissions, true) ;
		}
	}
	
	public static function rm($path) 
	{
		// check is the directory can be deleted
		$dirname = dirname($path);
		if (! is_writable($dirname)) {
			throw new \Exception("Error while removing file or directory: path `$dirname` is not writable");
		}
		if (is_dir($path)) {
			// remove directory contents
			self::cleanup($path, true, false);
			if (! @rmdir($path)) {
				throw new \Exception("Error while removing directory `$path`");
			}
		} elseif (is_file($path)) {
			if (! @unlink($path)) {
				throw new \Exception("Error while removing file `$path`");
			}
		} else {
			throw new \Exception("Error while removing file or directory: path `$path` not exists");
		}
	}
	
	public static function cleanup($path, $recursive=false, $files_only=true)
	{
		$parent_is_writable = is_writable($path);
		$path = rtrim($path, '/') . '/';
		$h = @opendir($path);
		if (! $h) {
			throw new \Exception("Access denied to path `$path`");
		}
		// list directory contents
		while (($file = readdir($h)) !== false) {
			if ('.' == $file || '..' == $file) {
				continue;
			}
			$cpath = $path . $file;
			if (is_dir($cpath)) {
				if ($recursive) {
					// clean-up nested directory
					try {
						self::cleanup($cpath, true, $files_only);
					} catch (\Exception $e) {
						closedir($h);
						throw $e;
					}
				}
				if (! $files_only) {
					// try to delete the directory
					if (! $parent_is_writable) {
						closedir($h);
						throw new \Exception("Error while removing directory: path `$path` is not writable");
					}
					if (! @rmdir($cpath)) {
						closedir($h);
						throw new \Exception("Error while removing directory `$cpath`");
					}
				}
			} else {
				// try to remove the file
				if (! $parent_is_writable) {
					closedir($h);
					throw new \Exception("Error while removing file: path `$path` is not writable");
				}
				if (! @unlink($cpath)) {
					closedir($h);
					throw new \Exception("Error while removing file `$cpath`");
				}
			}
		}
		closedir($h);
	}
	
	public static function copy($file, $newname, $dirPerms=0700)
	{
		$destination = dirname($newname);
		self::checkdir($destination, $dirPerms);
		if (! is_writable($destination)) {
			throw new \Exception("Error while copying file or directory: path `$destination` is not writable");
		}
		$copyname = $destination . '/' . basename($newname);
		if (is_dir($file)) {
			// copy the directory and its contents
			self::checkdir($copyname, fileperms($file) & 0777);
			$h = @opendir($file);
			if (! $h) {
				throw new \Exception("Access denied to path `$file`");
			}
			$dir = rtrim($file, '/');
			// list directory contents
			while (($name = readdir($h)) !== false) {
				if ('.' == $name || '..' == $name) {
					continue;
				}
				try {
					self::copy($dir . '/' . $name, $copyname . '/' . $name);
				} catch (\Exception $e) {
					closedir($h);
					throw $e;
				}
			}
			closedir($h);
		} else {
			// try to copy the file
			if (! @copy($file, $copyname)) {
				throw new \Exception("Error while copying file `$file`");
			}
		}
	}
	
	public static function copyContents($from, $to, $dirPerms=0700)
	{
		$from = rtrim($from, '/');
		$to = rtrim($to, '/');
		$h = @opendir($from);
		if (! $h) {
			throw new \Exception("Access denied to path `$from`");
		}
		while (($name = readdir($h)) !== false) {
			if ('.' == $name || '..' == $name) {
				continue;
			}
			try {
				self::copy($from . '/' . $name, $to . '/' . $name);
			} catch (\Exception $e) {
				closedir($h);
				throw $e;
			}
		}
		closedir($h);
	}
	
	public static function rename($file, $newname, $move=false, $dirPerms=0700)
	{
		$dirfrom = dirname($file);
		if (! $move) {
			// just renaming
			$dirto = $dirfrom;
			$newname = $dirto . '/' . $newname;
		} elseif ($file != $newname) {
			// move to another destination
			$dirto = dirname($newname);
			self::checkdir($dirto, $dirPerms);
			if (! is_writable($dirto)) {
				throw new \Exception("Error while renaming file or directory: path `$dirto` is not writable");
			}
		}
		if ($file != $newname) {
			// try to move file/dir
			if (! is_writable($dirfrom)) {
				throw new \Exception("Error while renaming file or directory: path `$dirfrom` is not writable");
			}
			if (! @rename($file, $newname)) {
				throw new \Exception("Error while renaming file or directory `$file`");
			}
		}
		return $newname;
	}
	
	public static function lsr($path, &$list, $prefix='')
	{
		if (is_dir($path)) {
			$h = @opendir($path);
			if (! $h) {
				throw new \Exception("Access denied to path `$file`");
			}
			$path = rtrim($path, '/');
			while (($name = readdir($h)) !== false) {
				if ('.' == $name || '..' == $name) {
					continue;
				}
				$fullpath = $path . '/' . $name;
				$shortpath = ('' == $prefix ? '' : ($prefix . '/')) . $name;
				if (is_dir($fullpath)) {
					$list[] = $shortpath;
					try {
						self::lsr($fullpath, $list, $shortpath);
					} catch (\Exception $e) {
						closedir($h);
						throw $e;
					}
				} else {
					$list[] = $shortpath;
				}
			}
			closedir($h);
		} else {
			throw new \Exception("Not valid directory: `$path`");
		}
	}
	
	public static function chmod($path, $permissions)
	{
		// check are old and new permissions different
		if ((fileperms($path) & 0777) != $permissions) {
			if (! @chmod($path, $permissions)) {
				throw new \Exception("Error while changing permissions of `$path`");
			}
		}
	}
	
	public static function realpath($path)
	{
		if ('' == $path || '/' != $path{0}) {
			// make path absolute
			$path = getcwd() . '/' . $path;
		}
		$realpath = array();
		foreach (explode('/', $path) as $component) {
			if ('' == $component || '.' == $component) {
				// empty component or `this directory` component
				continue;
			}
			if ('..' == $component) {
				// `parent directory` component
				array_pop($realpath);
				continue;
			}
			$realpath[] = $component;
		}
		return '/' . implode('/', $realpath);
	}
	
	public static function str2perms($perms) 
	{
		if (is_int($perms)) {
			return $perms;
		} 
		if (is_string($perms)) {
			if ('0' == $perms{0}) {
				// octal permissions
				return octdec($perms);
			} elseif (preg_match('#^([r-][w-][x-]){3}$#i', $perms)) {
				// rwx permissions
				$val = 0;
				if ($perms{0} == 'r') $val |= 256;
				if ($perms{1} == 'w') $val |= 128;
				if ($perms{2} == 'x') $val |= 64;
				if ($perms{3} == 'r') $val |= 32;
				if ($perms{4} == 'w') $val |= 16;
				if ($perms{5} == 'x') $val |= 8;
				if ($perms{6} == 'r') $val |= 4;
				if ($perms{7} == 'w') $val |= 2;
				if ($perms{8} == 'x') $val |= 1;
				return $val;
			}
		}
		return false;
	}
	
	public static function perms2str($perms, $separator='')
	{
		if (($perms & 0xC000) == 0xC000) {
			$info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
			$info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
			$info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
			$info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
			$info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
			$info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
			$info = 'p';
		} else {
			$info = 'u';
		}
		$info .= $separator;
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
			(($perms & 0x0800) ? 's' : 'x' ) :
			(($perms & 0x0800) ? 'S' : '-'));

		$info .= $separator;
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
			(($perms & 0x0400) ? 's' : 'x' ) :
			(($perms & 0x0400) ? 'S' : '-'));

		$info .= $separator;
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
			(($perms & 0x0200) ? 't' : 'x' ) :
			(($perms & 0x0200) ? 'T' : '-'));

		return $info;
	}
}
