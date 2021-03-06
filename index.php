<?php
require_once 'data.php';
require_once 'classloader.php';
require_once 'enums/chargemode.php';
header('Content-Type: text/html; charset=utf-8');

global $car;
global $kamareon;
global $id_token;

/**
 * Start charging
 */
if (isset($_GET['startcharging'])) {
    $kamareon->start_charging($car, $id_token);
}

/**
 * Start AC
 */
if (isset($_GET['startac'])) {
    $kamareon->start_ac($car, $id_token, CONFIG_AC_TEMP);
}

/**
 * Cancel AC
 */
if (isset($_GET['stopac'])) {
    $kamareon->stop_ac($car, $id_token);
}

/**
 * Set charge mode
 */
if(isset($_GET['chargemode'])) {
    switch ($_GET['chargemode']) {
        default:
        case 'always':
            $kamareon->send_charge_mode($car, $id_token, CHARGE_MODE_ALWAYS);
            break;
        case 'schedule':
            $kamareon->send_charge_mode($car, $id_token, CHARGE_MODE_SCHEDULE);
            break;
    }
}

/**
 * Redirect to remove GET parameters:
 * Stupid, because we have to re-do all the data.php stuff. Good: because page refresh doesn't trigger last action again
 * Stupid, because why now use POST? Good: bookmarkable actions.
 */
if (isset($_GET) && !empty($_GET)) {
    header("HTTP/1.1 302 Moved Temporarily");
    header("Location: ".parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
    header("Connection: close");
}

const CHARGING_STATUS = array('0' => 'Not charging',
    '0.1' => 'Waiting for planned charge',
    '0.2' => 'Charge ended',
    '0.3' => 'Waiting for current charge',
    '0.4' => 'Energy flap opened',
    '1' => 'Charging',
    '-1' => 'Not charging or plugged in',
    '-1.1' => 'Not available');

const PLUG_STATUS = array(0 => 'Unplugged',
    1 => 'Plugged in',
    -1 => 'Plug error',
    -2147483648 => 'Not available');

/**
 * HTML output/templating
 */
$html_replace = NULL;
$html_replace_with = NULL;
$content = NULL;
$template = NULL;
switch ($car->model) {
    case ZOE_PH1:
        if (!empty(CONFIG_TEMPLATE)) {
            $template = CONFIG_TEMPLATE;
        } else {
            $template = 'zoe1';
        }
        if ($car->charging_status) {
            $content = file_get_contents('templates/' . $template . '-charging.html');
        } else {
            $content = file_get_contents('templates/' . $template . '.html');
        }
        $html_replace = array(
            '{CAR_NAME}',
            '{REFRESH_URL}',
            '{DATA_TIMESTAMP}',
            '{MILEAGE}',
            '{PLUGGED_IN}',
            '{CHARGING}',
            '{REMAINING}',
            '{POWER}',
            '{BATTERY_LEVEL}',
            '{BATTERY_ENERGY}',
            '{RANGE}',
            '{BATTERY_TEMP}',
            '{OUTDOOR_TEMP}',
            '{HIDDEN_WHEN_NOT_PLUGGED_IN}'
        );
        $html_replace_with = array(
            CONFIG_NAME,
            parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
            $car->timestamp,
            $car->mileage,
            PLUG_STATUS[$car->plug_status],
            CHARGING_STATUS[strval($car->charging_status)], // PHP is one hell of a drug (reg. float array keys)
            $car->remaining_charging_time,
            $car->get_instantaneous_power(),
            $car->battery_level,
            $car->battery_energy,
            $car->range,
            $car->battery_temperature,
            $car->outdoor_temperature,
            (($car->plug_status == 1) ? '' : 'hidden')
        );
        break;
    case ZOE_PH2:
        if (!empty(CONFIG_TEMPLATE)) {
            $template = CONFIG_TEMPLATE;
        } else {
            $template = 'zoe2';
        }
        if ($car->charging_status) {
            $content = file_get_contents('templates/' . $template . '-charging.html');
        } else {
            $content = file_get_contents('templates/' . $template . '.html');
        }
        $html_replace = array(
            '{CAR_NAME}',
            '{REFRESH_URL}',
            '{DATA_TIMESTAMP}',
            '{MILEAGE}',
            '{PLUGGED_IN}',
            '{CHARGING}',
            '{REMAINING}',
            '{POWER}',
            '{BATTERY_LEVEL}',
            '{BATTERY_ENERGY}',
            '{RANGE}',
            '{BATTERY_TEMP}',
            '{GPS_LAT}',
            '{GPS_LON}',
            '{GPS_TIME}',
            '{HIDDEN_WHEN_NOT_PLUGGED_IN}'
        );
        $html_replace_with = array(
            CONFIG_NAME,
            parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
            $car->timestamp,
            $car->mileage,
            PLUG_STATUS[$car->plug_status],
            CHARGING_STATUS[strval($car->charging_status)],
            $car->remaining_charging_time,
            $car->get_instantaneous_power(),
            $car->battery_level,
            $car->battery_energy,
            $car->range,
            $car->battery_temperature,
            $car->gps_latitude,
            $car->gps_longitude,
            $car->gps_timestamp,
            (($car->plug_status == 1) ? '' : 'hidden')
        );
        break;
    default:
        break;
}
echo str_replace($html_replace, $html_replace_with, $content);