<?php
namespace Molly\Backend\Controllers;

use Molly\Models\UserBalance,
	Molly\Backend\Forms\User\Edit AS UserEditForm,
	Molly\Backend\Forms\User\NewPassword AS NewPasswordForm,
	Molly\Backend\Forms\User\Balance AS BalanceForm,
	Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersController extends ControllerBase
{
	public function listAction()
	{
		$this->setTitle("Пользователи");
		
		if( $this->request->isPost() )
		{
			switch( $this->request->getPost("bulkAction") )
			{
				case "0":
					# Nothing
					break;
				case "1":
					# Deleting
					$bulkList = $this->request->getPost("bulk");
					
					if( empty($bulkList) )
						break;
					
					$users = $this->modelsManager->createBuilder()
						->from("Molly\Models\User")
						->inWhere("id", $bulkList)
						->getQuery()->execute();
					
					if( empty($users) )
						break;
					
					foreach( $users AS $user )
						$user->delete();
					break;
			}
			
			return $this->response->redirect($_SERVER["HTTP_REFERER"]);
		}
		
		$users = $this->modelsManager->createBuilder()
			->from("Molly\Models\User")
			->orderBy("id ASC");
		
		$paginator = new PaginatorQueryBuilder(array(
			"builder" => $users,
			"limit" => 25,
			"page" => $this->request->getQuery("page", "int"),
		));
		
		$this->view->usersPage = $paginator->getPaginate();
	}
	
	public function showAction( $id )
	{
		$user = $this->modelsManager->createBuilder()
			->from("Molly\Models\User")
			->where("id = :id:", array("id" => $id))
			->getQuery()->execute()->getFirst();
		
		if( !$user )
			$this->dispatcher->forward(array(
				"controller" => "error",
				"action" => "notFound",
			));
		
		$this->setTitle("Пользователь #".$user->id);
		$this->view->editUser = $user;
		$this->view->balanceOperations = UserBalance::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $user->id,
			),
			"order" => "id DESC",
		));
		$form = new UserEditForm($user);
		$this->view->form = $form;
		
		if( $this->request->isPost() AND $this->request->getPost("action") == "data" )
		{
			$postData = $this->request->getPost();
			
			$postData["phone"] = preg_replace("/[^0-9]/", "", $postData["phone"]);
			
			$form->bind($postData, $user);
			
			if( $form->isValid() )
			{
				if( $user->save() )
					return $this->response->redirect(array("for" => "user", "id" => $user->id));
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
		
		$form2 = new NewPasswordForm();
		$this->view->form2 = $form2;
		
		if( $this->request->isPost() AND $this->request->getPost("action") == "password" )
		{
			if( $form2->isValid($this->request->getPost()) )
			{
				$user->password = $this->security->hash($this->request->getPost("password"));
				$user->save();
				
				return $this->response->redirect(array("for" => "user", "id" => $user->id));
			}
		}
		
		$form3 = new BalanceForm();
		$this->view->form3 = $form3;
		
		if( $this->request->isPost() AND $this->request->getPost("action") == "balance" )
		{
			if( $form3->isValid($this->request->getPost()) )
			{
				$type = $this->request->get("type");
				$amount = $this->request->get("amount");
				
				if( !in_array($type, array("in", "out")) )
				{
					$field = $form3->getMessagesFor("type");
					$field->appendMessage(new \Phalcon\Validation\Message("Некорректный тип", "type"));
				}
				elseif( $amount <= 0 )
				{
					$field = $form3->getMessagesFor("amount");
					$field->appendMessage(new \Phalcon\Validation\Message("Введите число", "amount"));
				}
				else
				{
					switch( $type )
					{
						case "out":
							$amount = 0 - $amount;
							break;
					}
					
					$balanceItem = new UserBalance();
					$balanceItem->assign(array(
						"userId" => $user->id,
						"amount" => $amount,
						"comment" => $this->request->get("comment"),
					));
					
					if( !$balanceItem->save() )
					{
						foreach( $balanceItem->getMessages() AS $message )
						{
							$field = $form3->getMessagesFor($message->getField());
							
							if( $field )
								$field->appendMessage(new \Phalcon\Validation\Message($message->getMessage(), $message->getField()));
							else
							{
								$field = $form3->getMessagesFor("comment");
								$field->appendMessage(new \Phalcon\Validation\Message($message->getField().": ".$message->getMessage(), $message->getField()));
							}
						}
					}
					else
					{
						return $this->response->redirect(array("for" => "user", "id" => $user->id));
					}
				}
			}
		}
	}
}