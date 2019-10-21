<?php
namespace Molly;

use Phalcon\Mvc\User\Component;

class MenuStandart extends Component
{
	private $_menu;
	
	public function __construct()
	{
		
	}
	
	public function setMenu( $menu )
	{
		$this->_menu = $this->initMenu($menu);
	}
	
	private function initMenu( $menu )
	{
		foreach( $menu AS $menuItem )
		{
			$menuItem->link = $this->getDI()->getShared("url")->get($menuItem->link);
		}
		
		return $menu;
	}
	
	private function setCurrent( $menu, $controllerName, $actionName )
	{
		foreach( $menu AS $menuItem )
		{
			$itemCurrent = false;
			foreach( $menuItem->controllers AS $controller )
			{
				if( $controllerName == $controller->c AND $actionName == $controller->a )
				{
					$menuItem->current = true;
					$itemCurrent = true;
					$issetCurrent = true;
				}
			}
			
			if( !$itemCurrent )
			{
				$menuItem->current = false;
			}
		}
	}
	
	public function afterExecuteRoute()
	{
		$currentController = $this->getDI()->getShared("dispatcher")->getControllerName();
		$currentAction = $this->getDI()->getShared("dispatcher")->getActionName();
		
		$this->setCurrent($this->_menu, $currentController, $currentAction);
	}
	
	public function getMenu()
	{
		return $this->_menu;
	}
}