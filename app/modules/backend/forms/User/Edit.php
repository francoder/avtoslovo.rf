<?php
namespace Molly\Backend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Select,
	Phalcon\Forms\Element\Password,
	Phalcon\Validation\Validator\Email,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\StringLength,
	Molly\Models\UserGroup;

class Edit extends Form
{
	public function initialize()
	{
		$firstName = new Text("firstName");
		$firstName->addValidator(new PresenceOf(array(
			"message" => "Некорректное имя",
		)));
		$lastName = new Text("lastName");
		$lastName->addValidator(new PresenceOf(array(
			"message" => "Некорректное фамилия",
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
		$groupId = new Select("groupId", UserGroup::find(), array("using" => array("id", "title")));
		$groupId->addValidator(new PresenceOf(array(
			"message" => "Некорректная Группа",
		)));
		
		$this->add($firstName);
		$this->add($lastName);
		$this->add($city);
		$this->add($phone);
		$this->add($phone);
		$this->add($email);
		$this->add($groupId);
	}
}