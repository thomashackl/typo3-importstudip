<?php

namespace UniPassau\ImportStudip;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \UniPassau\ImportStudip\RESTAccessException;

require_once(realpath(__DIR__.'/../Resources/Private/PHP/restclient/restclient.php'));
require_once(realpath(__DIR__.'/../Resources/Private/PHP/oauth/OAuth.php'));

class StudipRESTHelper {

    private $client = null;

    public function __construct() {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        if ($config['studip_url'] && $config['studip_api_path'] && $config['consumer_key'] && $config['consumer_secret']) {
            /*
             * Build correct URL (check for slashes between path parts and try
             * to remove double slashes in address)
             */
            $url = $config['studip_url'];
            if (substr($url, -1, 1) != '/') {
                $url .= '/';
            }
            if (substr($config['studip_api_path'], 0, 1) == '/') {
                $url .= substr($config['studip_api_path'], 1);
            } else {
                $url .= $config['studip_api_path'];
            }
            $url = str_replace('//', '/', $url);
            // Build OAuth headers for request.
            $consumer = new \OAuthConsumer($config['consumer_key'], $config['consumer_secret']);
            $request = \OAuthRequest::from_consumer_and_token($consumer, NULL, 'GET', $url, NULL);
            $request->sign_request(new \OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);
            $auth_header = $request->to_header();
            $this->client = new \RestClient(array(
                'base_url' => $url,
                'format' => 'json',
                'headers' => array($auth_header)
            ));
        } else {
            throw new RESTAccessException(LocalizationUtility::translate('tx_importstudip.exception.incomplete_api_data', 'importstudip'), 1406188236);
        }
    }

    public function call($route) {
        return $this->client->get($route);
    }

}
