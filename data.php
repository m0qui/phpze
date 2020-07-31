<?php
require_once 'config.php';
require_once 'classloader.php';
$car = NULL;
switch (CONFIG_MODEL) {
    case 1:
        $car = new ZoePh1(CONFIG_VIN);
        break;
    case 2:
        $car = new ZoePh2(CONFIG_VIN);
        break;
    default:
        break;
}
$kamareon = new KamereonService(CONFIG_COUNTRY);
$cache_storge = new CacheDataStorage();
$id_token = NULL;

// Read cached car data (if any)
$cache_storge->read_data($car);
$cached_car = clone $car;

/**
 * Restore or create new Gigya/Kamareon session
 */
$session_handler = new SessionCacheHelper();
if ($session_handler->is_session_expired()) {
    $gigya = new GigyaService(CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_COUNTRY);
    $gigya_data = $gigya->retrieve_gigya_data();
    if (!$gigya_data) {
        die('Gigya service error: please check login');
    }
    if (!$kamareon->retrieve_kamereon_account_id($gigya_data->id_token, $gigya_data->person_id)) {
        die('Kamareon service error: please check login');
    };
    $id_token = $gigya_data->id_token;
} else {
    $session_data = $session_handler->read_session();
    $id_token = $session_data['token'];
    $kamareon->kamereon_account_id = $session_data['id'];
}

/**
 * Retrieve vehicle battery data
 */
$status = $kamareon->retrieve_vehicle_battery_data($car, $id_token);
$session_handler->store_session($id_token, $kamareon->kamereon_account_id);
if ($status && ($cached_car != $car)) {
    // Retrieve more vehicle data, if battery data changed.
    $kamareon->retrieve_mileage($car, $id_token);
    switch ($car->model) {
        case ZOE_PH1:
            $kamareon->retrieve_outdoor_temperature($car, $id_token);
            break;
        case ZOE_PH2:
            $kamareon->retrieve_location($car, $id_token);
            break;
        default:
            break;
    }
    $cache_storge->store_data($car);
    $data_storage = new CvsDataStorage();
    $data_storage->store_data($car);
}