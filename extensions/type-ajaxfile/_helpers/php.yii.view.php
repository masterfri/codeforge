<?php

$this->registerHelper('form_control', function ($invoker, $attribute, $mode='')
{
    if ($attribute->getIsCustomType()) {
        if ('ajaxfile' == $attribute->getCustomType()) {
            if($attribute->getBoolHint('hidden')) {
                return false;
            }
            if ('update' == $mode && $attribute->getBoolHint('readonly')) {
                return false;
            }
            return array('extensions.type-ajaxfile.form-control-ajaxfile', array(
                'attribute' => $attribute,
                'mode' => $mode,
            ));
        }
    }
    return $invoker->referSuper();
}, 100, '::php::yii::view');

$this->registerHelper('detail_view_attributes', function ($invoker, $attribute)
{
    if ($attribute->getIsCustomType()) {
        if ('ajaxfile' == $attribute->getCustomType()) {
            return array(
                sprintf("array('name' => '%s', 'value' => \$model->getFileLink('%s'), 'type' => 'raw')", $attribute->getName(), $attribute->getName()),
            );
        }
    }
    return $invoker->referSuper();
}, 100, '::php::yii::view');
