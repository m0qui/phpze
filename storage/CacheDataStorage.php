<?php


class CacheDataStorage implements DataStorage
{
    const CAR_CACHE_FILE = '.car.cache';

    /**
     * @inheritDoc
     */
    function store_data(&$car)
    {
        $json = json_encode($car);
        $filehandler = fopen(self::CAR_CACHE_FILE, 'w');
        fwrite($filehandler, $json);
        fclose($filehandler);
    }

    /**
     * @inheritDoc
     */
    function set_prefix($prefix)
    {
        // TODO: Implement setPrefix() method.
    }

    function read_data(&$car)
    {
        if (file_exists(self::CAR_CACHE_FILE)) {
            $filehandler = fopen(self::CAR_CACHE_FILE, 'r');
            $data = json_decode(fread($filehandler, filesize(self::CAR_CACHE_FILE)), TRUE);
            fclose($filehandler);
            foreach ($data as $key => $value) {
                $car->{$key} = $value;
            }
        }
    }

    function read_data_for_debug()
    {
        if (file_exists(self::CAR_CACHE_FILE)) {
            $filehandler = fopen(self::CAR_CACHE_FILE, 'r');
            $content = fread($filehandler, filesize(self::CAR_CACHE_FILE));
            fclose($filehandler);
            return $content;
        }
    }
}