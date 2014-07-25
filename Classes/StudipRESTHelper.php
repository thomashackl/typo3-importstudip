<?php

namespace UniPassau\ImportStudip;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Resources/Private/PHP/restclient/restclient.php');

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
            $url = $this->config['studip_url'];
            if (substr($url, -1, 1) != '/') {
                $url .= '/';
            }
            if (substr($this->config['studip_api_path'], 0, 1) == '/') {
                $url .= substr($this->config['studip_api_path'], 1);
            } else {
                $url .= $this->config['studip_api_path'];
            }
            $this->client = new \RestClient(array(
                'base_url' => $url,
                'username' => $this->config['studip_api_username'],
                'password' => $this->config['studip_api_password']
            ));
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.incomplete_api_config', 'importstudip'),
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
        }
    }

    public function call($route) {
        $response = $this->client->get($route);
        //echo 'Response:<pre>'.print_r($response, 1).'</pre>';
        if ($response->info->http_code == '200') {
            return json_decode($response->response);
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.rest_access_error', 'importstudip').' '.$response->response,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_importstudip.message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
        }
    }

}
