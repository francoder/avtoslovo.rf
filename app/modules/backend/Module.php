<?php
namespace Molly\Backend;

use Molly\MenuGroupped,
	Molly\Authorization,
	Molly\Backend\Plugins\Authorization AS AuthorizationPlugin,
	Molly\Geocode,
	Molly\Catalog,
	Molly\CatalogHelper,
	Phalcon\Loader,
	Phalcon\DiInterface,
	Phalcon\Mvc\Url,
	Phalcon\Mvc\View,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Http\Response\Cookies,
	Phalcon\Mvc\ModuleDefinitionInterface,
	Phalcon\Events\Manager AS EventsManager,
	Phalcon\Session\Adapter\Files AS Session;

define("MODULE_ROOT_DIR", dirname(__FILE__));

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders( DiInterface $di = NULL )
	{
		$loader = new Loader();
		$loader->registerNamespaces(array(
			# Module
			"Molly\Backend\Controllers" => $di->get("module")->controllersDir,
			"Molly\Backend\Plugins" => $di->get("module")->pluginsDir,
			"Molly\Backend\Forms" => $di->get("module")->formsDir,
			"Molly\Backend" => $di->get("module")->libraryDir,
			# System
			"Molly\Models" => $di->get("application")->system->modelsDir,
			"Molly" => $di->get("application")->system->libraryDir,
		));
		$loader->register();
	}
	
	public function registerServices( DiInterface $di = NULL )
	{
		$di->set("dispatcher", function(){
			$eventsManager = new EventsManager();
			$authorizationPlugin = new AuthorizationPlugin();
			$eventsManager->attach("dispatch:beforeDispatch", $authorizationPlugin);
			
			$dispatcher = new Dispatcher();
			$dispatcher->setDefaultNamespace("Molly\Backend\Controllers\\");
			$dispatcher->setEventsManager($eventsManager);
			
			return $dispatcher;
		});
		$di->set("cookies", function(){
			$cookies = new Cookies();
			$cookies->useEncryption(false);
			
			return $cookies;
		});
		$di->set("session", function(){
			$session = new Session();
			$session->start();
			
			return $session;
		});
		$di->set("url", function() use ($di){
			$url = new Url();
			$url->setBaseUri($di->get("module")->baseUri);
			
			return $url;
		});
		$di->set("view", function() use ($di){
			$view = new View();
			$view->setViewsDir($di->get("module")->viewsDir);
			$view->registerEngines(
				array(
					".phtml"	=> "Phalcon\Mvc\View\Engine\Php"
				)
			);
			
			return $view;
		});
		# Molly
		$di->set("auth", function(){
			return new Authorization();
		});
		$di->set("geocode", function(){
			return new Geocode();
		});
		$di->set("catalog", function(){
			return new Catalog();
		});
		$di->set("cataloghelper", function(){
			return new CatalogHelper();
		});
		$di->set("menu", function(){
			$menu = new MenuGroupped();
			$menuConfig = require MODULE_ROOT_DIR."/config/menu.php";
			$menu->setMenu($menuConfig);
			
			return $menu;
		});
	}
}