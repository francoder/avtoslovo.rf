<?php
namespace Molly\Backend\Controllers;

use Molly\Backend\Forms\Session\Login AS LoginForm;

class SessionController extends ControllerBase
{
	public function initialize()
	{
		$this->view->setMainView("notAuthorized");
	}
	
	public function loginAction()
	{
		$this->setTitle("Авторизация");
		
		$form = new LoginForm();
		
		if( $this->request->isPost() AND $form->isValid($this->request->getPost()) )
		{
			$authResult = $this->auth->login(array(
				"email" => $this->request->getPost("email"),
				"password" => $this->request->getPost("password"),
			));
			
			if( $authResult )
				return $this->response->redirect();
		}
		
		$this->view->form = $form;
	}
	
	public function logoutAction()
	{
		$this->auth->logout();
		return $this->response->redirect("");
	}
}