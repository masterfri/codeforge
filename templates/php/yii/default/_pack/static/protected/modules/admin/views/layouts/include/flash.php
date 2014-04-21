<?php

$flashes = Yii::app()->user->getFlashes();
if(! empty($flashes)) {
	foreach ($flashes as $key => $val) {
		if ('counters' == $key) continue;
		if (strpos($key, 'error') !== false) {
			$class = 'alert alert-danger';
		} elseif (strpos($key, 'message') !== false) {
			$class = 'alert alert-success';
		} elseif (strpos($key, 'warning') !== false) {
			$class = 'alert alert-warning';
		} else {
			$class = 'alert alert-info';
		}
		echo CHtml::openTag('div', array('class' => $class));
		echo '<button type="button" class="close" data-dismiss="alert">×</button>';
		echo $val;
		echo CHtml::closeTag('div');
	}
}
