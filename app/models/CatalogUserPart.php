<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogUserPart extends Model
{
	public $id;
	public $executedAt;
	public $userId;
	public $article;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_users_parts");
	}
	
	public function getSource()
	{
		return "molly_catalog_users_parts";
	}
	
	public function beforeValidation()
	{
		$this->executedAt = (new \DateTime())->format("Y-m-d H:i:s");
	}
}