<?php
class Template {
	
	protected $variables = array();
	protected $_module;
	protected $_controller;
	protected $_action;
	protected $_layout = NULL;
	
	public $content;
	public $helpers;
	
	function __construct($controller,$action,$module='default') {
		$this->_module = $module;
		$this->_controller = $controller;
		$this->_action = $action;
	}

	/** Set Variables **/

	function set($name,$value)
	{
		$this->variables[$name] = $value;
	}

	function set_helpers($helper)
	{
		$this->helpers = $helper;
	}

	function set_layout($layout)
	{
		$this->_layout = $layout;
	}

	function get_view($filename)
	{
		extract($this->variables);
		if (is_file($filename))
		{
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		return false;
	}

	/** Display Template **/
	
	function render($disableLayout = FALSE, $controller = NULL, $action = NULL)
	{
		extract($this->variables);
		$controller = ($controller !== NULL) ? $controller : $this->_controller;
		$action = ($action !== NULL) ? $action : $this->_action;

		if (file_exists(MODULE_PATH . 'views' . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . '.php')) {
			$this->content = $this->get_view (MODULE_PATH . 'views' . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . '.php');
		}

		if ($disableLayout === FALSE)
		{
			if ($this->_layout === NULL)
			{
				$this->_layout = $controller;
			}
			if (file_exists(MODULE_PATH . 'views'. DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php')) {
				include (MODULE_PATH . 'views'. DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
			} else {
				include (MODULE_PATH . 'views'. DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'application.php');
			}
		}
		else
		{
			echo $this->content;
		}
  }

}