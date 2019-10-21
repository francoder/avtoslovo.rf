<?php
namespace Molly\Backend\Controllers;

use Molly\Models\User,
	Molly\Models\Order,
	Molly\Models\FeedBack;

class IndexController extends ControllerBase
{
	public function indexAction()
	{
		$this->setTitle("Обзор");
		
		$this->view->feedBack = FeedBack::find(array(
			"limit" => 5,
			"order" => "id DESC",
		));
		$this->view->feedBackCount = FeedBack::count(array(
			"conditions" => "note = ''",
		));
		
		$this->view->users = User::find(array(
			"limit" => 5,
			"order" => "id DESC",
		));
		
		$this->view->orders = Order::find(array(
			"limit" => 10,
			"order" => "id DESC",
		));
		$this->view->ordersCount = Order::count(array(
			"conditions" => "status = '1'",
		));
	}
}