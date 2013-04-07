<?php

function mysql_attr_type($attribute)
{
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT: 
			return 'INTEGER';
			
		case Attribute::TYPE_DECIMAL: 
			return 'DECIMAL' . (is_array($attribute->getSize()) ? sprintf('(%s)', implode(',', $attribute->getSize())) : '');
			
		case Attribute::TYPE_CHAR:
			return sprintf('VARCHAR(%d)', $attribute->getSize() ? $attribute->getSize() : 250);
			
		case Attribute::TYPE_TEXT: 
			return 'TEXT';
			
		case Attribute::TYPE_BOOL: 
			return 'TINYINT';
			
		case Attribute::TYPE_INTOPTION: 
			return $attribute->getIsCollection() ? 'TEXT' : 'INTEGER';
			
		case Attribute::TYPE_STROPTION: 
			return $attribute->getIsCollection() ? 'TEXT' : sprintf('VARCHAR(%d)', max_option_len($attribute->getOptions(), 10));
			
		case Attribute::TYPE_CUSTOM: 
			switch ($attribute->getCustomType()) {
				case 'email':
					return 'VARCHAR(100)';
				
				case 'date':
					return 'DATE';
					
				case 'datetime':
					return 'DATETIME';
					
				case 'time':
					return 'VARCHAR(10)';
					
				case 'phoneNumber':
					return 'VARCHAR(20)';
				
				case 'file':
					return 'VARCHAR(200)';
			}
	}
	return 'VARCHAR(256) /* UNRECOGNIZED TYPE */';
}

function mysql_escape_value($val) 
{
	if (null !== $val) {
		if (is_bool($val)) {
			return $val ? '1' : '0';
		} elseif (is_numeric($val)) {
			return strval($val);
		} else {
			return '"' . addslashes($val) . '"';
		}
	}
	return 'NULL';
}
