<?php

$this->registerHelper('attribute_type', function ($invoker, $attribute) 
{
	if ($attribute->getIsCustomType()) {
		if (in_array($attribute->getCustomType(), array('date', 'time', 'datetime'))) {
			switch ($attribute->getCustomType()) {
				case 'date':
					return 'DATE';
				case 'time':
					return 'TIME';
				default:
					return 'DATETIME';
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::mysql');
