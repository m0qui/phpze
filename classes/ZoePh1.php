<?php
require_once 'enums/carmodels.php';

class ZoePh1 extends Car
{
    public $outdoor_temperature = NULL;

    // Weather data (only available with ZOE_PH1

    function __construct($vin)
    {
        $this->model = ZOE_PH1;
        $this->vin = $vin;
    }

    public function get_instantaneous_power()
    {
        return $this->instantaneous_power / 1000;
    }
}