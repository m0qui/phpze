<?php

/**
 * Class Car
 */
abstract class Car
{
    // General
    public $vin = NULL;
    public $model = NULL;
    public $timestamp = NULL;

    // Vehicle data
    public $mileage = NULL;
    public $battery_temperature = NULL;
    public $battery_level = NULL;
    public $battery_energy = NULL;
    public $range = NULL;

    // Charging
    public $charging_status = NULL;
    public $plug_status = NULL;
    public $remaining_charging_time = NULL;
    public $instantaneous_power = NULL;

    /**
     * Car constructor.
     * @param $vin string Vehicle identification number
     */
    public function __construct($vin)
    {
        $this->vin = $vin;
    }

    /**
     * @return false|string|null Remaining charging time in hours:minutes
     */
    public function get_remaining_charging_time()
    {
        if ($this->charging_status != 1 && (empty($this->remaining_charging_time) || empty($this->timestamp))) {
            return NULL;
        } else {
            if (!empty($this->remaining_charging_time)) {
                $timeReady = date_create_from_format(DATE_ISO8601, $this->timestamp, timezone_open('UTC'));
                $timeReady = date_add($timeReady, date_interval_create_from_date_string($this->remaining_charging_time . ' minutes'));
                $timeReady = date_timezone_set($timeReady, timezone_open('Europe/Berlin'));
                return date_format($timeReady, 'H:i');
            } else {
                return '';
            }
        }
    }

    abstract protected function get_instantaneous_power();
}