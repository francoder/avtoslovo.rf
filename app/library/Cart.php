<?php
namespace Molly;

use Molly\Models\UserCart;
use Phalcon\Mvc\User\Component;

class Cart extends Component
{
	private $cart;
	
	public function getCart()
	{
//		if( is_array($this->cart) ) return $this->cart;
		
		$this->cart = array();
        $userCart = UserCart::find(array(
            "conditions" => "active = :active: AND userId = :userId:",
            "bind" => array(
                "active" => "Y",
                "userId" => $this->auth->getCurrentId(),
            ),
        ));
		
		if( count($userCart) > 0)
		{
			$this->cart = $userCart;
		}
		
		return $this->cart;
	}
	
	public function getFullCart()
	{
		$cart = $this->getCart();
		$apiQuerys = array();
		
		foreach( $cart AS $cartItem )
		{
			list($api, $code, $stock) = explode("::", $cartItem->code);
			
			if( !isset($apiQuerys[$api]) ) $apiQuerys[$api] = array();
			if( !isset($apiQuerys[$api][$code]) ) $apiQuerys[$api][$code] = array();
			
			$apiQuerys[$api][$code][$stock] = $cartItem->count;
		}
		
		$data = $this->catalog->cartAll($apiQuerys);
		
		$cartFull = array();
		
		foreach( $cart AS $cartItem )
		{
			list($api, $code, $stock) = explode("::", $cartItem->code);
			
			if( !isset($data[$api]) )
			{
				$this->remove($cartItem->code);
				continue;
			}
			
			if( !isset($data[$api][$code]) )
			{
				$this->remove($cartItem->code);
				continue;
			}
			
			if( !isset($data[$api][$code][$stock]) )
			{
				$this->remove($cartItem->code);
				continue;
			}
			
			$data[$api][$code][$stock]["aviable"] = $data[$api][$code][$stock]["count"];
			$data[$api][$code][$stock]["code"] = $cartItem->code;
			$data[$api][$code][$stock]["count"] = $cartItem->count;
			
			$currentData = $data[$api][$code][$stock];
			
			$cartFull[] = $currentData;
		}
		
		return $cartFull;
	}
	
	public function getCount()
	{
		$cart = $this->getCart();
		
		return count($cart);
	}
	
	public function isInCart( $code )
	{
		$cart = $this->getCart();
		
		foreach( $cart AS $cartItem )
		{
			if( $cartItem->code == $code )
				return true;
		}
		
		return false;
	}
	
	private function setCart( $cart )
	{
		$this->cart = $cart;
        $userCart = new UserCart();
		foreach ($cart AS $cartItem) {
            $userCart->userId = $this->auth->getCurrentId();
            $userCart->code =  $cartItem['code'];
            $userCart->count = $cartItem['count'];
            $userCart->items = json_encode($cart);
            $userCart->save();
        }

		$this->getDI()->getShared("session")->set("cart", $cart);
		
		return true;
	}
	
	public function setCount( $code, $count = 1 )
	{
        $cart = $this->getCart();
        $userCart = UserCart::findFirst(array(
            "conditions" => "code = :code: AND userId = :userId: AND active = :active:",
            "bind" => array(
                "active" => 'Y',
                "code" => $code,
                "userId" => $this->auth->getCurrentId(),
            ),
        ));

		if( !empty($cart) )
		{
			foreach( $cart AS $cartItem )
			{
				if( $cartItem->code == $code )
				{
                    $items = json_decode($cartItem->items);
					$cartItem->count = $count;
					$userCart->count = $count;
                    foreach ($items AS $item => $value) {
                        $value->count = $count;
                    }
                    $userCart->items = json_encode($items);
					$userCart->save();

//					$this->setCart($cart);

					return true;
				}
			}
		}

		return false;
	}
	
	public function add( $code, $count = 1 )
	{
		$cart = $this->getCart();

		if( !empty($cart) )
			foreach( $cart AS $cartItem )
				if( $cartItem->code == $code )
					return false;

		$cartArray[] = array(
			"code" => $code,
			"count" => $count,
		);

		$cart = (object) $cartArray;
		
		$this->setCart($cart);
		
		return true;
	}
	
	public function remove( $code )
	{
		$cart = $this->getCart();
		$userCart = UserCart::findFirst(array(
            "conditions" => "code = :code: AND userId = :userId: AND active = :active:",
            "bind" => array(
                "code" => $code,
                "userId" => $this->auth->getCurrentId(),
                "active" => 'Y'
            ),
        ));
		
		if( !empty($cart) && $userCart !== false)
		{
			foreach( $cart AS $key => $cartItem )
			{
				if( $cartItem->code == $code )
				{
                    $userCart->active = "N";
                    $userCart->items = json_encode($cart);
                    $userCart->save();
//					$this->setCart($cart);
					return true;
				}
			}
		}
		
		return false;
	}
	
	public function clear()
	{
		$this->setCart(array());
		
		return true;
	}
}