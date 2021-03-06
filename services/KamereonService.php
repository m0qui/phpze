<?php
require_once 'enums/chargemode.php';

class KamereonService
{
    const KAMEREON_API_KEY = 'Ae9FDWugRxZQAGm3Sxgk7uJn6Q4CGEA2';
    const KAMEREON_API_BACKEND_URL = 'https://api-wired-prod-1-euw1.wrd-aws.com/';
    const KAMEREON_API_BACKEND_PERSONS_URI = 'commerce/v1/persons/';
    const KAMEREON_API_BACKEND_ACCOUNTS_URI = 'commerce/v1/accounts/';
    const KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI = '/kamereon/kca/car-adapter/v1/cars/';
    const KAMEREON_API_BACKEND_CAR_ADAPTER_V2_URI = '/kamereon/kca/car-adapter/v2/cars/';
    const KAMEREON_API_BACKEND_COUNTRY_PARAM = '?country=';
    const KAMEREON_API_BACKEND_BATTERY_STATUS_URI = '/battery-status';
    const KAMEREON_API_BACKEND_COCKPIT_URI = '/cockpit';
    const KAMEREON_API_BACKEND_LOCATION_URI = '/location';
    const KAMEREON_API_BACKEND_LOCK_STATUS_URI = '/lock-status';
    const KAMEREON_API_BACKEND_CHARGING_URI = '/actions/charging-start';
    const KAMEREON_API_BACKEND_AC_URI = '/actions/hvac-start';
    const KAMEREON_API_BACKEND_CHARGE_MODE_URI = '/actions/charge-mode';
    const KAMEREON_API_BACKEND_NOTIFICATION_SETTINGS_URI = '/notification-settings';
    const KAMEREON_API_BACKEND_HVAC_STATUS_URI = '/hvac-status';

    public $kamereon_account_id = NULL;
    private $kamereon_country_code = NULL;

    /**
     * KamereonService constructor.
     * @param string $kamereon_country_code Country (ISO 3166-1 alpha-2)
     */
    public function __construct($kamereon_country_code)
    {
        $this->kamereon_country_code = $kamereon_country_code;
    }

    /**
     * Retrieve KamereonService Account ID
     *
     * @param string $id_token GigyaData ID token
     * @param string $person_id GiygaData person ID
     * @return string KamereonService Account ID
     */
    function retrieve_kamereon_account_id($id_token, $person_id)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_PERSONS_URI . $person_id . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $response_data_kamereon = $curl_helper->exec_curl_with_header($this->build_default_header_data($id_token), $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['accounts'])) {
            $kamereon_account_id = $response_data_kamereon['accounts'][0]['accountId'];
            $this->kamereon_account_id = $kamereon_account_id;
            return $kamereon_account_id;
        }
        return false;
    }

    /**
     * Buidl default header data array
     *
     * @param string $id_token GigyaData ID token
     * @return string[] header data array
     */
    public function build_default_header_data($id_token)
    {
        return array(
            'apikey: ' . self::KAMEREON_API_KEY,
            'x-gigya-id_token: ' . $id_token,
        );
    }

    /**
     * @param string $id_token GigyaData ID token
     * @return string[] header data array
     */
    public function build_default_header_data_for_payload($id_token)
    {
        $header = $this->build_default_header_data($id_token);
        array_push($header, 'Content-type: application/vnd.api+json');
        return $header;
    }

    /**
     * @param Car $car Car object to store data in
     * @param string $id_token GigyaData ID token
     * @return boolean wether or not the battery data retrieval was successful
     */
    function retrieve_vehicle_battery_data(&$car, $id_token)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V2_URI . $car->vin . self::KAMEREON_API_BACKEND_BATTERY_STATUS_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $response_data_kamereon = $curl_helper->exec_curl_with_header($this->build_default_header_data($id_token), $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data']) && isset($response_data_kamereon['data']['attributes'])) {
            $timestamp_from_format = date_create_from_format(DATE_ISO8601, $response_data_kamereon['data']['attributes']['timestamp'], timezone_open('UTC'));
            $timestamp_with_timezone = date_timezone_set($timestamp_from_format, timezone_open('Europe/Berlin'));
            $timestamp = date_format($timestamp_with_timezone, 'Y-m-d H:m:s');
            $car->charging_status = $response_data_kamereon['data']['attributes']['chargingStatus'];
            $car->plug_status = $response_data_kamereon['data']['attributes']['plugStatus'];
            $car->battery_level = $response_data_kamereon['data']['attributes']['batteryLevel'];
            $car->battery_temperature = $response_data_kamereon['data']['attributes']['batteryTemperature'];
            $car->battery_energy = $response_data_kamereon['data']['attributes']['batteryAvailableEnergy'];
            $car->range = $response_data_kamereon['data']['attributes']['batteryAutonomy'];
            $car->remaining_charging_time = $response_data_kamereon['data']['attributes']['chargingRemainingTime'];
            $car->timestamp = $timestamp;
            $car->instantaneous_power = $response_data_kamereon['data']['attributes']['chargingInstantaneousPower'];
        } else {
            return false;
        }
        return true;
    }

    /**
     * @param Car $car Car object to store data in
     * @param string $id_token GigyaData ID token
     * @return boolean true/false wether or not the milage data was retrieved successfully.
     */
    function retrieve_mileage(&$car, $id_token)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_COCKPIT_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $response_data_kamereon = $curl_helper->exec_curl_with_header($this->build_default_header_data($id_token), $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data'])) {
            $mileage = $response_data_kamereon['data']['attributes']['totalMileage'];
            $car->mileage = $mileage;
            return true;
        }
        return false;
    }

    /**
     * Start charging
     *
     * @param Car $car
     * @param string $id_token
     * @return bool
     */
    function start_charging(Car &$car, $id_token)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_CHARGING_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $payload = '{"data":{"type":"ChargingStart","attributes":{"action":"start"}}}';
        $response_data_kamereon = $curl_helper->exec_curl_with_header_and_payload($this->build_default_header_data_for_payload($id_token), $payload, $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data']) && isset($response_data_kamereon['data']['id'])) {
            return true;
        }
        return false;
    }

    function send_charge_mode(Car &$car, $id_token, $charge_mode)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_CHARGE_MODE_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $payload = '{"data":{"type":"ChargeMode","attributes":{"action":"'.$charge_mode.'"}}}';
        $response_data_kamereon = $curl_helper->exec_curl_with_header_and_payload($this->build_default_header_data_for_payload($id_token), $payload, $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data']) && isset($response_data_kamereon['data']['id'])) {
            return true;
        }
        return false;
    }

    /**
     * Start air conditioning
     *
     * @param Car $car
     * @param string $id_token
     * @param int $temperature
     * @return bool
     */
    function start_ac(Car &$car, $id_token, $temperature)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_AC_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $payload = '{"data":{"type":"HvacStart","attributes":{"action":"start","targetTemperature":"' . intval($temperature) . '"}}}';
        $response_data_kamereon = $curl_helper->exec_curl_with_header_and_payload($this->build_default_header_data_for_payload($id_token), $payload, $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data']) && isset($response_data_kamereon['data']['id'])) {
            return true;
        }
        return false;
    }

    /**
     * Stop air conditioning
     *
     * @param Car $car
     * @param string $id_token
     * @return bool
     */
    function stop_ac(Car &$car, $id_token)
    {
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_AC_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $payload = '{"data":{"type":"HvacStart","attributes":{"action":"cancel"}}}';
        $response_data_kamereon = $curl_helper->exec_curl_with_header_and_payload($this->build_default_header_data_for_payload($id_token), $payload, $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data']) && isset($response_data_kamereon['data']['id'])) {
            return true;
        }
        return false;
    }

    /**
     * @param ZoePh1 $car Car object to store data in
     * @param string $id_token GigyaData ID token
     * @return boolean true/false wether or not the outdoor temperature data was retrieved successfully.
     */
    function retrieve_outdoor_temperature(&$car, $id_token)
    {
        assert($car->model == ZOE_PH1, 'Error: Outdoor temperature only available for ZOE Phase 1.');
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_HVAC_STATUS_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $response_data_kamereon = $curl_helper->exec_curl_with_header($this->build_default_header_data($id_token), $url);
        if (!empty($response_data_kamereon) && isset($response_data_kamereon['data'])) {
            $outdoor_temperature = $response_data_kamereon['data']['attributes']['externalTemperature'];
            $car->outdoor_temperature = $outdoor_temperature;
            return true;
        }
        return false;
    }

    /**
     * @param ZoePh2 $car Car object to store data in
     * @param string $id_token GigyaData ID token
     * @return boolean true/false wether or not the location data was retrieved successfully.
     */
    function retrieve_location(&$car, $id_token)
    {
        assert($car->model == ZOE_PH2, 'Error: Location only available for ZOE Phase 2.');
        $curl_helper = new CurlHelper();
        $url = self::KAMEREON_API_BACKEND_URL . self::KAMEREON_API_BACKEND_ACCOUNTS_URI . $this->kamereon_account_id . self::KAMEREON_API_BACKEND_CAR_ADAPTER_V1_URI . $car->vin . self::KAMEREON_API_BACKEND_LOCATION_URI . self::KAMEREON_API_BACKEND_COUNTRY_PARAM . $this->kamereon_country_code;
        $response_data_kamereon = $curl_helper->exec_curl_with_header($this->build_default_header_data($id_token), $url);
        if (isset($response_data_kamereon) && isset($response_data_kamereon['data'])) {
            $gps_latitude = $response_data_kamereon['data']['attributes']['gpsLatitude'];
            if (!empty($gps_latitude)) {
                $car->gps_latitude = $gps_latitude;
            } else {
                return false;
            }
            $gps_longitude = $response_data_kamereon['data']['attributes']['gpsLongitude'];
            if (!empty($gps_longitude)) {
                $car->gps_longitude = $gps_longitude;
            } else {
                return false;
            }
            $date_from_format = date_create_from_format(DATE_ISO8601, $response_data_kamereon['data']['attributes']['lastUpdateTime'], timezone_open('UTC'));
            $date_with_timezone = date_timezone_set($date_from_format, timezone_open('Europe/Berlin'));
            $timestamp = date_format($date_with_timezone, 'Y-m-d H:i:s');
            if (!empty($timestamp)) {
                $car->gps_timestamp = $timestamp;
            } else {
                return false;
            }
            return true;
        }
        return false;
    }
}