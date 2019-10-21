<?php
namespace Molly\Frontend\Controllers;

use Molly\Frontend\Forms\Session\Login AS LoginForm;

class SessionController extends ControllerBase
{
	public function loginAction()
	{
		if( $this->auth->isAuthorized() )
			return $this->response->redirect("");
		
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
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->auth->logout();
		
		return $this->response->redirect("");
	}
}