<?php

require_once(realpath(__DIR__.'/../../Resources/Private/PHP/php-restclient/restclient.php'));

namespace \UniPassau\ImportStudip;

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class StudipRESTHelper {

    private $client = null;

    public function __construct() {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        if ($config['studip_url'] && $config['studip_api_path']) {
            $url = $config['studip_url'];
            if (substr($url, -1, 1) != '/') {
                $url .= '/';
            }
            if (substr($config['studip_api_path'], 0, 1) != '/') {
                $url .= substr($config['studip_api_path'], 1);
            } else {
                $url .= $config['studip_api_path'];
            }
            $this->client = new RestClient(array(
                'base_url' => $url, 
                'format' => 'json'
            ));
        } else {
            throw new Exception(LocalizationUtility::translate('tx_importstudip.exception.incomplete_api_path', 'importstudip'));
        }
    }

    public function call($route) {
        $this->client->get($route);
    }

}
