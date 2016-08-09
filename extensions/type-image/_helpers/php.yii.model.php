<?php

$this->registerHelper('relations', function ($invoker, $attribute, $model)
{
	if ($attribute->getIsCustomType()) {
		if ('image' == $attribute->getCustomType()) {
			$type_class = $invoker->getEnv('type.image.model.class', 'File');
			if ($attribute->getIsCollection()) {
				$link = $attribute->getHint('manymanylink');
				if ($link && preg_match('#([a-z0-9_]+)\s*[(]\s*([a-z0-9_]+)\s*,\s*([a-z0-9_]+)\s*[)]#i', $link, $matches)) {
					$table_name = $invoker->refer('table_name', $matches[1]);
					$fk1 = sprintf('%s_id', $matches[2]);
					$fk2 = sprintf('%s_id', $matches[3]);
				} else {
					$table_name = sprintf('%s_%s_%s', $invoker->refer('table_name', $attribute->getOwner()), $invoker->refer('table_name', $type_class), $attribute->getName());
					$fk1 = sprintf('%s_id', $invoker->refer('table_name', $attribute->getOwner()));
					$fk2 = sprintf('%s_id', $invoker->refer('table_name', $type_class));
				}
				return array(
					sprintf("array(self::MANY_MANY, '%s', '{{%s}}(%s,%s)')", $type_class, $table_name, $fk1, $fk2),
				);
			} else {
				return array(
					sprintf("array(self::BELONGS_TO, '%s', '%s_id')", $type_class, $attribute->getName()),
				);
			}
		}
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');

$this->registerHelper('table_name', function ($invoker, $model)
{
	$type = is_string($model) ? $model : $model->getName();
	if ('image' == $type) {
		return $invoker->referSuper($invoker->getEnv('type.image.model.class', 'File'));
	}
	return $invoker->referSuper();
}, 100, '::php::yii::model');
