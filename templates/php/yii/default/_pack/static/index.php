<?php

defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('DEBUG_MAIL') or define('DEBUG_MAIL',true);

require_once('../yii/yii.php');

date_default_timezone_set('Europe/Helsinki');

$config = 'protected/config/main.php';

Yii::createWebApplication($config)->run();
