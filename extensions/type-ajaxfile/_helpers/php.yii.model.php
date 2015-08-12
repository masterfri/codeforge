<?php

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute)
{
    if ($attribute->getIsCustomType()) {
        if ('ajaxfile' == $attribute->getCustomType()) {
            $rules = $invoker->referSuper();
            $rules[] = "'safe'";
            return $rules;
        }
    }
    return $invoker->referSuper();
}, 100, '::php::yii::model');