<?php
include_once('../app/bootstrap.php');
include_once('../app/config/config.php');
$app = new App(APP_PATH,APPLICATION_ENV,$config);
$app->run();