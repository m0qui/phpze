<?php

class SessionCacheHelper
{
    const SESSION_CACHE_FILE = '.session.cache';

    function store_session($token, $accountId)
    {
        $now = date_create('now');
        $date = date_format($now, 'Y-m-d');
        $time = date_format($now, 'H:i:s');
        if (!empty($token) && !empty($accountId)) {
            $data = array('date' => $date, 'time' => $time, 'token' => $token, 'id' => $accountId);
            $json = json_encode($data);
            $filehandler = fopen(self::SESSION_CACHE_FILE, 'w');
            fwrite($filehandler, $json);
            fclose($filehandler);
        } else {
            die('Incomplete session data: token=' . $token . ' accountId=' . $accountId);
        }
    }

    function is_session_expired()
    {
        if ($this->check_for_existing_session()) {
            $data = $this->read_session();
            $now = date_create('now');
            $date = date_format($now, 'Y-m-d');
            if ($data['date'] == $date) {
                return false;
            }
        }
        return true;
    }

    function check_for_existing_session()
    {
        return file_exists(self::SESSION_CACHE_FILE);
    }

    function read_session()
    {
        $filehandler = fopen(self::SESSION_CACHE_FILE, 'r');
        $data = json_decode(fread($filehandler, filesize(self::SESSION_CACHE_FILE)), TRUE);
        fclose($filehandler);
        return $data;
    }
}