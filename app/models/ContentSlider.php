<?php

namespace Molly\Models;

use Phalcon\Mvc\Model;

class ContentSlider extends Model
{
    public $id;
    public $createdAt;
    public $userId;
    public $name;
    public $title;
    public $path;

    public function initialize()
    {
        $this->belongsTo(
            "userId",
            "Molly\Models\User",
            "id",
            array(
                "alias" => "user",
            )
        );
        $this->setSource("molly_content_slider");
    }

    public function getSource()
    {
        return "molly_content_slider";
    }
}