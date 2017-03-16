<?php

$this->registerHelper('attribute_validation_rules', function ($invoker, $attribute)
{
    if ($attribute->getIsCustomType()) {
        if ('comment' == $attribute->getCustomType()) {
            $rules = $invoker->referSuper();
            $rules[] = "'safe'";

            if ($attribute->getHint('length')) {
                $rules[] = sprintf("'length', 'max' => %d", intval($attribute->getHint('length')));
            }
            return $rules;
        }
    }
    return $invoker->referSuper();
}, 100, '::php::yii::au_model');