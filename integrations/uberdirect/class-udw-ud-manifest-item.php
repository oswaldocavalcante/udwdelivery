<?php

class ManifestItem 
{
    public $name;
    public $quantity;
    public $size;
    public $dimensions;
    public $price;
    public $weight;
    public $vat_percentage;

    public function __construct( $name, $quantity )
    {
        $this->name = $name;
        $this->quantity = $quantity;
    }
}