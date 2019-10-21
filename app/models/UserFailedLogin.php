<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserFailedLogin extends Model
{
	public $id;
	public $userId;
	public $ipAddress;
	public $attempted;
	
	public function initialize()
	{
		$this->setSource("molly_users_failed_logins");
	}
	
	public function getSource()
	{
		return "molly_users_failed_logins";
	}
}