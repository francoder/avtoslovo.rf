<?php
namespace Molly\Backend\Controllers;

use Phalcon\Mvc\Dispatcher,
	Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
	private $_breadcrumbs = array();
	
	public function initialize()
	{
		$this->tag->setTitle($this->application->title);
	}
	
	public function setTitle( $title, $mdash = true )
	{
		if( $mdash )
			$titleStr = $title . " | ";
		else
			$titleStr = $title;
		
		$this->tag->prependTitle($titleStr);
	}
	
	public function addBreadCrumbs( $title, $link )
	{
		$this->_breadcrumbs[] = array(
			"title" => $title,
			"link" => $link,
		);
	}
	
	public function afterExecuteRoute( Dispatcher $dispatcher )
	{
		$this->view->breadcrumbs = $this->_breadcrumbs;
		$this->menu->afterExecuteRoute();
	}
}