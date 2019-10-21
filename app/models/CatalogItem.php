<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogItem extends Model
{
	public $id;
	public $supplier;
	public $title;
	public $externalId;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_items");
	}
	
	public function getSource()
	{
		return "molly_catalog_items";
	}
}