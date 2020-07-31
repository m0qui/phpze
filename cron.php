<?php
require_once 'data.php';
require_once 'classloader.php';
header('Content-Type: text/plain; charset=utf-8');
$send_email = new SendEmailService();
global $cached_car;
global $car;
global $mail_batterylevel;
global $status;
if ($cached_car->charging_status && !$car->charging_status) {
    echo "Finished charging";
    $send_email->send_email_fully_charged($car);
    // mail($username, $zoename, "Ladevorgang beendet.\nAkkustand: " . $batteryLevel . " %\nStatusupdate: " . $car->timestamp);
}
if (!$cached_car->charging_status && ($cached_car->charging_status < 1) && ($car->charging_status >= 1)) {
    echo "Started charging";
    $send_email->reset_email_send_status();
}
if (!$car->charging_status && $cached_car->battery_level != NULL && ($cached_car->battery_level < $car->battery_level)) {
    echo "Mad regenerative brake, bro!";
}

if ($car->charging_status && ($car->battery_level > $mail_batterylevel)) {
    echo "Battery level reached";
    $send_email->send_email_threshold_reached($car);
}
echo "Object mismatch:" . PHP_EOL;
var_dump($cached_car != $car);
echo "Battery data retrieval okay:" . PHP_EOL;
var_dump($status);