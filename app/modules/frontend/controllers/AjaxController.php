<?php
namespace Molly\Frontend\Controllers;

use Molly\Models\FeedBack,
        Molly\Models\Picking,
	Molly\Models\Order,
	Molly\Frontend\Forms\FeedBack AS FeedBackForm,
        Molly\Frontend\Forms\Picking AS PickingForm;

class AjaxController extends ControllerBase
{
	public function ajaxAction()
	{
		$this->view->disable();
		$this->response->setContentType("application/json", "UTF-8");
		
		switch( $this->request->get("action") )
		{
			case "citySearch":
				$result = $this->geocode->search($this->request->get("city"), "(cities)");
				
				return $this->response->setJsonContent(array("error" => false, "data" => array(
					"words" => $this->request->get("city"),
					"result" => $result,
				)));
				break;
			
			case "place":
				$result = $this->geocode->details($this->request->get("placeId"));
				
				return $this->response->setJsonContent(array("error" => false, "data" => array(
					"result" => $result->result,
				)));
				break;
			
			case "feedback":
				$feedback = new FeedBack();
				$form = new FeedBackForm();
				
				$postData = $this->request->getPost();
				$postData["phone"] = preg_replace("/[^0-9]/", "", $postData["phone"]);
				
                                $form->bind($postData, $feedback);
				
				if( !$form->isValid() )
				{
					$messages = array();
					
					foreach( $form->getMessages() AS $message )
					{
						$messages[] = array(
							"field" => $message->getField(),
							"message" => $message->getMessage()
						);
					}
					
					return $this->response->setJsonContent(array(
						"error" => true,
						"messages" => $messages,
					));
				}
				
				if( !$feedback->save() )
				{
					$messages = array();
					
					foreach( $feedback->getMessages() AS $message )
					{
						$messages[] = array(
							"field" => $message->getField(),
							"message" => $message->getMessage()
						);
					}
					
					return $this->response->setJsonContent(array(
						"error" => true,
						"messages" => $messages,
					));
				}
				
				return $this->response->setJsonContent(array(
					"error" => false,
				));
				break;

			case "picking":
				$picking = new Picking();
				$form = new PickingForm();
				
				$postData = $this->request->getPost();
				$postData["phone"] = preg_replace("/[^0-9]/", "", $postData["phone"]);
                                
				$note = "Описание:";
                                
                                if ($postData['brand']) {
                                     $note .= "\nМарка: ".$postData['brand']; 
                                }
                                if ($postData['model']) {
                                     $note .= "\nМодель: ".$postData['model'];     
                                }
                                if ($postData['year']) {
                                     $note .= "\nГод: ".$postData['year']; 
                                }
                                if ($postData['vin']) {
                                     $note .= "\nVIN: ".$postData['vin']; 
                                }
                                if ($postData['staff']) {
                                     $note .= "\nСостав: ".$postData['staff']; 
                                }
                                
                                $postData["comment"] = $note;
                                
				$form->bind($postData, $picking);

				if( !$form->isValid() )
				{
					$messages = array();
					
					foreach( $form->getMessages() AS $message )
					{
						$messages[] = array(
							"field" => $message->getField(),
							"message" => $message->getMessage()
						);
					}
					
					return $this->response->setJsonContent(array(
						"error" => true,
						"messages" => $messages,
					));
				}
				
				if( !$picking->save() )
				{
					$messages = array();
					
					foreach( $picking->getMessages() AS $message )
					{
						$messages[] = array(
							"field" => $message->getField(),
							"message" => $message->getMessage()
						);
					}
					
					return $this->response->setJsonContent(array(
						"error" => true,
						"messages" => $messages,
					));
				}
                                
				return $this->response->setJsonContent(array(
					"error" => false,
				));
				break;

			case "reOrder":
				$order = Order::findFirst(array(
					"conditions" => "id = :id: AND userId = :userId:",
					"bind" => array(
						"id" => $this->request->get("id", "int"),
						"userId" => $this->auth->getCurrentId(),
					),
				));
				
				if( !$order )
				{
					return $this->response->setJsonContent(array(
						"error" => true,
						"errorMsg" => "Заказ не найден",
					));
				}
				
				$this->cart->clear();
				
				foreach( json_decode($order->items) AS $item )
				{
					$this->cart->add($item->code, $item->count);
				}
				
				return $this->response->setJsonContent(array(
					"error" => false,
				));
				break;
			
			case "cancelOrder":
				$order = Order::findFirst(array(
					"conditions" => "id = :id: AND userId = :userId:",
					"bind" => array(
						"id" => $this->request->get("id", "int"),
						"userId" => $this->auth->getCurrentId(),
					),
				));
				
				if( !$order )
				{
					return $this->response->setJsonContent(array(
						"error" => true,
						"errorMsg" => "Заказ не найден",
					));
				}
				
				if( $order->isClosed() )
				{
					return $this->response->setJsonContent(array(
						"error" => true,
						"errorMsg" => "Заказ уже завершен",
					));
				}
				
				$order->close()->save();
				
				return $this->response->setJsonContent(array(
					"error" => false,
				));
				break;
			
			case "addToCart":
				$code = $this->request->get("code");
				
				$this->cart->add($code);
				
				return $this->response->setJsonContent(array(
					"error" => false,
					"data" => array(
						"count" => $this->cart->getCount(),
					),
				));
				break;
			
			case "addToCartFull":
				$code = $this->request->get("code");
				$count = $this->request->get("count", "int");
				
				if( $count < 1 ) $count = 1;
				
				$this->cart->add($code, $count);
				
				return $this->response->setJsonContent(array(
					"error" => false,
					"data" => array(
						"count" => $this->cart->getCount(),
					),
				));
				break;
			
			case "removeFromCart":
				$code = $this->request->get("code");
				
				$this->cart->remove($code);
				
				return $this->response->setJsonContent(array(
					"error" => false,
					"data" => array(
						"count" => $this->cart->getCount(),
					),
				));
				break;
			
			default:
				return $this->response->setJsonContent(array("error" => true, "errorMsg" => "Action not found"));
				break;
		}
	}
}