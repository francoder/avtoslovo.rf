<?php
namespace Molly\Frontend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Password,
	Phalcon\Validation\Validator\Email,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\StringLength,
	Phalcon\Validation\Validator\Confirmation;

class Register extends Form
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
		$password = new Password("password");
		$password->addValidator(new PresenceOf(array(
			"message" => "Некорректный пароль",
		)));
		$password->addValidator(new StringLength(array(
			"min" => 6,
			"messageMinimum" => "Пароль слишком короткий",
		)));
		$passwordConfirm = new Password("passwordConfirm");
		$passwordConfirm->addValidator(new Confirmation(array(
			"message" => "Пароли не совпадают",
			"with" => "password",
		)));
		
		$this->add($firstName);
		$this->add($lastName);
		$this->add($city);
		$this->add($phone);
		$this->add($email);
		$this->add($password);
		$this->add($passwordConfirm);
	}
}