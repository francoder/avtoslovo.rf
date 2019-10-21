<?php
namespace Molly\Backend\Controllers;

class ErrorController extends ControllerBase
{
	public function initialize()
	{
		$this->view->setMainView("error");
	}
	
	public function notFoundAction()
	{
		$this->setTitle("404 Не найдено");
		$this->response->setStatusCode(404, "Not Found");
	}
	
	public function notAllowedAction()
	{
		$this->setTitle("403 Нет доступа");
		$this->response->setStatusCode(403, "Not Forbidden");
	}
}