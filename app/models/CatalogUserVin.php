<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogUserVin extends Model
{
	public $id;
	public $executedAt;
	public $userId;
	public $vin;
        public $model;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_users_vin");
	}
	
	public function getSource()
	{
		return "molly_catalog_users_vin";
	}
	
	public function beforeValidation()
	{
		$this->executedAt = (new \DateTime())->format("Y-m-d H:i:s");
	}
}