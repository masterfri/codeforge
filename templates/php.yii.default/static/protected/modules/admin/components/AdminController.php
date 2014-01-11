<?php

class AdminController extends Controller
{
	public $layout = 'admin.views.layouts.default';
	public $menu = array();
	
	public function init()
	{
		parent::init();
		$this->pageTitle = 'Администрирование сайта';
		Yii::app()->user->loginUrl = $this->createUrl('/admin/default/login');
	}
	
	public function accessRules()
	{
		return array(
			array('allow',
				'roles' => array('admin'),
			),
			array('deny'),
		);
	}
	
	public function initMCE()
	{
		$tiny_opts = array(
			'mode' => 'specific_textareas',
			'theme' => 'advanced',
			'editor_selector' => 'rich',
			'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,formatselect,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,images,code,|,forecolor,backcolor,removeformat,sub,sup,|,charmap',
			'plugins' => 'images,inlinepopups',
		);
		$cs = Yii::app()->clientScript;
		$cs->registerScriptFile(Yii::app()->request->baseUrl . '/rc/tiny_mce/tiny_mce.js');
		$cs->registerScript('tinyMce', 'tinyMCE.init(' . CJSON::encode($tiny_opts) . ')', CClientScript::POS_HEAD);
	}
}
