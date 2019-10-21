<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserAuto extends Model
{
	public $id;
	public $userId;
	public $catalogId;
	public $vehicleId;
	public $ssd;
	
	public function initialize()
	{
		$this->setSource("molly_users_autos");
	}
	
	public function getSource()
	{
		return "molly_users_autos";
	}
}