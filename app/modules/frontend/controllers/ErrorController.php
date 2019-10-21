<?php
namespace Molly\Frontend\Controllers;

use Molly\Frontend\CatalogModels;

class ErrorController extends ControllerBase
{
    use CatalogModels;
	public function initialize()
	{
	    $this->getModelList();
		$this->view->setMainView("error");
	}
	
	public function notFoundAction()
	{
	    $this->getModelList();
		$this->setTitle("404 Не найдено");
		$this->response->setStatusCode(404, "Not Found");
	}
}