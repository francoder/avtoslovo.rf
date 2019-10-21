<?php
namespace Molly\Backend\Forms\User;

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Select,
	Phalcon\Forms\Element\TextArea;

class Balance extends Form
{
	public function initialize()
	{
		$type = new Select("type", array(
			"in" => "Начисление",
			"out" => "Списание",
		));
		$amount = new Text("amount");
		$comment = new TextArea("comment");
		
		$this->add($type);
		$this->add($amount);
		$this->add($comment);
	}
}