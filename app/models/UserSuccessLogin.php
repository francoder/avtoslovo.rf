<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserSuccessLogin extends Model
{
	public $id;
	public $createdAt;
	public $userId;
	public $ipAddress;
	public $userAgent;
	
	public function initialize()
	{
		$this->setSource("molly_users_success_logins");
	}
	
	public function getSource()
	{
		return "molly_users_success_logins";
	}
}