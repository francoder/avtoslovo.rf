<?php
namespace Molly;

use Phalcon\Mvc\User\Component;

class MenuGroupped extends Component
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
			switch( $menuItem->type )
			{
				case "item":
					$menuItem->link = $this->getDI()->getShared("url")->get($menuItem->link);
					break;
				
				case "group":
					$this->initMenu($menuItem->menu);
					break;
			}
		}
		
		return $menu;
	}
	
	private function setCurrent( $menu, $controllerName, $actionName )
	{
		$issetCurrent = false;
		
		foreach( $menu AS $menuItem )
		{
			switch( $menuItem->type )
			{
				case "item":
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
					break;
				
				case "group":
					$itemCurrent = $this->setCurrent($menuItem->menu, $controllerName, $actionName);
					
					if( $itemCurrent )
					{
						$issetCurrent = true;
						$menuItem->current = true;
					}
					else
					{
						$menuItem->current = false;
					}
					break;
			}
		}
		
		return $issetCurrent;
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