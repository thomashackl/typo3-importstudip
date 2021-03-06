<?php

/**
 * REST API access.
 *
 * This software is published under the GNU General Public License version 2.
 * The license text can be found at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category   Extension
 * @package    ImportStudip
 * @subpackage Utility
 * @author     Thomas Hackl <thomas.hackl@uni-passau.de>
 */

namespace UniPassau\Importstudip\Utility;

class StudipRESTHelper {

    private $client = null;
    private $config = null;

    /**
     * Initializes the helper by creating a new REST client connected to
     * the Stud.IP REST API.
     */
    public function __construct() {

        // Get global extension config.
        $om = new \TYPO3\CMS\Extbase\Object\ObjectManager();
        $configurationUtility = $om->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $this->config = $configurationUtility->getCurrentConfiguration('importstudip');

        // All necessary values set?
        if ($this->config['studip_url']['value'] &&
            $this->config['studip_api_username']['value'] &&
            $this->config['studip_api_password']['value']) {

            /*
             * Build correct URL (check for slashes between path parts and try
             * to remove double slashes in address)
             */
            if (substr($this->config['studip_url']['value'], 0, 1) == '/') {
                $url = substr($this->config['studip_url']['value'], 1);
            } else {
                $url = $this->config['studip_url']['value'];
            }
            $url .= 'api.php';

            // Config for REST API access.
            $restconfig = array(
                'base_url' => $url
            );
            // Add access credentials if set in extension config.
            if ($this->config['studip_api_username']['value']) {
                $restconfig['username'] = $this->config['studip_api_username']['value'];
            }
            if ($this->config['studip_api_password']['value']) {
                $restconfig['password'] = $this->config['studip_api_password']['value'];
            }

            // Initialize REST client.
            $this->client = new \RestClient($restconfig);

        // Some required values are missing -> show an error message.
        } else {

            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.incomplete_api_config', 'importstudip'),
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        }
    }

    /**
     * Call the given route and return collected data.
     *
     * @param String $route the REST route to call
     * @return String|NULL JSON-encoded data as returned by REST call.
     */
    public function call($route) {

        // Call route.
        $response = $this->client->get($route);

        // HTTP code 200 -> all ok, return response.
        if (in_array($response->info->http_code, array(200, 302))) {

            return $response->response;

        // Some other HTTP status code has occured -> show what happened in a message.
        } else {

            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.rest_access_error', 'importstudip').' '.$response->response.'<br/>'.$route,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);

            return null;

        }
    }

}
