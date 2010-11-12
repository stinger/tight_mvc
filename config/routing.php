<?php

$routing = array(
	'/admin\/(.*?)\/(.*?)\/(.*)/' => 'admin/\1_\2/\3'
);

$default['module'] = 'default';
$default['controller'] = 'index';
$default['action'] = 'index';