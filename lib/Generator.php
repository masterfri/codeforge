<?php 

global $the_generator;

class Generator
{
	protected $scheme_dir = 'schemes/';
	protected $cache_dir = 'cache/';
	protected $work_dir;
	protected $current_file = null;
	protected $messages;
	protected $_dirperms = 0755;
	protected $_fileperms = 0644;
	protected $_env = array();
	
	public function __construct()
	{
		foreach (glob($this->scheme_dir . "_helpers/*.php") as $file) {
			require_once $file;
		}
	}
	
	public function setDirPerms($val)
	{
		$this->_dirperms = $val;
	}
	
	public function setFilePerms($val)
	{
		$this->_fileperms = $val;
	}
	
	public function getDirPerms()
	{
		return $this->_dirperms;
	}
	
	public function getFilePerms()
	{
		return $this->_fileperms;
	}
	
	public function setEnv(array $values)
	{
		$this->_env = $values;
	}
	
	public function getEnv($name=null, $default='')
	{
		if (! $name) {
			return $this->_env;
		} else {
			return isset($this->_env[$name]) ? $this->_env[$name] : $default;
		}
	}
	
	public function output(Lex $lex, $outdir)
	{
		global $the_generator;
		$the_generator = $this;
		$this->messages = array();
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
	
	public function setSchemesDir($dir)
	{
		if (! is_dir($dir) || !is_readable($dir)) {
			throw new Exception("Directory `$dir` is not exists");
		}
		$this->scheme_dir = rtrim($dir, '/') . '/';
	}
	
	public function getSchemesDir()
	{
		return $this->scheme_dir;
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
	
	public function applyScheme(Model $model, $scheme, $outdir)
	{
		$this->work_dir = rtrim($outdir, '/') . '/';
		$templates = $this->getSchemeFiles($scheme);
		foreach ($templates as $template) {
			$this->applySchemeTemplate($model, $template);
			if ($this->current_file !== null) {
				$this->closeFile();
			}
		}
	}
	
	protected function applySchemeTemplate(Model $model, $template)
	{
		include $this->complie($template);
	}
	
	protected function complie($template)
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
						$part = preg_replace('/^\s*open_file\(/', '$this->openFile(', $part);
						$part = preg_replace('/^\s*close_file\(/', '$this->closeFile(', $part);
						$part = preg_replace('/^\s*message\(/', '$this->message(', $part);
						$part = preg_replace('/^\s*start_attr_list_natural_order/', 'foreach($model->getAttributes(false) as $attribute):', $part);
						$part = preg_replace('/^\s*start_attr_list/', 'foreach($model->getAttributes() as $attribute):', $part);
						$part = preg_replace('/^\s*end_attr_list/', 'endforeach;', $part);
						$part = preg_replace('/^\s*=/', 'echo ', $part);
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
			if (! @mkdir($dir, $this->_dirperms, true)) {
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
		if (!@chmod($this->current_file, $this->_fileperms)) {
			throw new Exception("Can't change permissions for `{$this->current_file}`");
		}
		$this->current_file = null;
	}
	
	protected function getSchemeFiles($scheme)
	{
		$dir = $this->scheme_dir . $scheme;
		if (!is_dir($dir) || !is_readable($dir)) {
			throw new Exception("Scheme `$scheme` not found");
		}
		$list = array();
		foreach (glob("$dir/*.tmpl") as $file) {
			$list[basename($file)] = $file;
		}
		ksort($list);
		return $list;
	}
	
	public function message($message)
	{
		$this->messages[] = $message;
	}
}
