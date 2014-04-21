<?php

Yii::import('zii.widgets.CListView');

class ListView extends CListView
{
	public $template = '{items} {pager}';
	public $cssFile = false;
	public $pagerCssClass = 'pagination-wrapper';
	public $pager = array(
		'cssFile' => false,
		'header' => '',
		'htmlOptions' => array(
			'class' => 'pagination',
		),
		'hiddenPageCssClass' => 'disabled',
		'selectedPageCssClass' => 'active',
		'firstPageLabel' => '&lt;&lt;',
		'lastPageLabel' => '&gt;&gt;',
		'prevPageLabel' => '&lt;',
		'nextPageLabel' => '&gt;',
	);
}
