<?php

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Attribute::TYPE_INT:
			return ($attribute->getIsUnsigned() ? 'unsigned ' : '') . 'int';
					
		case Attribute::TYPE_DECIMAL: 
			return ($attribute->getIsUnsigned() ? 'unsigned ' : '') . 'decimal' . (is_array($attribute->getSize()) ? sprintf('(%s)', implode(',', $attribute->getSize())) : '');
		
		case Attribute::TYPE_CHAR:
			return 'char' . ($attribute->getSize() ? sprintf('(%d)', $attribute->getSize()) : '');
					
		case Attribute::TYPE_TEXT: 
			return 'text';
					
		case Attribute::TYPE_BOOL: 
			return 'bool';
					
		case Attribute::TYPE_INTOPTION: 
			return 'option';
			
		case Attribute::TYPE_STROPTION: 
			return 'enum';
		
		case Attribute::TYPE_CUSTOM:
			return 'custom';
	}
});
