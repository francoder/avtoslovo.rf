<?php
namespace Molly\Frontend\Forms;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\TextArea,
	Phalcon\Validation\Validator\PresenceOf,
	Phalcon\Validation\Validator\StringLength;

class FeedBack extends Form
{
	public function initialize()
	{
		$name = new Text("name");
		$phone = new Text("phone");
		$comment = new TextArea("comment");
		
		$name->addValidator(new PresenceOf(array(
			"message" => "Введите ваше имя",
		)));
		$phone->addValidator(new StringLength(array(
			"min" => 11,
			"max" => 11,
			"messageMinimum" => "Некорректный Телефон",
			"messageMaximum" => "Некорректный Телефон",
		)));
		
		$this->add($name);
		$this->add($phone);
		$this->add($comment);
	}
}