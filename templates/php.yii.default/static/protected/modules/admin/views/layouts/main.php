<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo CHtml::encode(Yii::app()->name); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="/rc/bootstrap/css/bootstrap.css" type="text/css">
		<link rel="stylesheet" href="/rc/bootstrap/css/bootstrap-theme.css" type="text/css">
		<link rel="stylesheet" href="/rc/css/admin.css" type="text/css">
		
		<?php
			$cs = Yii::app()->clientScript;
			$cs->registerCoreScript('jquery');
			$cs->registerScriptFile('/rc/bootstrap/js/bootstrap.js', CClientScript::POS_END);
			$cs->registerScriptFile('/rc/js/admin.js', CClientScript::POS_END);
		?>
		
	</head>
	<body>
		<?php echo $content; ?>
	</body>
</html>

