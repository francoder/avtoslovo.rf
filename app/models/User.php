<?php
namespace Molly\Models;

use Phalcon\Mvc\Model,
	Phalcon\Validation,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\Uniqueness;

class User extends Model
{
	public $id;
	public $createdAt;
	public $active;
	public $email;
	public $password;
	public $phone;
	public $firstName;
	public $lastName;
	public $city;
	public $groupId;
	public $photoId;
	
	public function initialize()
	{
		$this->setSource("molly_users");
		
		$this->belongsTo(
			"groupId",
			"Molly\Models\UserGroup",
			"id",
			array(
				"alias" => "group",
			)
		);
		
		$this->skipAttributesOnCreate(array("photoId"));
	}
	
	public function getSource()
	{
		return "molly_users";
	}
	
	public function beforeValidationOnCreate()
	{
		if( !$this->active )
		{
			if( $this->getDI()->getShared("application")->users->emailRequired )
				$this->active = "N";
			else
				$this->active = "Y";
		}
		
		if( !$this->createdAt )
		{
			$this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
		}
		
		if( !$this->groupId )
		{
			$this->groupId = $this->getDI()->getShared("application")->users->defaultGroup;
		}
	}
	
	public function validation()
	{
		$validator = new Validation();
		$validator->add("email", new PresenceOf(array(
			"message" => "Email обязателен к заполнению",
		)));
		$validator->add("phone", new PresenceOf(array(
			"message" => "Телефон обязателен к заполнению",
		)));
		$validator->add("firstName", new PresenceOf(array(
			"message" => "Имя обязательно к заполнению",
		)));
		$validator->add("lastName", new PresenceOf(array(
			"message" => "Фамилия обязательна к заполнению",
		)));
		$validator->add("password", new PresenceOf(array(
			"message" => "Пароль обязателен к заполнению",
		)));
		$validator->add("email", new Uniqueness(array(
			"message" => "Пользователь с таким Email'ом уже зарегистрирован",
		)));
		$validator->add("phone", new Uniqueness(array(
			"message" => "Пользователь с таким телефоном уже зарегистрирован",
		)));
		
		return $this->validate($validator);
	}
	
	public function beforeDelete()
	{
		$rememberTokens = UserRememberToken::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->id,
			),
		));
		
		foreach( $rememberTokens AS $token )
		{
			$token->delete();
		}
		
		$failedLogins = UserFailedLogin::find(array(
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->id,
			),
		));
		
		foreach( $failedLogins AS $login )
		{
			$login->delete();
		}
	}
	
	public function getBalance()
	{
		$sum = UserBalance::sum(array(
			"column" => "amount",
			"conditions" => "userId = :userId:",
			"bind" => array(
				"userId" => $this->id,
			),
		));
		
		return $sum;
	}
	
	public function getCityName()
	{
		if( $this->getDI()->has("geocode") AND $this->city != "" )
		{
			$result = $this->getDI()->getShared("geocode")->details($this->city);
			
			if( isset($result->result->name) )
				return $result->result->name;
		}
		
		return "";
	}
	
	public function getFullName( $seperator = " " )
	{
		return implode($seperator, array($this->firstName, $this->lastName));
	}
	
	public function getPhoneFormatted()
	{
		if( strlen($this->phone) > 0 )
			return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})/", "+$1 ($2) $3-$4-$5", $this->phone);
		else
			return "";
	}
}