<?php
/**
 * Auto load classes
 */
spl_autoload_register(function ($class_name) {
    $directorys = array(
        'classes/',
        'enums/',
        'helper/',
        'services/',
        'storage/'
    );
    foreach ($directorys as $directory) {
        //see if the file exsists
        if (file_exists($directory . $class_name . '.php')) {
            require_once($directory . $class_name . '.php');
            return;
        }
    }
});

