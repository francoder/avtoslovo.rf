<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogSupplier extends Model
{
	public $id;
	public $code;
	public $active;
	public $markup;
	public $storages;
	public $brands;
	public $contract;
	
	public function initialize()
	{
		$this->setSource("molly_catalog_suppliers");
	}
	
	public function getSource()
	{
		return "molly_catalog_suppliers";
	}
	
	public function getMarkups()
	{
		if( $this->markup == "" )
			$this->markup = "[]";
		
		return json_decode($this->markup);
	}
	
	public function getStorages()
	{
		if( $this->storages == "" )
			$this->storages = "[]";
		
		return json_decode($this->storages);
	}
	
	public function getStorageUp( $id )
	{
		$storages = $this->getStorages();
		
		if( !$storages )
			return 0;
		
		foreach( $storages AS $storage )
		{
			if( $storage->id == $id )
				return $storage->up;
		}
		
		return 0;
	}
	
	public function getStorageRate( $id )
	{
		$storages = $this->getStorages();
		
		if( !$storages )
			return 1;
		
		foreach( $storages AS $storage )
		{
			if( $storage->id == $id )
			{
				if( isset($storage->rate) )
					return $storage->rate;
				
				break;
			}
		}
		
		return 1;
	}
	
	public function getStorageStat( $id )
	{
		$storages = $this->getStorages();
		
		$stat = array(
			"all" => 0,
			"before" => 0,
			"intime" => 0,
			"fail" => 0,
		);
		
		if( !$storages )
			return $stat;
		
		foreach( $storages AS $storage )
		{
			if( $storage->id == $id )
			{
				if( isset($storage->stat) )
				{
					$stat = array(
						"all" => $storage->stat->all,
						"before" => $storage->stat->before,
						"intime" => $storage->stat->intime,
						"fail" => $storage->stat->fail,
					);
					
					return $stat;
				}
				
				break;
			}
		}
		
		return $stat;
	}
	
	public function getStorageActive( $id )
	{
		$storages = $this->getStorages();
		
		if( !is_array($storages) )
			return false;
		
		foreach( $storages AS $storage )
		{
			if( $storage->id == $id )
			{
				if( isset($storage->active) )
					return $storage->active == true;
				
				break;
			}
		}
		
		return false;
	}
	
	public function getBrands( $hasArray = false )
	{
		if( $this->brands == "" )
			$this->brands = "[]";
		
		if( $hasArray )
			return json_decode($this->brands, true);
		else
			return json_decode($this->brands);
	}
	
	public function getBrandsByBrand()
	{
		$result = array();
		$brands = $this->getBrands();
		
		foreach( $brands AS $brand )
		{
			$result[strtoupper($brand->code)] = array(
				"code" => $brand->code,
				"markups" => $brand->markups,
			);
		}
		
		return $result;
	}
}