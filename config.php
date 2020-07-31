<?php
// Display name
const CONFIG_NAME = 'Renault Zoe';

// Zoe Model (1|2)
const CONFIG_MODEL = 2;

// MyRenault username
const CONFIG_USERNAME = 'user@domain.tld';

// MyRenault password
const CONFIG_PASSWORD = '*******';

// VIN
const CONFIG_VIN = 'VF1**************';

// Country (for API)
const CONFIG_COUNTRY = 'DE';

// Target temperatur for AC
const CONFIG_AC_TEMP = 21;

// Wether to remove user sensitive data from debug.php output (please check output before posting!)
const CONFIG_CENSOR_OUTPUT = true;

// Custom template: you need two files, basename.html and basename-charging.html
const CONFIG_TEMPLATE = NULL;
//const CONFIG_TEMPLATE = 'basename';

// Not implemented yet
const SEND_EMAIL = false;
const SEND_EMAIL_FULLY_CHARGED = true;
const SEND_EMAIL_BATTERY_THRESHOLD = true;