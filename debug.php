<?php
require_once 'config.php';
require_once 'classloader.php';

header('Content-Type: text/plain; charset=utf-8');

/**
 * @param KamereonService $kamareon
 * @param GigyaData $gigya_data
 * @param string $url
 * @param string[] $replace_patterns
 * @param string[] $replace_placeholders
 */
function print_api_endpoint(KamereonService $kamareon, GigyaData $gigya_data, $url, $replace_patterns, $replace_placeholders)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $kamareon->build_default_header_data($gigya_data->id_token));
    $response = curl_exec($ch);
    //$obj = json_decode($response);
    echo censor_output($replace_patterns, $replace_placeholders, 'Endpoint: ' . $url);
    echo censor_output($replace_patterns, $replace_placeholders, 'Data: ' . json_encode(json_decode($response), JSON_PRETTY_PRINT)) . PHP_EOL;
    echo PHP_EOL;
    //return $response;
}

/**
 * @param string[] $replace_patterns
 * @param string[] $replace_placeholders
 * @param string $string
 * @return string
 */
function censor_output($replace_patterns, $replace_placeholders, $string)
{
    return ((!CONFIG_CENSOR_OUTPUT || isset($_GET['uncensored']) || (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'uncensored')) ? $string : preg_replace($replace_patterns, $replace_placeholders, $string)) . PHP_EOL;
}

$cache_storge = new CacheDataStorage();
$cached_data = $cache_storge->read_data_for_debug();

$kamareon = new KamereonService(CONFIG_COUNTRY);
$gigya = new GigyaService(CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_COUNTRY);
$gigya_data = $gigya->retrieve_gigya_data();
if (!$gigya_data) {
    die('Gigya service error: please check login');
}
if (!$kamareon->retrieve_kamereon_account_id($gigya_data->id_token, $gigya_data->person_id)) {
    die('Kamareon service error: please check login');
};

$replace_patterns = array(
    '([A-HJ-NPR-Z]|[0-9]){11}[0-9]{6}',
    '\/accounts\/.*?\/',
    CONFIG_USERNAME,
    CONFIG_PASSWORD,
    $gigya_data->id_token,
    $gigya_data->person_id,
    $kamareon->kamereon_account_id,
    '"firstName": ".*?"',
    '"lastName": ".*?"',
    '"idpId": ".*?"',
    '"partyId": ".*?"',
    '"gpsLatitude": [0-9]{1,2}\.[0-9]{1,10}',
    '"gpsLongitude": [0-9]{1,2}\.[0-9]{1,10}',
    '"gps_latitude": [0-9]{1,2}\.[0-9]{1,10}',
    '"gps_longitude": [0-9]{1,2}\.[0-9]{1,10}'
);
$replace_patterns_with_delimiter = array();
foreach ($replace_patterns as $pattern) {
    array_push($replace_patterns_with_delimiter, '/' . $pattern . '/i');
}
$replace_placeholders = array(
    'VF1**************',
    '/**-**-**-**/',
    'USERNAME',
    'PASSWORD',
    'GIGYA_ID_TOKEN',
    'GIGYA_PERSON_ID',
    'KAMEREON_ACCOUNT_ID',
    '"firstName": "Jane"',
    '"lastName": "Doe"',
    '"idpId": "********************************"',
    '"partyId": "************************************"',
    '"gpsLatitude": 12.34567890',
    '"gpsLongitude": 12.34567890',
    '"gps_latitude": 12.34567890',
    '"gps_longitude": 12.34567890'
);

$urls = array($kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_PERSONS_URI . $gigya_data->person_id . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
foreach (array($kamareon::KAMEREON_API_BACKEND_CAR_ADAPTER_V2_URI, $kamareon::KAMEREON_API_BACKEND_CAR_ADAPTER_V2_URI) as $api_version_backend_url) {
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_BATTERY_STATUS_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_COCKPIT_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_HVAC_STATUS_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_LOCATION_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_LOCK_STATUS_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
    array_push($urls, $kamareon::KAMEREON_API_BACKEND_URL . $kamareon::KAMEREON_API_BACKEND_ACCOUNTS_URI . $kamareon->kamereon_account_id . $api_version_backend_url . CONFIG_VIN . $kamareon::KAMEREON_API_BACKEND_NOTIFICATION_SETTINGS_URI . $kamareon::KAMEREON_API_BACKEND_COUNTRY_PARAM . CONFIG_COUNTRY);
}

if ($cached_data) {
    echo censor_output($replace_patterns_with_delimiter, $replace_placeholders, "Cache:");
    echo censor_output($replace_patterns_with_delimiter, $replace_placeholders, 'Data: ' . json_encode(json_decode($cached_data), JSON_PRETTY_PRINT));
    echo PHP_EOL;
}

foreach ($urls as $url) {
    print_api_endpoint($kamareon, $gigya_data, $url, $replace_patterns_with_delimiter, $replace_placeholders);
}
