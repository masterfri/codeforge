<?php

Yii::import('zii.widgets.CDetailView');

class DetailView extends CDetailView
{
	public $htmlOptions = array(
		'class' => 'table table-striped table-bordered table-condensed detailed-view'
	);
}
