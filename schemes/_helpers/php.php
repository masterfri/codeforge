<?php

function php_attr_privacy($attribute) 
{
	switch ($attribute->getPrivacy()) {
		case Attribute::PRIV_PRIVATE: return 'private';
		case Attribute::PRIV_PROTECTED: return 'protected';
	}
	return 'public';
}

function php_escape_value($val) 
{
	if (null !== $val) {
		if (is_bool($val)) {
			return $val ? 'true' : 'false';
		} elseif (is_numeric($val)) {
			return strval($val);
		} else {
			return '"' . addslashes($val) . '"';
		}
	}
	return 'null';
}

function php_attr_type_name($attribute)
{
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT: return 'int';
		case Attribute::TYPE_DECIMAL: return 'float';
		case Attribute::TYPE_CHAR:
		case Attribute::TYPE_TEXT: return 'string';
		case Attribute::TYPE_BOOL: return 'boolean';
		case Attribute::TYPE_INTOPTION: return 'int';
		case Attribute::TYPE_STROPTION: return 'string';
		case Attribute::TYPE_CUSTOM: return $attribute->getCustomType();
	}
	return 'mixed';
}
