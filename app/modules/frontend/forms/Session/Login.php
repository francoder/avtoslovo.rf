<?php
namespace Molly\Frontend\Forms\Session;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Password,
	Phalcon\Validation\Validator\PresenceOf;

class Login extends Form
{
	public function initialize()
	{
		$email = new Text("email");
		$email->addValidator(new PresenceOf(array(
			"message" => "Введите Email",
		)));
		$password = new Password("password");
		$password->addValidator(new PresenceOf(array(
			"message" => "Введите пароль",
		)));
		
		$this->add($email);
		$this->add($password);
	}
}