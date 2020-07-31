<?php

class CvsDataStorage implements DataStorage
{
    const DATA_CSV_FILENAME = 'data.csv';
    const DATA_CVS_DELIMITER = ';';
    private $filename_prefix = '';

    /**
     * @param $car Car|ZoePh1|ZoePh2 Car object to store data of
     */
    function store_data(&$car)
    {
        $header = false;
        if (!file_exists($this->filename_prefix . self::DATA_CSV_FILENAME)) {
            $header = true;
        }
        $file = fopen($this->filename_prefix . self::DATA_CSV_FILENAME, 'a');

        switch ($car->model) {
            case ZOE_PH2:
                if ($header) {
                    fputcsv($file, array("Timestamp", "Mileage", "Battery level", "Battery temperature", "Battery energy", "Instantaneous power", "Range", "Charging status", "Plug status", "Remaining charging time", "GPS timestamp", "GPS latitude", "GPS longitude"), self::DATA_CVS_DELIMITER);
                }
                fputcsv($file, array(
                    $car->timestamp,
                    $car->mileage,
                    $car->battery_level,
                    $car->battery_temperature,
                    $car->battery_energy,
                    $car->get_instantaneous_power(),
                    $car->range,
                    $car->charging_status,
                    $car->plug_status,
                    $car->remaining_charging_time,
                    $car->gps_timestamp,
                    $car->gps_latitude,
                    $car->gps_longitude

                ), self::DATA_CVS_DELIMITER);
                break;
            case ZOE_PH1:
                if ($header) {
                    fputcsv($file, array("Timestamp", "Mileage", "Battery level", "Battery temperature", "Battery energy", "Instantaneous power", "Range", "Charging status", "Plug status", "Remaining charging time", "Outdoor temperature"), self::DATA_CVS_DELIMITER);
                }
                fputcsv($file, array(
                    $car->timestamp,
                    $car->mileage,
                    $car->battery_level,
                    $car->battery_temperature,
                    $car->battery_energy,
                    $car->get_instantaneous_power(),
                    $car->range,
                    $car->charging_status,
                    $car->plug_status,
                    $car->remaining_charging_time,
                    $car->outdoor_temperature

                ), self::DATA_CVS_DELIMITER);
                break;
        }
        fclose($file);
    }

    function set_prefix($prefix)
    {
        $this->filename_prefix = $prefix;
    }
}