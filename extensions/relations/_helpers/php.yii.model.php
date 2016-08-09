<?php

$this->registerHelper('behaviors', function ($invoker, $model)
{
	$result = $invoker->referSuper();
	$relations = array();
	foreach ($model->getAttributes() as $attribute) {
		if ($attribute->getType() == Codeforge\Attribute::TYPE_CUSTOM) {
			$relation = $invoker->refer('attribute_relation', $attribute);
			if ('many-to-many' == $relation || 'one-to-one' == $relation) {
				if ($attribute->getBoolHint('cascadesave')) {
					$relations[$attribute->getName()] = array('cascadeSave' => true);
				} else {
					$relations[] = $attribute->getName();
				}
			} elseif ('many-to-one' == $relation) {
				$relations[] = $attribute->getName();
			} elseif ('one-to-many' == $relation) {
				$params = array();
				if ($attribute->getBoolHint('cascadesave')) {
					$params['cascadeSave'] = true;
				}
				if ($attribute->getBoolHint('cascadedelete')) {
					$params['cascadeDelete'] = true;
					if ($attribute->getBoolHint('quickdelete')) {
						$params['quickDelete'] = true;
					}
				}
				if (!empty($params)) {
					$relations[$attribute->getName()] = $params;
				} else {
					$relations[] = $attribute->getName();
				}
			}
		}
	}
	if (count($relations)) {
		$result[] = array(
			'class' => 'RelationBehavior',
			'attributes' => $relations,
		);
	}
	return $result;
}, 100, '::php::yii::model');
