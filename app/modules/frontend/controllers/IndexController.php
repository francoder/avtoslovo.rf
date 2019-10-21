<?php
namespace Molly\Frontend\Controllers;

use Molly\Models\ContentBanner;
use Molly\Models\Order,
    Molly\Models\User,
    Molly\Models\ContentSlider,
    Molly\Models\UserBalance,
	Molly\Frontend\Forms\Order AS OrderForm;
use Molly\Frontend\CatalogModels;
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;
use Voronkovich\SberbankAcquiring\OrderStatus;

class IndexController extends ControllerBase
{
    protected $username = 'avtoslovo-api';
    protected $password = 'avtoslovo';

    use CatalogModels;
	public function indexAction()
	{
		$this->setTitle("Главная страница");

        $this->getModelList();

        $slides = ContentSlider::find();
        $this->view->slides = $slides;
	}
	
	public function cartAction()
	{
		$this->setTitle("Корзина");

        $this->getModelList();
		
		$cart = $this->cart->getFullCart();
		$users = User::find(array(
            "conditions" => "active = :active:",
            "bind" => array(
                "active" => "Y",
            ),
        ));

        $currentUser = User::findFirst(array(
            "conditions" => "id = :id:",
            "bind" => array(
                "id" => $this->auth->getCurrentId(),
            ),
        ));
		
		$this->view->setVars(array('cart' => $cart, 'users' => $users, 'currentUser' => $currentUser));
	}
	
	public function cartOrderAction()
	{
		$this->setTitle("Заказ");

        $this->getModelList();

		if( $this->request->isPost() )
		{
			$counts = $this->request->getPost("count");
			
			if( is_array($counts) AND !empty($counts) )
			{
				foreach( $counts AS $key => $count )
				{
					$this->cart->setCount($key, $count);
				}
			}
		}
		
		if( !$this->auth->isAuthorized() )
			return $this->response->redirect("login");
		
		// $form = new OrderForma();
		// $this->view->form = $form;
		
		$cart = $this->cart->getFullCart();
		$items = array();
		$amount = 0;
		$delivery = 0;
		
		$toOrder = $this->request->get("order");
		
		$orderedItems = array();
		
		foreach( $cart AS $cartItem )
		{
			if( $cartItem["aviable"] < $cartItem["count"] )
				continue;
			
			if( !isset($toOrder[$cartItem["code"]]) )
				continue;
			
			$orderedItems[] = $cartItem["code"];
			
			$items[] = array(
				"code" => $cartItem["code"],
				"article" => $cartItem["article"],
				"brand" => $cartItem["brand"],
				"title" => $cartItem["name"],
				"stock" => $cartItem["stock"],
				"count" => $cartItem["count"],
				"price" => $cartItem["price"],
				"delivery" => $cartItem["delivery"],
				"status" => "1",
				"originalPrice" => $cartItem["originalPrice"],
			);
			
			$delivery = max($cartItem["delivery"], $delivery);
			
			$amount += $cartItem["count"] * $cartItem["price"];
		}
		
		if( empty($items) )
		{
			$this->view->errorMsg = "Выберите хотябы один товар который есть в наличии";
		}
		else
		{
			$deliveryAt = new \DateTime();
			if( $delivery > 0 )
			{
				$deliveryAt->modify("+ " . $delivery . " days");
			}

			$userId = $this->request->getPost('users');

			$order = new Order();
			$order->assign(array(
				"deliveryAt" => $deliveryAt->format("Y-m-d H:i:s"),
				"userId" => isset($userId) && $userId != 0 ? $userId : $this->auth->getCurrentId(),
				"status" => 1,
				"items" => json_encode($items),
				"amount" => $amount,
			));
			
			if( $order->save() )
			{
				foreach( $orderedItems AS $item )
					$this->cart->remove($item);
				
				return $this->response->redirect(array(
					"for" => "order",
					"id" => $order->id,
				));
			}
			else
			{
				$messages = array();
				
				foreach( $order->getMessages() AS $message )
					$messages[] = $message->getMessage();
				
				$this->view->errorMsg = "Невозможно создать заказ: " . implode(", ", $messages);
			}
		}
	}

    public function createOrderAction()
    {
        $order = Order::findFirst(array(
            "conditions" => "id = :id: AND userId = :userId:",
            "bind" => array(
                "id" => $this->request->getPost('orderId'),
                "userId" => $this->auth->getCurrentId(),
            ),
        ));

        if( !$order )
        {
            return $this->response->setStatusCode(404, "Заказ не найден");
        }

        $client = new Client([
            'userName' => $this->username,
            'password' => $this->password,
            'language' => 'ru',
            'apiUri' => Client::API_URI_TEST,
            'httpMethod' => 'GET',
        ]);

        if($this->request->isPost()) {

            $orderId     = $order->id.'-'.time();
            $orderAmount = $order->amount * 100;
            $returnUrl  = 'http://avtoslovo.loc/payment-success';


            $params['currency'] = Currency::RUB;
            $params['failUrl']  = 'http://avtoslovo.loc/payment-failure';

            $result = $client->registerOrder($orderId, $orderAmount, $returnUrl, $params);

            $paymentFormUrl = $result['formUrl'];

            header('Location: ' . $paymentFormUrl);
        }
	}

    public function paymentSuccessAction()
    {
        if( !$this->auth->isAuthorized() )
            return $this->response->redirect("login");

        if (!$this->request->get('orderId')) {
            $this->setTitle('Заказ не найден');
            $this->view->msg = 'Заказ не найден';
        }

        $client = new Client([
            'userName' => $this->username,
            'password' => $this->password,
            'language' => 'ru',
            'apiUri' => Client::API_URI_TEST,
            'httpMethod' => 'GET',
        ]);
        $orderId = $this->request->get('orderId');
        $result = $client->getOrderStatusExtended($orderId);
        $orderNumber = $result['orderNumber'];

        $order = Order::findFirst(array(
            "conditions" => "id = :id: AND userId = :userId:",
            "bind" => array(
                "id" => stristr($orderNumber, '-', true),
                "userId" => $this->auth->getCurrentId(),
            ),
        ));

        if(!OrderStatus::isDeclined($result['orderStatus'])) {
            $items = json_decode($order->items);
            foreach ($items AS $item => $value) {
                $value->status = 2;
            }
            $order->items = json_encode($items);
            $order->status = 1;
            $order->save();
        }

        $this->setTitle("Оплата произведена");
        $this->view->msg = 'Оплата прошла успешно';
        $this->view->pick('pages/paymentSuccess');
	}

    public function paymentFailureAction()
    {
        if( !$this->auth->isAuthorized() )
            return $this->response->redirect("login");

        $this->setTitle("Ошибка при оплате");
        $this->view->pick('pages/paymentFailure');
	}
}