<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserPasswordReset extends Model
{
	public $id;
	public $createdAt;
	public $modifiedAt;
	public $userId;
	public $code;
	public $reset;
	
	public function initialize()
	{
		$this->setSource("molly_users_pasword_resets");
		
		$this->belongsTo(
			"userId",
			"Molly\Models\User",
			"id",
			array(
				"alias" => "user",
			)
		);
		
		$this->skipAttributesOnCreate(array("modifiedAt"));
	}
	
	public function getSource()
	{
		return "molly_users_pasword_resets";
	}
	
	public function beforeValidationOnCreate()
	{
		$this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
		$this->code = preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(openssl_random_pseudo_bytes(24)));
		$this->reset = "N";
	}
	
	public function beforeValidationOnUpdate()
	{
		$this->modifiedAt = (new \DateTime())->format("Y-m-d H:i:s");
	}
	
	public function afterCreate()
	{
		$this->getDI()->getShared("mail")->createMessageFromView(
			"user/passwordReset",
			array(
				"domain" => $this->getDI()->getShared("application")->domain,
				"model" => $this,
			)
		)->to($this->user->email)->subject("Восстановление пароля")->send();
	}
}