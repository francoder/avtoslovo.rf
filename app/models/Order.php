<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class Order extends Model
{
	public $id;
	public $createdAt;
	public $deliveryAt;
	public $userId;
	public $status;
	public $items;
	public $amount;
	
	public function initialize()
	{
		$this->belongsTo(
			"userId",
			"Molly\Models\User",
			"id",
			array(
				"alias" => "user",
			)
		);
		
		$this->setSource("molly_orders");
	}
	
	public function getSource()
	{
		return "molly_orders";
	}
	
	public function beforeValidationOnCreate()
	{
		if( !$this->createdAt )
			$this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
	}
	
	public function beforeValidationOnUpdate()
	{
		$old = self::findFirst($this->id);
		
		if( $old->status != $this->status )
		{
			// Do ststus changed
			
			if( $this->status == "10" )
			{
				$balanceItem = new UserBalance();
				$balanceItem->assign(array(
					"userId" => $this->userId,
					"type" => "order",
					"itemId" => $this->id,
					"amount" => $this->amount,
					"comment" => "Начисление за отмену заказа #" . $this->id,
				));
				$balanceItem->save();
				
			}
		}
	}
	
	public function afterCreate()
	{
		$balanceItem = new UserBalance();
		$balanceItem->assign(array(
			"userId" => $this->userId,
			"type" => "order",
			"itemId" => $this->id,
			"amount" => -$this->amount,
			"comment" => "Списание за заказ #" . $this->id,
		));
		$balanceItem->save();
		
		$users = User::find(array(
			"conditions" => "groupId = '1' AND active = 'Y'",
		));
		
		foreach( $users AS $user )
		{
			$this->getDI()->getShared("mail")->createMessageFromView(
				"admin/order",
				array(
					"domain" => $this->getDI()->getShared("application")->domain,
					"model" => $this,
					"user" => $user,
				)
			)->to($user->email)->subject("Новый заказ #".$this->id)->send();
		}
	}
	
	public function getCreatedDateTime()
	{
		$datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->createdAt);
		
		return $datetime;
	}
	
	public function getDeliveryDateTime()
	{
		$datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->deliveryAt);
		
		return $datetime;
	}
	
	public function getStatuses()
	{
		$statuses = array(
			"1" => "Ожидает обработки",
			"2" => "В работе",
			"7" => "Ожидает прибытия",
			"8" => "Готов к выдаче",
			"9" => "Выполнен",
			"10" => "Отменен",
		);
		
		return $statuses;
	}
	
	public function getItemStatuses()
	{
		$statuses = array(
			"1" => "Ожидает оплаты",
			"2" => "Оплачено",
		);
		
		return $statuses;
	}
	
	public function isClosed()
	{
		if( in_array($this->status, array(9, 10))  )
			return true;
		
		return false;
	}
	
	public function close()
	{
		$this->status = 10;
		
		return $this;
	}
	
	public function getStatus()
	{
		return $this->getStatuses()[$this->status];
	}
}