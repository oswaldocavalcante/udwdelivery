<?php

class ManifestItem 
{
    public $name;
    public $quantity;
    public $size;
    public $dimensions;
    public $price;
    public $must_be_upright;
    public $wight;
    public $var_percentage;

    public function __construct( $name, $quantity )
    {
        $this->name = $name;
        $this->quantity = $quantity;
    }
}