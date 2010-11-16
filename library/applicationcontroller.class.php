<?php
class ApplicationController
{
	protected $_module;
	protected $_controller;
	protected $_action;
	protected $_template;

	private $disableLayout;
	private $render;

	function __construct($controller, $action, $module='default')
	{
		global $inflect;

		$this->_module = $module;
		$this->_controller = ucfirst($controller);
		$this->_action = $action;
		
		$model = ucfirst($inflect->singularize($controller));
		$helper = ucfirst($controller).'Helper';

		if (!file_exists(MODULE_PATH . 'helpers' . DIRECTORY_SEPARATOR . strtolower($helper) . '.php'))
		{
			$helper = 'ApplicationHelper';
		}
		$this->$helper = new $helper;
		
		$this->_template = new Template($controller,$action,$module);
		$this->_template->set_helpers($this->$helper);
		$this->_template->set('helpers', $this->$helper);

		if (file_exists(MODULE_PATH . 'models' . DIRECTORY_SEPARATOR . strtolower($model) . '.php'))
		{
			$this->$model = new $model;
		}


		$this->render = 1;
		$this->disableLayout = 0;
	}

	function set_render($render)
	{
		$this->render = $render;
	}

	function disable_layout()
	{
		$this->disableLayout = 1;
		return $this;
	}

	function set($name,$value)
	{
		$this->_template->set($name,$value);
		return $this;
	}

	function layout_as($layout)
	{
		$this->_template->set_layout($layout);
		return $this;
	}

	function render($controller, $action)
	{
		$this->_template->render($this->disableLayout,$controller, $action);
		$this->set_render(0);
		return $this;
	}

	function __destruct()
	{
		if ($this->render)
		{
			$this->_template->render($this->disableLayout);
		}
	}
		
}