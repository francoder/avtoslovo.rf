<?php
namespace Molly\Models;

use Phalcon\Mvc\Model;

class UserGroup extends Model
{
    public $id;
    public $title;
    public $admin;
    
    public function initialize()
    {
        $this->setSource("molly_users_groups");
    }
    
    public function getSource()
	{
		return "molly_users_groups";
	}
}