<?php

namespace Molly\Models;

use Phalcon\Mvc\Model;

class CatalogLocalSteklo extends Model
{
    public $id;
    public $supplier;
    public $hash;
    public $oem;

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
        $this->setSource("molly_catalog_local_steklo");
    }

    public function getSource()
    {
        return "molly_catalog_local_steklo";
    }
}