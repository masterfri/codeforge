<?php

$this->registerHelper('relations', function ($invoker, $attribute, $model)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			if ($attribute->getIsCollection()) {
				$link = $attribute->getHint('manymanylink');
				if ($link && preg_match('#([a-z0-9_]+)\s*[(]\s*([a-z0-9_]+)\s*,\s*([a-z0-9_]+)\s*[)]#i', $link, $matches)) {
					$table_name = $invoker->refer('table_name', $matches[1]);
					$fk1 = sprintf('%s_id', $matches[2]);
					$fk2 = sprintf('%s_id', $matches[3]);
				} else {
					$table_name = sprintf('%s_%s_image', $invoker->refer('table_name', $attribute->getOwner()), $attribute->getName());
					$fk1 = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
					$fk2 = 'image_id';
				}
				return array(
					sprintf("array(self::MANY_MANY, '%s', '{{%s}}(%s,%s)')", $invoker->getEnv('type.image.model.class', 'File'), $table_name, $fk1, $fk2),
				);
			} else {
				return array(
					sprintf("array(self::BELONGS_TO, '%s', '%s_id')", $invoker->getEnv('type.image.model.class', 'File'), $attribute->getName()),
				);
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
