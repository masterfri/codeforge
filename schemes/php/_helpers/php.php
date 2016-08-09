<?php

$this->registerHelper('escape_value', function($invoker, $val, $padding=false) 
{
	if (null !== $val) {
		if (is_array($val)) {
			if (is_int($padding)) {
				$padding = str_repeat("\t", $padding);
			} elseif (false === $padding) {
				$padding = '';
			}
			$result = array();
			$result[] = 'array(';
			if (($n = count($val)) > 0 && array_keys($val) !== range(0, $n - 1)) {
				foreach($val as $k => $v) {
					$result[] = sprintf("\t%s => %s,", $invoker->refer('escape_value', $k), $invoker->refer('escape_value', $v, "\t$padding"));
				}
			} else {
				foreach($val as $v) {
					$result[] = sprintf("\t%s,",$invoker->refer('escape_value', $v, "\t$padding"));
				}
			}
			$result[] = ')';
			return implode("\n$padding", $result);
		} elseif (is_object($val)) {
			return $invoker->refer('escape_value', get_object_vars($val), $padding);
		} elseif (is_bool($val)) {
			return $val ? 'true' : 'false';
		} elseif (is_numeric($val)) {
			return strval($val);
		} else {
			return "'" . addslashes($val) . "'";
		}
	}
	return 'null';
});

$this->registerHelper('attribute_type', function ($invoker, $attribute)
{
	switch ($attribute->getType()) {
		case Codeforge\Attribute::TYPE_INT: return 'int';
		case Codeforge\Attribute::TYPE_DECIMAL: return 'float';
		case Codeforge\Attribute::TYPE_CHAR:
		case Codeforge\Attribute::TYPE_TEXT: return 'string';
		case Codeforge\Attribute::TYPE_BOOL: return 'boolean';
		case Codeforge\Attribute::TYPE_INTOPTION: return 'int';
		case Codeforge\Attribute::TYPE_STROPTION: return 'string';
		case Codeforge\Attribute::TYPE_CUSTOM: return $attribute->getCustomType();
	}
	return 'mixed';
});
