<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserBalance extends Model
{
	public $id;
	public $createdAt;
	public $userId;
	public $type;
	public $itemId;
	public $amount;
	public $text;
	
	public function initialize()
	{
		$this->setSource("molly_users_balance");
	}
	
	public function getSource()
	{
		return "molly_users_balance";
	}
	
	public function beforeValidationOnCreate()
	{
		if( !$this->createdAt )
			$this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
		
		if( !$this->itemId )
			$this->itemId = "0";
	}
	
	public function getCreatedDateTime()
	{
		return \DateTime::createFromFormat("Y-m-d H:i:s", $this->createdAt);
	}
}