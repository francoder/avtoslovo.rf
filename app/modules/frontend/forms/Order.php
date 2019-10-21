<?php
namespace Molly\Frontend\Forms;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\TextArea,
	Phalcon\Validation\Validator\PresenceOf;

class Order extends Form
{
	public function initialize()
	{
		$name = new Text("name");
		$phone = new Text("phone");
		$comment = new TextArea("comment");
		
		$name->addValidator(new PresenceOf(array(
			"message" => "Введите ваше имя",
		)));
		
		$this->add($name);
		$this->add($phone);
		$this->add($comment);
	}
}