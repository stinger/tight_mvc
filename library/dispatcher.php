<?php

/** Check if environment is development and display errors **/

function set_reporting()
{
	if (DEVELOPMENT_ENVIRONMENT == true)
	{
		error_reporting(E_ALL);
		ini_set('display_errors','On');
	}
	else
	{
		error_reporting(E_ALL);
		ini_set('display_errors','Off');
		ini_set('log_errors', 'On');
		ini_set('error_log', APPLICATION_BASE . DIRECTORY_SEPARATOR . 'tmp'. DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'error.log');
	}
}

/** Check for Magic Quotes and remove them **/

function deeply_stripslashes($value)
{
	$value = is_array($value) ? array_map('deep_stripslashes', $value) : stripslashes($value);
	return $value;
}

function remove_magic_quotes() {
if ( get_magic_quotes_gpc() ) {
	$_GET    = deeply_stripslashes($_GET   );
	$_POST   = deeply_stripslashes($_POST  );
	$_COOKIE = deeply_stripslashes($_COOKIE);
}
}

/** Check register globals and remove them **/

function unregister_globals() {
    if (ini_get('register_globals')) {
        $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
					if (!empty($GLOBALS[$value])){
            foreach ($GLOBALS[$value] as $key => $var) {
                if ((isset($GLOBALS[$key])) && ($var === $GLOBALS[$key]) && (!in_array($key,array('url','default','routing','inflect','irregularWords')))) {
                    unset($GLOBALS[$key]);
                }
            }
					}
        }
    }
}

/** Secondary Call Function **/

function perform_action($controller,$action,$queryString = null,$render = 0)
{
	$controllerName = ucfirst($controller).'Controller';
	$dispatch = new $controllerName($controller,$action);
	$dispatch->render = $render;
	return call_user_func_array(array($dispatch,$action),$queryString);
}

/** Routing **/

function route_url($url)
{
	global $routing;
	foreach ( $routing as $pattern => $result )
	{
		if (preg_match($pattern, $url))
		{
			return preg_replace($pattern, $result, $url);
		}
	}
	return ($url);
}

/** Main Call Function **/

function call_hook()
{
	global $url;
	global $default;

	$queryString = array();

	if (!isset($url))
	{
		$module = $default['module'];
		$controller = $default['controller'];
		$action = $default['action'];
	}
	else
	{
		$controller = $default['controller'];
		$url = route_url($url);
		$urlArray = array();
		$urlArray = explode("/",$url);
		$module = $urlArray[0];
		if ((strtolower($module) != $default['module']) && (!is_dir(APPLICATION_BASE . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR. $module)))
		{
			$module = NULL;
		}
		else
		{
			array_shift($urlArray);
		}

		$controller = $urlArray[0];
		array_shift($urlArray);
		if (empty($controller))
		{
			$controller = $default['controller'];
		}

		if (!empty($urlArray[0]))
		{
			$action = $urlArray[0];
			array_shift($urlArray);
		}
		else
		{
			$action = 'index'; // Default Action
		}
		$queryString = $urlArray;
	}
	
	$controllerName = ucfirst($controller).'Controller';
	$dispatch = new $controllerName($controller,$action,CURRENT_MODULE);
	if ((int)method_exists($controllerName, $action))
	{
		if((int)method_exists($controllerName,'before_action'))
		{
			call_user_func_array(array($dispatch,"before_action"),$queryString);
		}
		call_user_func_array(array($dispatch,$action),$queryString);
		if((int)method_exists($controllerName,'after_action'))
		{
			call_user_func_array(array($dispatch,"after_action"),$queryString);
		}
	}
	else
	{
		/* Error Generation Code Here */
		$dispatch->render = 0;
		$url = '/'.$url;
		include_once (dirname(__FILE__).'/../public/404.php');
	}
}

/** Autoload any classes that are required **/


function __autoload($className)
{
	if (file_exists(APPLICATION_BASE . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . strtolower($className) . '.class.php'))
	{
		require_once(APPLICATION_BASE . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . strtolower($className) . '.class.php');
	}
	else if (file_exists(MODULE_PATH . 'controllers' . DIRECTORY_SEPARATOR . strtolower($className) . '.php'))
	{
		require_once(MODULE_PATH . 'controllers' . DIRECTORY_SEPARATOR . strtolower($className) . '.php');
	}
	else if (file_exists(MODULE_PATH . 'models' . DIRECTORY_SEPARATOR . strtolower($className) . '.php'))
	{
		require_once(MODULE_PATH . 'models' . DIRECTORY_SEPARATOR . strtolower($className) . '.php');
	}
	else if (file_exists(MODULE_PATH . 'helpers' . DIRECTORY_SEPARATOR . strtolower($className) . '.php'))
	{
		require_once(MODULE_PATH . 'helpers' . DIRECTORY_SEPARATOR . strtolower($className) . '.php');
	}
	else
	{
		global $url;
		/* Error Generation Code Here */
		$url = '/'.$url;
		if (!preg_match('/Controller/',$className))
		{
			header("HTTP/1.0 500 Internal Server Error");
			include_once (dirname(__FILE__).'/../public/500.php');
		}
		else
		{
			header("HTTP/1.0 404 Not Found");
			include_once (dirname(__FILE__).'/../public/404.php');
		}
		exit();
	}
}


/** GZip Output **/

function gzip_output()
{
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ') || false !== strpos($ua, 'Opera'))
	{
		return false;
	}
	$version = (float)substr($ua, 30);
	return (($version < 6) || ($version == 6  && false === strpos($ua, 'SV1')));
}

function get_module()
{
	global $url;
	global $default;

	if (!isset($url)) {
		$module = $default['module'];
	}
	else
	{
		$url = route_url($url);
		$urlArray = array();
		$urlArray = explode("/", $url);
		$module = $urlArray[0];
	}
	if (!is_dir(APPLICATION_BASE . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR. $module))
	{
		$module = null;
	}

	$scopedAppPath = APPLICATION_BASE . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR;
	if ((!empty($module)) && (is_dir($scopedAppPath . $module)))
	{
		$scopedAppPath .= $module . DIRECTORY_SEPARATOR;
	}
	define ('MODULE_PATH', $scopedAppPath);
	define ('CURRENT_MODULE', $module);
}

/** Get Required Files **/
get_module();
gzip_output() || ob_start("ob_gzhandler");
$cache = new Cache();
$inflect = new Inflection();

set_reporting();
remove_magic_quotes();
unregister_globals();
call_hook();


?>