<?php
namespace Molly\Frontend;

use Molly\MenuStandart,
	Molly\Authorization,
	Molly\Geocode,
	Molly\Cart,
	Molly\Catalog,
	Molly\CatalogHelper,
	Phalcon\Loader,
	Phalcon\DiInterface,
	Phalcon\Mvc\Url,
	Phalcon\Mvc\View,
	Phalcon\Mvc\Dispatcher,
	Phalcon\Http\Response\Cookies,
	Phalcon\Mvc\ModuleDefinitionInterface,
	Phalcon\Session\Adapter\Files as Session;

define("MODULE_ROOT_DIR", dirname(__FILE__));

trait CatalogModels {
    function getModelList() {
        $command = $this->cataloghelper->buildCommand("ListCatalogs", array("Locale" => "ru_RU", "ssd" => ""));
        $catalogs = $this->cataloghelper->query($command);
        $this->view->marks = $catalogs->ListCatalogs;
        $this->view->catalogs = $catalogs->ListCatalogs;
    }
}

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders( DiInterface $di = NULL )
	{
		$loader = new Loader();
		$loader->registerNamespaces(array(
			# Module
			"Molly\Frontend\Controllers" => $di->get("module")->controllersDir,
			"Molly\Frontend\Forms" => $di->get("module")->formsDir,
			"Molly\Frontend" => $di->get("module")->libraryDir,
			# System
			"Molly\Models" => $di->get("application")->system->modelsDir,
			"Molly" => $di->get("application")->system->libraryDir,
		));
		$loader->register();
	}
	
	public function registerServices( DiInterface $di = NULL )
	{
		$di->set("dispatcher", function(){
			$dispatcher = new Dispatcher();
			$dispatcher->setDefaultNamespace("Molly\Frontend\Controllers\\");
			
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
		$di->set("cart", function(){
			return new Cart();
		});
		$di->set("catalog", function(){
			return new Catalog();
		});
		$di->set("cataloghelper", function(){
			return new CatalogHelper();
		});
		$di->set("menu", function(){
			$menu = new MenuStandart();
			$menuConfig = require MODULE_ROOT_DIR."/config/menu.php";
			$menu->setMenu($menuConfig);
			
			return $menu;
		});
	}
}