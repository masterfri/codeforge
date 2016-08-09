<?php

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	switch ($attribute->getType()) {
		case Codeforge\Attribute::TYPE_INT:
			return ($attribute->getIsUnsigned() ? 'unsigned ' : '') . 'int';
					
		case Codeforge\Attribute::TYPE_DECIMAL: 
			return ($attribute->getIsUnsigned() ? 'unsigned ' : '') . 'decimal' . (is_array($attribute->getSize()) ? sprintf('(%s)', implode(',', $attribute->getSize())) : '');
		
		case Codeforge\Attribute::TYPE_CHAR:
			return 'char' . ($attribute->getSize() ? sprintf('(%d)', $attribute->getSize()) : '');
					
		case Codeforge\Attribute::TYPE_TEXT: 
			return 'text';
					
		case Codeforge\Attribute::TYPE_BOOL: 
			return 'bool';
					
		case Codeforge\Attribute::TYPE_INTOPTION: 
			return 'option';
			
		case Codeforge\Attribute::TYPE_STROPTION: 
			return 'enum';
		
		case Codeforge\Attribute::TYPE_CUSTOM:
			return 'custom';
	}
});
