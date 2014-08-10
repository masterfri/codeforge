<?php

class Controller extends CController
{
	protected $_breadcrumbs;
	protected $_pageHeading;
	
	public function setBreadcrumbs(array $value)
	{
		$this->_breadcrumbs = array();
		foreach ($value as $label => $url) {
			if(is_string($label)) {
				if (false !== $url) {
					$this->_breadcrumbs[$label] = $url;
				} else {
					$this->_breadcrumbs[] = $label;
				}
			} else {
				$this->_breadcrumbs[] = $url;
			}
		}
	}
	
	public function getBreadcrumbs()
	{
		if (null === $this->_breadcrumbs) {
			$this->_breadcrumbs = empty($this->_pageHeading) ? array() : array($this->_pageHeading);
		}
		return $this->_breadcrumbs;
	}
	
	public function setPageHeading($value)
	{
		$this->_pageHeading = $value;
	}
	
	public function getPageHeading()
	{
		if (null === $this->_pageHeading) {
			$this->_pageHeading = empty($this->_breadcrumbs) ? '' : end($this->_breadcrumbs);
		}
		return $this->_pageHeading;
	}
	
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
	
	protected function loadModel($id, $class)
	{
		if (is_array($id)) {
			$model = $class::model()->findByAttributes($id);
		} else {
			$model = $class::model()->findByPk($id);
		}
		if (!$model) {
			throw new CHttpException(404, Yii::t('site', "$class not found"));
		}
		return $model;
	}
	
	protected function createSearchModel($class)
	{
		$model = new $class('search');
		$model->unsetAttributes();
		if (isset($_GET[$class])) {
			$model->attributes = $_GET[$class];
		}
		return $model;
	}
	
	protected function saveModel($model, $ajaxValidation=false)
	{
		if ($ajaxValidation) {
			$this->performAjaxValidation($model);
		}
		$class = get_class($model);
		if (isset($_POST[$class])) {
			$model->attributes = $_POST[$class];
			return $model->save();
		}
		return false;
	}
	
	protected function performAjaxValidation($model)
	{
		$id = strtolower(get_class($model)) . '-form';
		if(isset($_POST['ajax']) && $_POST['ajax'] === $id) {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public function getOption($class, $optname=null, $nice=true)
	{
		if (null === $optname) {
			return $class::instance();
		} elseif ($nice) {
			return $class::instance()->getNiceValue($optname);
		} else {
			return $class::instance()->$optname;
		}
	}
}
