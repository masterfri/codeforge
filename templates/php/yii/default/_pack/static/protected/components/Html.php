<?php

class Html extends CHtml
{
	public static function normalizeOption($value, $default=null, $htmlencode=false)
	{
		if (preg_match('/[$][{]([a-z0-9_]+)[.]([a-z0-9_]+)[}]/', $value, $matches)) {
			return self::opt($matches[2], $matches[1], $default, $htmlencode);
		}
		return $value;
	}
	
	public static function opt($name, $category, $default=null, $htmlencode=true)
	{
		$class = ucfirst($category) . 'Options';
		return $htmlencode ? 
			self::encode(self::value($class::instance(), $name, $default=null)) :
			self::value($class::instance(), $name, $default=null);
	}
	
	public static function transparentPixel()
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90LFAsVB29byN4AAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAA1JREFUCNdj+P//PwMACPwC/lyfz9oAAAAASUVORK5CYII=';
	}
	
	public static function imgUrl($model, $width=0, $height=0, $default=null)
	{
		if ($model instanceof File && $model->getIsImage()) {
			if ($width || $height) {
				return $model->getUrlResized($width, $height);
			} else {
				return $model->getUrl();
			}
		}
		return null !== $default ? $default : self::transparentPixel();
	}
	
	public static function img($model, $alt='', $htmlOptions=array(), $width=0, $height=0, $default=false)
	{
		$url = self::imgUrl($model, $width, $height, $default);
		if ($url !== false) {
			if ($model instanceof File && $model->getIsImage()) {
				if (!isset($htmlOptions['width']) && !isset($htmlOptions['height'])) {
					$htmlOptions['width'] = $model->width;
					$htmlOptions['height'] = $model->height;
				}
			}
			return CHtml::image($url, $alt, $htmlOptions);
		}
		return '';
	}
	
	public static function lightHtml($value, $category=null, $default=null)
	{
		if (null !== $category) {
			$value = self::opt($value, $category, $default, false);
		}
		$value = preg_replace('#<br\s*/?>#i', "\n", $value);
		$value = preg_replace('#<(/?[abisu])>#i', '{{$1}}', $value);
		$value = preg_replace('#<(a\s[^>]+)>#i', '{{$1}}', $value);
		$value = preg_replace('#</?[a-z]+[^<>]*>#i', '', $value);
		$value = str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $value);
		$value = str_replace(array('{{', '}}'), array('<', '>'), $value);
		return nl2br($value);
	}
	
	public static function number($value, $category=null, $default=null)
	{
		if (null !== $category) {
			$value = self::opt($value, $category, $default, false);
		}
		return number_format((float) $value, 0, '', ' ');
	}
}
