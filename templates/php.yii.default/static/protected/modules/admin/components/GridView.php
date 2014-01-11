<?php

Yii::import('zii.widgets.grid.CGridView');

class GridView extends CGridView
{
	public $htmlOptions = array('class'=>'content');
	public $itemsCssClass = 'table table-striped table-condensed grid-view';
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
