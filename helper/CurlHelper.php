<?php

/**
 * Class CurlHelper
 */
class CurlHelper
{
    /**
     * @param $header_data array POST data
     * @param $url string Data url
     * @return array
     */
    public function exec_curl_with_header(array $header_data, $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);

        return $this->exec_curl($ch);
    }

    /**
     * @param $ch
     * @return bool|mixed
     */
    public function exec_curl(&$ch)
    {
        $response = curl_exec($ch);
        if ($response === FALSE || empty($response) || empty(json_decode($response, TRUE))) {
            return false;
        }

        return json_decode($response, TRUE);
    }

    /**
     * @param $post_data array POST data
     * @param $url string Data url
     * @return array
     */
    public function exec_curl_with_post(array $post_data, $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        return json_decode($response, TRUE);
    }
}