<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogLocalSupplier extends Model
{
	public $id;
	public $active;
	public $activeTo;
	public $code;
	public $title;
	public $markups;
	public $stockName;
	public $delivery;
	public $deliveryRate;
	public $deliveryStat;
	public $contract;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_local_suppliers");
	}
	
	public function getSource()
	{
		return "molly_catalog_local_suppliers";
	}
	
	public function isActive()
	{
		if( $this->active == "N" )
			return false;
		
		if( $this->activeTo != "" )
		{
			$dateCurrent = new \DateTime();
			
			if( $this->getActiveToDatetime()->format("YmdHi") < $dateCurrent->format("YmdHi") )
				return false;
		}
		
		return true;
	}

    public function isContract()
    {
        if($this->contract == "N")
            return false;

        return true;
	}
	
	public function getActiveToDatetime()
	{
		if( $this->activeTo != "" )
			return \DateTime::createFromFormat("Y-m-d H:i:s", $this->activeTo);
		
		return false;
	}
	
	public function getDeliveryStat()
	{
		if( $this->deliveryStat == "" )
			$this->deliveryStat = "[]";
		
		$stat = json_decode($this->deliveryStat);
		
		$finalStat = array(
			"all" => (isset($stat->all)?$stat->all:0),
			"before" => (isset($stat->before)?$stat->before:0),
			"intime" => (isset($stat->intime)?$stat->intime:0),
			"fail" => (isset($stat->fail)?$stat->fail:0),
		);
		
		return $finalStat;
	}
	
	public function getMarkups()
	{
		if( $this->markups == "" )
			$this->markups = "[]";
		
		return json_decode($this->markups);
	}
	
	public function markup( $price )
	{
		$markups = $this->getMarkups();
		
		usort($markups, function($a, $b){
			if($a->value == $b->value) return 0;
			return ($a->value < $b->value) ? 1 : -1;	
		});
		
		$originalPrice = $price;
		
		foreach( $markups AS $markup ){
			if( $markup->value >= $price )
				continue;
			
			$price = $price + 
				($originalPrice*$markup->percent/100);
			
			$price = $price + $markup->fixed;
			break;
		}
		
		return $price;
	}
	
	public function beforeValidation()
	{
		if( !$this->activeTo )
			$this->activeTo = new \Phalcon\Db\RawValue("default");
		
		if( $this->deliveryRate < 1 OR $this->deliveryRate > 5 )
			$this->deliveryRate = 1;
		
		if( $this->stockName == "" )
			$this->stockName = new \Phalcon\Db\RawValue('""');
	}
	
	public function beforeValidationOnCreate()
	{
		$this->beforeValidation();
	}
	
	public function beforeValidationOnUpdate()
	{
		$this->beforeValidation();
	}
	
	public function beforeUpdate()
	{
		$selfItem = CatalogLocalSupplier::findFirst($this->id);
		
		if( $selfItem->code != $this->code )
		{
			$model = new CatalogLocalItem();
			
			# Change code
			$this->getDI()->getShared("db")->update(
				$model->getSource(),
				array("supplier"),
				array($this->code),
				array(
					"conditions" => "supplier = ?",
					"bind" => array($selfItem->code),
				)
			);
		}
	}
	
	public function beforeDelete()
	{
		$model = new CatalogLocalItem();
		
		# Change code
		$this->getDI()->getShared("db")->delete(
			$model->getSource(),
			"supplier = '{$this->code}'"
		);
	}
}