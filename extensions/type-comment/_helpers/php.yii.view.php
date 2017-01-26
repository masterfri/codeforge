<?php

$this->registerHelper('form_control', function ($invoker, $attribute, $mode='')
{
    if ($attribute->getIsCustomType()) {
        if ('comment' == $attribute->getCustomType()) {
            if($attribute->getBoolHint('hidden')) {
                return false;
            }
            if ('update' == $mode && $attribute->getBoolHint('readonly')) {
                return false;
            }
            return array('extensions.type-comment.form-control-comment', array(
                'attribute' => $attribute,
                'mode' => $mode,
            ));
        }
    }
    return $invoker->referSuper();
}, 100, '::php::yii::view');