<?php
namespace Molly\Backend\Controllers;

use Molly\Models\Order,
	Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Molly\Models\User;

class OrdersController extends ControllerBase
{
	public function listAction()
	{
		if( $this->request->isPost() && $this->request->getPost("bulkAction")  )
		{
			switch( $this->request->getPost("bulkAction") )
			{
				case "0":
					# Nothing
					break;
				case "1":
					# Deleting
					$bulkList = $this->request->getPost("bulk");
					
					if( empty($bulkList) )
						break;
					
					$orders = $this->modelsManager->createBuilder()
						->from("Molly\Models\Order")
						->inWhere("id", $bulkList)
						->getQuery()->execute();
					
					if( empty($orders) )
						break;
					
					foreach( $orders AS $order )
						$order->delete();
					break;
			}
			
			return $this->response->redirect($_SERVER["HTTP_REFERER"]);
		}

		$orderId = $this->request->getPost("orderId");

        $order = Order::findFirst(array(
            "conditions" => "id = :id:",
            "bind" => array(
                "id" => $orderId,
            ),
        ));

        if( $this->request->isPost() AND $this->request->getPost("action") == "update" )
        {
            $allowedStatuses = $order->getStatuses();
            $newStatus = $this->request->getPost("status", "int");

            if( isset($allowedStatuses[$newStatus]) )
            {
                $order->status = $newStatus;
            }

            $order->save();

            return $this->response->redirect('orders');
        }

        if( $this->request->isPost() AND $this->request->getPost("action") == "items" )
        {
            $items = json_decode($order->items);
            $itemsNew = array();
            $itemsData = $this->request->getPost("orders");

            foreach( $items AS $itemKey => $item )
            {
                $itemData = $itemsData[$item->code];

                $item->status = isset($itemData["status"]) ? $itemData["status"] : $item->status;

                $itemsNew[$itemKey] = $item;
            }

            $order->items = json_encode($itemsNew);
            $order->save();

            return $this->response->redirect('orders');
        }

		if($this->request->isPost() && $this->request->getPost("fio")) {
		    $query = $this->request->getPost("fio");
            $this->setTitle("Список заказов");

            $users = User::find(array(
                "conditions" => "lastName LIKE :lastName: OR phone LIKE :phone:",
                "bind" => array(
                    "lastName" => '%'. $query . '%',
                    "phone" => '%'. $query . '%'
                ),
            ));

            $usersIdArray = array();

            foreach ($users AS $user) {
                $usersIdArray[] = $user->id;
            }

            $userIds =  array_map("intval", $usersIdArray);

            $orders = $this->modelsManager->createBuilder()
                ->from("Molly\Models\Order")
                ->inWhere("userId", $userIds)
                ->orderBy("status ASC, createdAt DESC, id ASC");


            $paginator = new PaginatorQueryBuilder(array(
                "builder" => $orders,
                "limit" => 25,
                "page" => $this->request->getQuery("page", "int"),
            ));

            $this->view->ordersPage = $paginator->getPaginate();

        } else {

            $this->setTitle("Список заказов");

            $orders = $this->modelsManager->createBuilder()
                ->from("Molly\Models\Order")
                ->orderBy("status ASC, createdAt DESC, id ASC");

            $paginator = new PaginatorQueryBuilder(array(
                "builder" => $orders,
                "limit" => 25,
                "page" => $this->request->getQuery("page", "int"),
            ));

            $this->view->ordersPage = $paginator->getPaginate();
        }

	}
	
	public function showAction( $id )
	{
		$order = Order::findFirst(array(
			"conditions" => "id = :id:",
			"bind" => array(
				"id" => $id,
			),
		));
		
		if( !$order )
			return $this->dispatcher->forward(array(
				"controller" => "error",
				"action" => "norFound",
			));
		
		if( $this->request->isPost() AND $this->request->getPost("action") == "update" )
		{
			$allowedStatuses = $order->getStatuses();
			$newStatus = $this->request->getPost("status", "int");
			
			if( isset($allowedStatuses[$newStatus]) )
			{
				$order->status = $newStatus;
			}
			
			$order->save();
			
			return $this->response->redirect(array(
				"for" => "order",
				"id" => $order->id,
			));
		}
		
		if( $this->request->isPost() AND $this->request->getPost("action") == "items" )
		{
			$items = json_decode($order->items);
			$itemsNew = array();
			$itemsData = $this->request->getPost("orders");
			
			foreach( $items AS $itemKey => $item )
			{
				$itemData = $itemsData[$item->code];
				
				$item->status = $itemData["status"];
				
				$itemsNew[$itemKey] = $item;
			}
			
			$order->items = json_encode($itemsNew);
			$order->save();
			
			return $this->response->redirect(array(
				"for" => "order",
				"id" => $order->id,
			));
		}
		
		$this->setTitle("Заказ #".$order->id);
		
		$anotherOrders = Order::find(array(
			"conditions" => "userId = :userId: AND id != :id:",
			"bind" => array(
				"userId" => $order->userId,
				"id" => $order->id,
			),
		));
		
		$this->view->order = $order;
		$this->view->anotherOrders = $anotherOrders;
	}
}