<?php
namespace Molly\Frontend\Controllers;

use Molly\Frontend\CatalogModels;
use Molly\Models\User,
	Molly\Models\Order,
	Molly\Models\UserAuto,
	Molly\Models\UserBalance,
	Molly\Models\UserPasswordReset,
	Molly\Frontend\Forms\User\Forgot AS ForgotForm,
	Molly\Frontend\Forms\User\Register AS RegisterForm,
	Molly\Frontend\Forms\User\Settings AS SettingsForm,
	Molly\Frontend\Forms\User\NewPassword AS NewPasswordForm,
	Molly\Frontend\Forms\User\ChangePassword AS ChangePasswordForm;

class UserController extends ControllerBase
{
    use CatalogModels;
	public function registerAction()
	{
		if( $this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Регистрация");
		
		$form = new RegisterForm();
		$user = new User();
		
		$postData = $this->request->getPost();
		
		$postData["phone"] = preg_replace("/[^0-9]/", "", $postData["phone"]);
		
		$form->bind($postData, $user);
		
		if( $this->request->isPost() AND $form->isValid() )
		{
			$user->password = $this->security->hash($user->password);
			
			if( $user->save() )
			{
				if( $this->application->users->emailRequired )
				{
					return $this->response->redirect("login");
				}
				else
				{
					$this->auth->authorize($user, true);
					
					return $this->response->redirect("");
				}
			}
			else
			{
				foreach( $user->getMessages() AS $message )
				{
					$field = $form->getMessagesFor($message->getField());
					
					if( $field )
						$field->appendMessage(new \Phalcon\Validation\Message($message->getMessage(), $message->getField()));
					else
					{
						$field = $form->getMessagesFor("email");
						$field->appendMessage(new \Phalcon\Validation\Message($message->getField().": ".$message->getMessage(), $message->getField()));
					}
				}
			}
		}
		
		$this->view->form = $form;
	}
	
	public function forgotAction()
	{
        $this->getModelList();
		if( $this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Восстановление пароля");
		
		$form = new ForgotForm();
		$this->view->form = $form;
		
		if( $this->request->isPost() AND $form->isValid($this->request->getPost()) )
		{
			$user = User::findFirst(array(
				"conditions" => "email = :email:",
				"bind" => array(
					"email" => $this->request->getPost("email"),
				),
			));
			
			if( !$user )
			{
				$form->getMessagesFor("email")->appendMessage(new \Phalcon\Validation\Message("Пользователь с таким Email'ом не найден", "email"));
				return;
			}
			
			$passwordReset = new UserPasswordReset();
			$passwordReset->userId = $user->id;
			
			if( !$passwordReset->save() )
			{
				$form->getMessagesFor("email")->appendMessage(new \Phalcon\Validation\Message("Временная ошибка, попробуйте позже", "email"));
				return;
			}
			
			$this->dispatcher->forward(array(
				"controller" => "user",
				"action" => "forgotSuccess",
			));
		}
	}
	
	public function forgotSuccessAction()
	{
		if( $this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Восстановление пароля");
	}
	
	public function resetpasswordAction( $code )
	{
		if( $this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$passwordReset = UserPasswordReset::findFirst(array(
			"conditions" => "code = :code: AND reset = 'n'",
			"bind" => array(
				"code" => $code,
			),
		));
		
		if( !$passwordReset )
			$this->dispatcher->forward(array(
				"controller" => "error",
				"action" => "notFound",
			));
		
		$this->setTitle("Восстановление пароля");
		
		$form = new NewPasswordForm();
		$this->view->form = $form;
		
		if( $this->request->isPost() AND $form->isValid($this->request->getPost()) )
		{
			$passwordReset->user->password = $this->security->hash($this->request->getPost("password"));
			$passwordReset->save();
			
			$this->auth->authorize($passwordReset->user, true);
			
			return $this->response->redirect("");
		}
	}
	
	public function profileAction()
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Личный кабинет");
		
	}
	
	public function autoAction()
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Мои автомобили");
		
		$autos = UserAuto::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->auth->getCurrentId(),
			),
		));
		
		$table = array();
		
		foreach( $autos AS $auto )
		{
			$commandVehicle = $this->cataloghelper->buildCommand("GetVehicleInfo", array(
				"Locale" => "ru_RU",
				"Catalog" => $auto->catalogId,
				"VehicleId" => $auto->vehicleId,
				"ssd" => $auto->ssd,
				"Localized" => "true",
			));
			$response = $this->cataloghelper->query($commandVehicle);
			
			$item = $auto->toArray();
			$item["data"] = $response->GetVehicleInfo->row;
			
			$table[] = $item;
		}
		
		$this->view->autos = $autos;
		$this->view->table = $table;
	}
	
	public function ordersAction()
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Мои заказы");
		
		$orders = Order::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->auth->getCurrentId(),
			),
			"order" => "createdAt DESC",
		));

		$this->view->orders = $orders;
	}
	
	public function orderAction( $id )
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		$order = Order::findFirst(array(
			"conditions" => "id = :id: AND userId = :userId:",
			"bind" => array(
				"id" => $id,
				"userId" => $this->auth->getCurrentId(),
			),
		));
		
		if( !$order && $this->auth->getCurrentGroupId() != 1)
			return $this->dispatcher->forward(array(
				"controller" => "error",
				"action" => "notFound",
			));
        if($this->auth->getCurrentGroupId() == 1) {
            $order = Order::findFirst(array(
                "conditions" => "id = :id:",
                "bind" => array(
                    "id" => $id,
                ),
            ));
        }
		$this->setTitle("Заказ #" . $order->id);
		
		$this->view->order = $order;
	}
	
	public function settingsAction()
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("Настройка аккаунта");
		
		$user = $this->auth->getCurrent();
		
		$settingsForm = new SettingsForm($user);
		$this->view->settingsForm = $settingsForm;
		
		if( $this->request->isPost() AND $this->request->getPost("form") == "user" )
		{
			$postData = $this->request->getPost();
			
			$postData["phone"] = preg_replace("/[^0-9]/", "", $postData["phone"]);
			
			$settingsForm->bind($postData, $user);
			
			if( $settingsForm->isValid() )
			{
				if( $user->save() )
				{
					return $this->response->redirect("profile/settings");
				}
				else
				{
					foreach( $user->getMessages() AS $message )
					{
						$field = $form->getMessagesFor($message->getField());
						
						if( $field )
							$field->appendMessage(new \Phalcon\Validation\Message($message->getMessage(), $message->getField()));
						else
						{
							$field = $form->getMessagesFor("email");
							$field->appendMessage(new \Phalcon\Validation\Message($message->getField().": ".$message->getMessage(), $message->getField()));
						}
					}
				}
			}
		}
		
		$changePasswordForm = new ChangePasswordForm();
		$this->view->changePasswordForm = $changePasswordForm;
		
		if( $this->request->isPost() AND $this->request->getPost("form") == "password" )
		{
			if( $changePasswordForm->isValid($this->request->getPost()) )
			{
				if( !$this->security->checkHash($this->request->getPost("oldpassword"), $user->password) )
				{
					$changePasswordForm->getMessagesFor("oldpassword")
						->appendMessage(new \Phalcon\Validation\Message("Неверный пароль",
							"oldpassword"));
				}
				else
				{
					$user->password = $this->security->hash($this->request->getPost("password"));
					$user->save();
					
					return $this->response->redirect("profile/settings");
				}
			}
		}
	}
	
	public function balanceAction()
	{
        $this->getModelList();
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("");
		
		$this->setTitle("История баланса");
		
		$this->view->balanceOperations = UserBalance::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->auth->getCurrentId(),
			),
			"order" => "id DESC",
		));
	}
}