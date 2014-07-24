<?php

namespace UniPassau\ImportStudip;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \UniPassau\ImportStudip\RESTAccessException;

require_once(ExtensionManagementUtility::extPath($_EXTKEY).'Classes/RESTAccessException.php');
require_once(ExtensionManagementUtility::extPath($_EXTKEY).'Resources/Private/PHP/restclient/restclient.php');

class StudipRESTHelper {

    private $client = null;
    private $config = null;

    public function __construct() {
        $this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        if ($this->config['studip_url'] && $this->config['studip_api_path'] && $this->config['studip_api_username'] && $this->config['studip_api_password']) {
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
            $this->client = new \RestClient(array(
                'base_url' => $url,
                'format' => 'json',
                'username' => $this->config['studip_api_username'],
                'password' => $this->config['studip_api_password']
            ));
        } else {
            throw new RESTAccessException(LocalizationUtility::translate('tx_importstudip.exception.incomplete_api_data', 'importstudip'), 1406188236);
        }
    }

    public function call($route) {
        return $this->client->get($route);
    }

}
