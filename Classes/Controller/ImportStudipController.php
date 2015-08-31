<?php

/**
 * ImportStudip Controller
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
 * @subpackage Controller
 * @author     Thomas Hackl <thomas.hackl@uni-passau.de>
 */

namespace UniPassau\ImportStudip\Controller;

require_once(realpath(__DIR__.'/../ViewHelpers/InstituteSelectViewHelper.php'));
require_once(realpath(__DIR__.'/../ViewHelpers/CourseTypeSelectViewHelper.php'));

use UniPassau\ImportStudip\Utility\StudipConnector;
use UniPassau\ImportStudip\Utility\StudipExternalPage;

class ImportStudipController extends \TYPO3\CMS\Extbase\MVC\Controller\ActionController {

    public function initializeAction() {
        // Check for site charset and set local variable accordingly
        if (stripos($GLOBALS['TSFE']->metaCharset, 'utf') !== false) {
            $this->utf = true;
        }
    }

    /**
     * Standard action for showing content when the extension is called.
     */
    public function indexAction()
    {
        if ($this->settings['pagetype'] != 'searchpage') {
            // Fetch Stud.IP external page.
            $content = StudipExternalPage::get(intval($GLOBALS['TSFE']->id),
                intval($this->configurationManager->getContentObject()->data['uid']),
                $this->settings, $this->controllerContext->getUriBuilder());

            // UTF8-encode the content if necessary.
            if ($this->utf) {
                $content = utf8_encode($content);
            }

            // Assign Stud.IP output to view.
            $this->view->assign('studipcontent', $content);
        } else {

            $semesters = array();
            $current = '';
            // Get all semesters and build a value => name array.
            foreach (json_decode(StudipConnector::getAllSemesters(), true) as $semester) {
                $semesters[$semester['semester_id']] = $semester['description'];
                if (!$current && !$semester['past']) {
                    $current = $semester['semester_id'];
                }
            }
            $this->view->assign('semesters', array_reverse($semesters));
            // Current semester is pre-selected
            $this->view->assign('selected_semester', $current);

            // Get all institutes and build a value => name array.
            $this->view->assign('institutes', json_decode(StudipConnector::getInstitutes('institute'), true));
            // If a pre-selected institute is set in backend, set it.
            $this->view->assign('institute', $this->settings['preselectinst']);

            // Get all coursetypes and build a value => name array.
            $coursetypes = json_decode(StudipConnector::getCourseTypes(), true);
            $this->view->assign('coursetypes', $coursetypes);
        }
    }

    public function searchcourseAction($searchterm, $semester='', $institute='')
    {

    }

    /**
     * AJAX endpoint for calls from extension Javascript.

     * @param array $params Array of parameters from the AJAX interface, currently unused
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler|NULL $ajaxObj Object of type AjaxRequestHandler
     * @return void
     */
    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        if (method_exists('UniPassau\\ImportStudip\\AjaxAction', $action)) {
            UniPassau\ImportStudip\AjaxController::$action();
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.rest_access_error', 'importstudip').' '.$response->response,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return $message->render;
        }
    }

}
