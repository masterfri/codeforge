<?php

class HtmlencodeAttributeBehavior extends AttributeBehavior
{
	public function getNiceValue($value, $attribute, array $args=array())
	{
		return CHtml::encode($value);
	}
}
