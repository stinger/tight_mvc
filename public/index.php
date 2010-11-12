<?php
define('APPLICATION_BASE', dirname(dirname(__FILE__)));

$url = (isset($_GET['url'])) ? $_GET['url'] : null;


require_once (APPLICATION_BASE . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php');