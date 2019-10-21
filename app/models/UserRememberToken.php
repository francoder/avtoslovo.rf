<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserRememberToken extends Model
{
	public $id;
	public $createdAt;
	public $userId;
	public $token;
	public $userAgent;
	
	public function initialize()
	{
		$this->belongsTo(
			"photoId",
			"Molly\Models\User",
			"id",
			array(
				"alias" => "user"
			)
		);
		
		$this->setSource("molly_users_remember_tokens");
	}
	
	public function getSource()
	{
		return "molly_users_remember_tokens";
	}
}