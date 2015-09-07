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
        $this->config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);

        // All necessary values set?
        if ($this->config['studip_url'] && $this->config['studip_api_username'] && $this->config['studip_api_password']) {

            /*
             * Build correct URL (check for slashes between path parts and try
             * to remove double slashes in address)
             */
            if (substr($this->config['studip_url'], 0, 1) == '/') {
                $url = substr($this->config['studip_url'], 1);
            } else {
                $url = $this->config['studip_url'];
            }
            $url .= 'api.php';

            // Initialize REST client.
            $this->client = new \RestClient(array(
                'base_url' => $url,
                'username' => $this->config['studip_api_username'],
                'password' => $this->config['studip_api_password']
            ));

        // Some required values are missing -> show an error message.
        } else {

            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.incomplete_api_config', 'importstudip'),
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
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
        if ($response->info->http_code == '200') {

            return $response->response;

        // Some other HTTP status code has occured -> show what happened in a message.
        } else {

            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.rest_access_error', 'importstudip').' '.$response->response.'<br/>'.$route,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);

            return null;

        }
    }

}
