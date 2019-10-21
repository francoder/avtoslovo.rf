<?php
namespace Molly\Frontend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Validation\Validator\Email;

class Forgot extends Form
{
	public function initialize()
	{
		$email = new Text("email");
		$email->addValidator(new Email(array(
			"message" => "Некорректный Email",
		)));
		
		$this->add($email);
	}
}