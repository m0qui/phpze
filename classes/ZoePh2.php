<?php
require_once 'enums/carmodels.php';

class ZoePh2 extends Car
{
    public $gps_latitude = NULL;

    // Location (only available with ZOE_PH2
    public $gps_longitude = NULL;
    public $gps_timestamp = NULL;

    function __construct($vin)
    {
        $this->model = ZOE_PH2;
        $this->vin = $vin;
    }

    public function get_instantaneous_power()
    {
        return $this->instantaneous_power / 10;
    }
}