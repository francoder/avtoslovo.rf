<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogUserFrame extends Model
{
	public $id;
	public $executedAt;
	public $userId;
	public $frame;
	public $model;

	public function initialize()
	{
		$this->setSource("molly_catalog_users_frame");
	}
	
	public function getSource()
	{
		return "molly_catalog_users_frame";
	}
	
	public function beforeValidation()
	{
		$this->executedAt = (new \DateTime())->format("Y-m-d H:i:s");
	}
}