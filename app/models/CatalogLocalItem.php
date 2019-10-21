<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message as Message;

class CatalogLocalItem extends Model
{
	public $id;
	public $supplier;
	public $externalId;
	public $hash;
	public $oem;
	public $brand;
	public $title;
	public $count;
	public $price;
	public $date;
	
	public function initialize()
	{
		$this->belongsTo(
			"supplier",
			"Molly\Models\CatalogLocalSupplier",
			"code",
			array(
				"alias" => "supplierConfig",
			)
		);
		$this->oem = "D";
		$this->setSource("molly_catalog_local_items");
	}
	
	public function getSource()
	{
		return "molly_catalog_local_items";
	}
	
	public function getStockName()
	{
		if( $this->supplierConfig AND $this->supplierConfig->stockName != "" )
			return $this->supplierConfig->stockName;
		
		return "stock";
	}
	
	public function getDeliveryRate()
	{
		if( $this->supplierConfig->deliveryRate )
			return $this->supplierConfig->deliveryRate;
		
		return 0;
	}
	
	public function getDeliveryStat()
	{
		if( $this->supplierConfig )
			return $this->supplierConfig->getDeliveryStat();
		
		return array(
			"all" => 0,
			"before" => 0,
			"intime" => 0,
			"fail" => 0,
		);
	}
        
        public function beforeValidation() {
             if (empty($this->externalId) || count_chars($this->externalId) == 0) {
                $this->externalId = new \Phalcon\Db\RawValue('"-"');;
            }
        }
}