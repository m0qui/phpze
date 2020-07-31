<?php

interface DataStorage
{
    /**
     * @param $car Car|ZoePh1|ZoePh2 Car object to store data of
     * @return mixed
     */
    function store_data(&$car);

    /**
     * @param $prefix string Filename prefix
     */
    function set_prefix($prefix);
}