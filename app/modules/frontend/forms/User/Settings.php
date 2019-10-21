<?php
namespace Molly\Frontend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Validation\Validator\Email,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\StringLength;

class Settings extends Form
{
	public function initialize()
	{
		$firstName = new Text("firstName");
		$firstName->addValidator(new PresenceOf(array(
			"message" => "Некорректное имя",
		)));
		$lastName = new Text("lastName");
		$lastName->addValidator(new PresenceOf(array(
			"message" => "Некорректная фамилия",
		)));
		$city = new Text("city");
		$city->addValidator(new PresenceOf(array(
			"message" => "Некорректный город",
		)));
		$phone = new Text("phone");
		$phone->addValidator(new StringLength(array(
			"min" => 11,
			"max" => 11,
			"messageMinimum" => "Некорректный Телефон",
			"messageMaximum" => "Некорректный Телефон",
		)));
		$email = new Text("email");
		$email->addValidator(new Email(array(
			"message" => "Некорректный Email",
		)));
		
		$this->add($firstName);
		$this->add($lastName);
		$this->add($city);
		$this->add($phone);
		$this->add($email);
	}
}