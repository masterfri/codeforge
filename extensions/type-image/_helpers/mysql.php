<?php

$this->registerHelper('table_name', function ($invoker, $model)
{
	$type = is_string($model) ? $model : $model->getName();
	if ('image' == $type) {
		return $invoker->referSuper($invoker->getEnv('type.image.model.class', 'File'));
	}
	return $invoker->referSuper();
}, 100, '::mysql');
