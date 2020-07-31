<?php

/**
 * Class Gigya
 *
 * Handle Gigya API calls using curl
 */
class GigyaService
{

    const GIGYA_ACCOUNTS_LOGIN = 'https://accounts.eu1.gigya.com/accounts.login';
    const GIGYA_ACCOUNTS_JWT = 'https://accounts.eu1.gigya.com/accounts.getJWT';
    const GIGYA_API_KEY_DE = '3_7PLksOyBRkHv126x5WhHb-5pqC1qFR8pQjxSeLB6nhAnPERTUlwnYoznHSxwX668';
    const GIGYA_API_KEY_AT = '3__B4KghyeUb0GlpU62ZXKrjSfb7CPzwBS368wioftJUL5qXE0Z_sSy0rX69klXuHy';
    const GIGYA_API_KEY_SE = '3_EN5Hcnwanu9_Dqot1v1Aky1YelT5QqG4TxveO0EgKFWZYu03WkeB9FKuKKIWUXIS';

    private $username = NULL;
    private $password = NULL;
    private $country = NULL;

    function __construct($username, $password, $country)
    {
        $this->username = $username;
        $this->password = $password;
        $this->country = $country;
    }

    /**
     * @return GigyaData Gigya session data
     */
    function retrieve_gigya_data()
    {
        $curl_helper = new CurlHelper();
        $gigya_data = new GigyaData();
        $post_data_login = array(
            'ApiKey' => $this->get_api_key($this->country),
            'loginId' => $this->username,
            'password' => $this->password,
            'include' => 'data',
            'sessionExpiration' => 60
        );
        $response_data_login = $curl_helper->exec_curl_with_post($post_data_login, self::GIGYA_ACCOUNTS_LOGIN);
        if ($response_data_login && isset($response_data_login['sessionInfo'])) {
            $gigya_data->oauth_token = $response_data_login['sessionInfo']['cookieValue'];
            $post_data_jwt = array(
                'oauth_token' => $gigya_data->oauth_token,
                'fields' => 'data.personId,data.gigyaDataCenter',
                'expiration' => 87000
            );
            $response_data_jwt = $curl_helper->exec_curl_with_post($post_data_jwt, self::GIGYA_ACCOUNTS_JWT);
            if ($response_data_jwt && isset($response_data_jwt['id_token'])) {
                $gigya_data->id_token = $response_data_jwt['id_token'];
                $gigya_data->person_id = $response_data_login['data']['personId'];

                $gigya_data->country = $this->country;

                return $gigya_data;
            }
        }
        return false;
    }

    private function get_api_key($country)
    {
        switch ($country) {
            case 'DE':
                return self::GIGYA_API_KEY_DE;
                break;
            case 'AT':
                return self::GIGYA_API_KEY_AT;
                break;
            case 'SE':
                return self::GIGYA_API_KEY_SE;
                break;
            default:
                return NULL;
        }
    }
}