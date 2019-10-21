<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class ApiRequest extends Model
{
	public $id;
	public $type;
	public $createdAt;
	public $executedAt;
	public $data;
	
	public function initialize()
	{
		$this->setSource("molly_api_requests");
	}
	
	public function getSource()
	{
		return "molly_api_requests";
	}
	
	public function beforeValidationOnCreate()
	{
		$this->createdAt = microtime(true);
		$this->executedAt = 0;
	}
}