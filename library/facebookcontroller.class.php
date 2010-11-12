<?php
class FacebookController extends ApplicationController
{
	public $fb = null;
	public $fbUID = null;
	public $fbSession = null;

	public $render;

	protected $_template;



	function __construct($controller, $action, $module='default')
	{
		parent::__construct($controller, $action, $module);

		$this->fb = new Facebook(array('appId' => FB_APP_ID, 'secret' => FB_SECRET, 'cookie' => TRUE));

		$is_tab = isset($_POST['fb_sig_in_profile_tab']);

		if (!$is_tab)
		{
			if ($this->fb->getUser() == NULL)
			{
				global $url;
				$actionUrl = $controller.'\/'.$action.'\/';
				if (!$module === NULL)
				{
					$actionUrl = $module.'\/'.$actionUrl;
				}
				$queryStr = preg_replace("/{$actionUrl}/", '', $url);
				$parts = explode('/',$queryStr);

				if (!in_array('ajax',$parts))
				{
					echo '<fb:redirect url="'.$this->fb->getLoginUrl(array('req_perms'=>'publish_stream,user_likes','next'=>FB_APP_URL,'cancel_url'=>FB_CANCEL_URL)).'" />';
				}
			}
			else
			{
				try
				{
					$this->fbSession = $this->fb->getSession();
					$this->fbUID = $this->fb->getUser();
					$this->fb->api('/me');
				}
				catch (Exception $e)
				{
					echo '<fb:redirect url="'.$this->fb->getLoginUrl(array('req_perms'=>'publish_stream,user_likes','next'=>FB_APP_URL,'cancel_url'=>FB_CANCEL_URL)).'" />';
				}
			}
		}
	}
}
