<?php

class ImageAttributeBehavior extends AttributeBehavior
{
	protected $_img_model = array();
	
	public function getNiceValue($value, $attribute, array $args=array())
	{
		$model = $this->getImage($value, $attribute);
		if ($model) {
			$format = array_shift($args);
			if (null === $format || $format == 'imageurl') {
				return $model->getUrl();
			} elseif ($format == 'image') {
				$htmlOptions = array_shift($args);
				return CHtml::image($model->getUrl(), '', $htmlOptions ? $htmlOptions : array());
			} elseif ($format == 'thumbnailurl') {
				$w = (int) array_shift($args);
				$h = (int) array_shift($args);
				return $model->getUrlResized($w, $h);
			} elseif ($format == 'thumbnail') {
				$w = (int) array_shift($args);
				$h = (int) array_shift($args);
				$htmlOptions = array_shift($args);
				return CHtml::image($model->getUrlResized($w, $h), '', $htmlOptions ? $htmlOptions : array());
			} else {
				return $model;
			}
		}
		return '';
	}
	
	protected function getImage($value, $attribute)
	{
		if (! array_key_exists($attribute, $this->_img_model)) {
			$img = File::model()->findByPk($value);
			if (!$img || strpos($img->mime, 'image/') !== 0) {
				$this->_img_model[$attribute] = false;
			} else {
				$this->_img_model[$attribute] = $img;
			}
		}
		return $this->_img_model[$attribute];
	}
}
