<?php
namespace Molly\Frontend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Password,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\StringLength,
	Phalcon\Validation\Validator\Confirmation;

class ChangePassword extends Form
{
	public function initialize()
	{
		$oldpassword = new Password("oldpassword");
		$oldpassword->addValidator(new PresenceOf(array(
			"messageMinimum" => "Введите старый пароль",
		)));
		$password = new Password("password");
		$password->addValidator(new StringLength(array(
			"min" => 6,
			"messageMinimum" => "Новый пароль слишком короткий",
		)));
		$passwordConfirm = new Password("passwordConfirm");
		$passwordConfirm->addValidator(new Confirmation(array(
			"message" => "Пароли не совпадают",
			"with" => "password",
		)));
		
		$this->add($oldpassword);
		$this->add($password);
		$this->add($passwordConfirm);
	}
}