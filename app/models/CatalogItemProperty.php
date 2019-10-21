<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogItemProperty extends Model
{
	public $id;
	public $itemId;
	public $key;
	public $value;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_items_properties");
	}
	
	public function getSource()
	{
		return "molly_catalog_items_properties";
	}
}