<?php

require_once(realpath(__DIR__.'/../../Resources/Private/PHP/php-restclient/restclient.php'));

class Tx_ImportStudip_Domain_Model_StudipRESTHelper {

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
            throw new Exception(Tx_Extbase_Utility_Localization::translate('tx_importstudip.exception.incomplete_api_path'));
        }
    }

    public function call($route) {
        $this->client->get($route);
    }

}
