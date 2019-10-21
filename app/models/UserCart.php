<?php

namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserCart extends Model
{
    public $id;
    public $active;
    public $createdAt;
    public $userId;
    public $code;
    public $count;
    public $items;

    public function initialize()
    {
        $this->setSource("molly_users_cart");
    }

    public function getSource()
    {
        return "molly_users_cart";
    }

    public function beforeValidationOnCreate()
    {
        if( !$this->createdAt )
            $this->createdAt = (new \DateTime())->format("Y-m-d H:i:s");
    }
}