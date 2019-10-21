<?php
namespace Molly\Backend\Plugins;

use Phalcon\Events\Event,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Mvc\User\Plugin;

class Authorization extends Plugin
{
	public function beforeDispatch( Event $event, Dispatcher $dispatcher )
	{
		if( !$this->auth->isAuthorized() AND 
			( $dispatcher->getControllerName() != "session" OR $dispatcher->getActionName() != "login" ) )
		{
			return $this->dispatcher->forward(array(
				"controller"	=> "session",
				"action"		=> "login",
			));
		}
		
		if( ( $this->auth->isAuthorized() AND $this->auth->getCurrent()->group->admin != "Y" ) AND 
			( $dispatcher->getControllerName() != "error" OR $dispatcher->getActionName() != "notAllowed" ) )
		{
			return $this->dispatcher->forward(array(
				"controller"	=> "error",
				"action"		=> "notAllowed",
			));
		}
	}
}