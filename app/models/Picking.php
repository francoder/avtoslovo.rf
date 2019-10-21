<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class Picking extends Model
{
	public $id;
	public $createdAt;
	public $viewed;
	public $name;
	public $phone;
	public $email;
	public $comment;
	public $note;
	
	public function initialize()
	{
		$this->setSource("molly_feedbacks");
	}
	
	public function getSource()
	{
		return "molly_feedbacks";
	}
	
	public function beforeValidationOnCreate()
	{
		$this->viewed = "N";
                
		if( !$this->createdAt ) $this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
		if( !$this->note ) $this->note = new \Phalcon\Db\RawValue('""');
		if( !$this->comment ) $this->comment = new \Phalcon\Db\RawValue('""');
	}
	
	public function beforeValidationOnUpdate()
	{
		if( !$this->comment ) $this->comment = new \Phalcon\Db\RawValue('""');
		if( !$this->note ) $this->note = new \Phalcon\Db\RawValue('""');
	}
	
	public function getCreatedDateTime()
	{
		$datetime = \DateTime::createFromFormat("Y-m-d H:i:s", $this->createdAt);
		
		return $datetime;
	}
	
	public function getPhoneFormatted()
	{
		if( strlen($this->phone) > 0 )
			return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})/", "+$1 ($2) $3-$4-$5", $this->phone);
		else
			return "";
	}
	
	public function afterCreate()
	{
		$users = User::find(array(
			"conditions" => "groupId = '1' AND active = 'Y'",
		));
		
		foreach( $users AS $user )
		{
			$this->getDI()->getShared("mail")->createMessageFromView(
				"admin/feedback",
				array(
					"domain" => $this->getDI()->getShared("application")->domain,
					"model" => $this,
					"user" => $user,
				)
			)->to($user->email)->subject("Обращение")->send();
		}
	}
}