<?php
namespace Molly\Frontend\Controllers;

use Phalcon\Mvc\Dispatcher,
	Phalcon\Mvc\Controller,
	Molly\Models\CatalogUserPart,
        Molly\Models\CatalogUserVin,
        Molly\Models\CatalogUserFrame;

class ControllerBase extends Controller
{
	private $_breadcrumbs = array();
	
	public function initialize()
	{
		$this->tag->setTitle($this->application->title);
		
		# Cart
		if( $this->auth->isAuthorized() )
		{
			$partList = CatalogUserPart::find(array(
				"conditions" => "userId = :userId:",
				"bind" => array(
					"userId" => $this->auth->getCurrentId(),
				),
				"order" => "executedAt DESC",
                "limit" => 20,
			));
			
            $vinList = CatalogUserVin::find(array(
				"conditions" => "userId = :userId:",
				"bind" => array(
                     "userId" => $this->auth->getCurrentId(),
				),
				"order" => "executedAt DESC",
                "limit" => 20,

			));

            $vinListLastFive = CatalogUserVin::find(array(
                "conditions" => "userId = :userId:",
                "bind" => array(
                    "userId" => $this->auth->getCurrentId(),
                ),
                "order" => "executedAt DESC",
                "limit" => 5,
            ));
                        
            $frameList = CatalogUserFrame::find(array(
				"conditions" => "userId = :userId:",
				"bind" => array(
                       "userId" => $this->auth->getCurrentId(),
				),
				"order" => "executedAt DESC",
                "limit" => 20,
			));
                        
			$this->view->partList = $partList;
                        $this->view->vinList = $vinList;
                        $this->view->vinListLastFive = $vinListLastFive;
                        $this->view->frameList = $frameList;
                        
		}
		else
		{
			$this->view->partList = array();
                        $this->view->vinList = array();
                        $this->view->frameList = array();
		}
	}
	
	public function setTitle( $title, $mdash = true )
	{
		if( $mdash )
			$titleStr = $title . " — ";
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
		
		$this->view->cartCount = $this->cart->getCount();
	}
}