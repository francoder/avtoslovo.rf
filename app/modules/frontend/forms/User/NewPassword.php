<?php
namespace Molly\Frontend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Password,
	Phalcon\Validation\Validator\StringLength,
	Phalcon\Validation\Validator\Confirmation;

class NewPassword extends Form
{
	public function initialize()
	{
		$password = new Password("password");
		$password->addValidator(new StringLength(array(
			"min" => 6,
			"messageMinimum" => "Пароль слишком короткий",
		)));
		$passwordConfirm = new Password("passwordConfirm");
		$passwordConfirm->addValidator(new Confirmation(array(
			"message" => "Пароли не совпадают",
			"with" => "password",
		)));
		
		$this->add($password);
		$this->add($passwordConfirm);
	}
}