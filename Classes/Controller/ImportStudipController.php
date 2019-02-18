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

namespace UniPassau\Importstudip\Controller;

use UniPassau\Importstudip\Utility\StudipConnector;
use UniPassau\Importstudip\Utility\StudipExternalPage;

class ImportStudipController extends \TYPO3\CMS\Extbase\MVC\Controller\ActionController {

    public function InitializeAction()
    {
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

        // Generate form for course search.
        if ($this->settings['pagetype'] == 'searchpage') {

            $this->view->assign('showsearch', true);

            $semesters = $this->getSemesters();
            $this->view->assign('semesters', array_reverse($semesters['semesters']));
            // Current semester is pre-selected
            $this->view->assign('semester', $semesters['current']);

            // Get all institutes and build a value => name array.
            $this->view->assign('institutes', $this->getInstitutes());
            // If a pre-selected institute is set in backend, set it.
            $this->view->assign('institute', $this->settings['preselectinst']);

            // Get all coursetypes and build a value => name array.
            $coursetypes = $this->getCourseTypes();
            $this->view->assign('coursetypes', $coursetypes);

        } else {

            $this->view->assign('showsearch', false);

            if ($this->settings['makelink'] && !StudipExternalPage::urlParameters(intval($this->configurationManager->getContentObject()->data['uid']))) {
                $this->view->assign('makelink', true);
                // Create a link that will fetch the configured Stud.IP external page.
                $this->view->assign('link', $this->makeLink());
                $this->view->assign('linktext', $this->settings['linktext']);
                $this->view->assign('linkformat', $this->settings['linkformat']);
            } else {
                // Fetch Stud.IP external page.
                $content = StudipExternalPage::get(intval($GLOBALS['TSFE']->id),
                    intval($this->configurationManager->getContentObject()->data['uid']),
                    $this->settings, $this->controllerContext->getUriBuilder());
            }

            // UTF8-encode the content if necessary.
            if (!$this->utf) {
                $content = utf8_decode($content);
            }

            // Assign Stud.IP output to view.
            $this->view->assign('studipcontent', $content);

        }

    }

    public function searchcourseAction()
    {

        // Get request values.
        if ($this->request->hasArgument('searchterm')) {
            $searchterm = trim($this->request->getArgument('searchterm'));
            $this->view->assign('searchterm', $searchterm);
        }

        // Fill values for search form.
        $semesters = $this->getSemesters();
        $this->view->assign('semesters', array_reverse($semesters['semesters']));
        // Current semester is pre-selected
        $this->view->assign('semester', $semesters['current']);

        // Get all institutes and build a value => name array.
        $this->view->assign('institutes', $this->getInstitutes());
        // If a pre-selected institute is set in backend, set it.
        $this->view->assign('institute', $this->settings['preselectinst']);

        // Get all coursetypes and build a value => name array.
        $coursetypes = $this->getCourseTypes();
        $this->view->assign('coursetypes', $coursetypes);

        if ($this->request->hasArgument('semester')) {
            $semester = $this->request->getArgument('semester');
            $this->view->assign('semester', $semester);
        }
        if ($this->request->hasArgument('institute')) {
            $institute = $this->request->getArgument('institute');
            $this->view->assign('institute', $institute);
        }
        if ($this->request->hasArgument('coursetype')) {
            $coursetype = $this->request->getArgument('coursetype');
            $this->view->assign('coursetype', $coursetype);
        }

        if (trim($searchterm)) {
            // Get search results.
            $results = json_decode(StudipConnector::frontendSearchCourse($searchterm, $semester, $institute, $coursetype), true);
            $this->view->assign('searchresults', $results);
            $this->view->assign('numresults', count($results));

            $configurationUtility = $this->objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
            $config = $configurationUtility->getCurrentConfiguration('importstudip');

            $studip_url = $config['studip_url']['value'];
            if (substr($studip_url, 0, 1) == '/') {
                $studip_url = substr($studip_url, 1);
            } else {
                $studip_url = $studip_url;
            }
            $this->view->assign('studip_url', $studip_url);
        } else {
            $this->view->assign('nosearchterm', 1);
        }

    }

    /**
     * AJAX endpoint for calls from extension Javascript.

     * @param array $params Array of parameters from the AJAX interface, currently unused
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler|NULL $ajaxObj Object of type AjaxRequestHandler
     * @return void
     */
    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
    {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        if (method_exists('UniPassau\\ImportStudip\\AjaxAction', $action)) {
            UniPassau\Importstudip\AjaxController::$action();
        } else {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.rest_access_error', 'importstudip').' '.$response->response,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message.error', 'importstudip'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            return $message->render();
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
     * @throws \Exception|\TYPO3\CMS\Extbase\Property\Exception
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response) {
        try {
            parent::processRequest($request, $response);
        }
        catch(\TYPO3\CMS\Extbase\Property\Exception $e) {
            if ($e->getPrevious() instanceof \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException) {
                $GLOBALS['TSFE']->pageNotFoundAndExit($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    private function getSemesters()
    {
        $semesters = array();
        $current = '';
        // Get all semesters and build a value => name array.
        foreach (json_decode(StudipConnector::getAllSemesters(), true) as $semester) {
            $semesters[$semester['semester_id']] = $semester['description'];
            if (!$current && !$semester['past']) {
                $current = $semester['semester_id'];
            }
        }
        return array('semesters' => $semesters, 'current' => $current);
    }

    private function getInstitutes()
    {
        return json_decode(StudipConnector::getInstitutes('institute'), true);
    }

    private function getCourseTypes()
    {
        return json_decode(StudipConnector::getCourseTypes(), true);
    }

    private function makeLink()
    {
        if ($this->settings['linktarget']) {
            $targetPage = StudipExternalPage::getTargetPage($this->settings['linktarget']);
            $targetElement = $this->settings['linktarget'];
        } else {
            $targetPage = $GLOBALS['TSFE']->id;
            $targetElement = intval($this->configurationManager->getContentObject()->data['uid']);
        }
        // Build base path to current page.
        $link = $this->controllerContext->getUriBuilder()
            ->reset()
            ->setTargetPageUid($targetPage)
            ->setCreateAbsoluteUri(true)
            ->buildFrontendUri();
        if (strpos($link, '?') !== false) {
            $link .= '&';
        } else {
            $link .= '?';
        }
        // Add current content element as target parameter.
        $link .= 'target='.$targetElement;

        // Add necessary parameters for Stud.IP call.
        // The basic parameters...
        $link .= '&module=' . $this->settings['module'];
        $link .= '&config_id=' . $this->settings['externconfig'];
        $link .= '&range_id=' . $this->settings['institute'];

        // ... everything else depends on set page type.
        switch ($this->settings['pagetype']) {

            // Show a single course.
            case 'coursedetails':
                $link .= '&seminar_id=' . $this->settings['coursesearch'];
                break;
            // Show a single person.
            case 'persondetails':
                $link .= '&username=' . $this->settings['personsearch'];
                break;

        }

        return $link;

    }

}
