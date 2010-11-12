<?php

define('APPLICATION_BASE', dirname(dirname(__FILE__)));

require_once(APPLICATION_BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

#$backupFile = DB_NAME.'_data'.date("-YmdHis").'.db';
#$command = 'mysqldump --opt -h'.DB_HOST.' -u'.DB_USER.' -p'.DB_PASSWORD.' '.DB_NAME.' no-data add-drop-table > '.$backupFile;
#system($command);

$backupFile = APPLICATION_BASE . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . DB_NAME . date("-YmdHis") . '.sql';
$command = 'mysqldump --opt -h'.DB_HOST.' -u'.DB_USER.' -p'.DB_PASSWORD.' '.DB_NAME.' > '.$backupFile;
system($command);

