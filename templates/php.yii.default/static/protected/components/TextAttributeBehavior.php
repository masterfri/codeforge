<?php

class TextAttributeBehavior extends AttributeBehavior
{
	public function getNiceValue($value, $attribute, array $args=array())
	{
		$value = preg_replace('#<br\s*/?>#i', "\n", $value);
		$value = preg_replace('#<\?[a-z]+[^<>]*>#i', "", $value);
		$value = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $value);
		return nl2br($value);
	}
}
