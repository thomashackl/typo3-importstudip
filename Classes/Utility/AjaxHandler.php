<?php

/**
 * ImportStudip Utility AjaxHandler
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

class AjaxHandler {

    public function handleAjax($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
        $action = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('action');
        $elementId = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('elementid');
        $ajaxObj->setContentFormat('json');
        $ajaxObj->addContent('tx_importstudip', self::$action($elementId));
    }

    public function institutes($elementId = '') {
        $om = new \TYPO3\CMS\Extbase\Object\ObjectManager();
        $configurationUtility = $om->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $config = $configurationUtility->getCurrentConfiguration('importstudip');

        $configtype = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype');
        return StudipConnector::getInstitutes($config['studip_use_hierarchy']['value'], $configtype, $elementId);
    }

    public function instituteform($elementId = '') {
        $institutes = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institutes');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getInstituteForm(json_decode($institutes, true), $inputname, $selected, $elementId);
    }

    public function externconfigurations($elementId = '') {
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        $types = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype');

        return StudipConnector::getExternConfigurations($institute, $types);
    }

    public function externconfigurationsform($elementId = '') {
        $externconfigs = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configurations');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        return ConfigForm::getExternConfigurationsForm(json_decode($externconfigs, true), $inputname, $selected);
    }

    public function personsearch($elementId = '') {
        return ConfigForm::getPersonSearch();
    }

    public function personsearchform($elementId = '') {
        $searchterm = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('searchterm');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        $data = json_decode(StudipConnector::searchUser($searchterm), true);
        return ConfigForm::getPersonSearchForm($data, $inputname, $selected);
    }

    public function personsearchresults($elementId = '') {
        $number = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('number');
        $html = '<div class="tx-importstudip-searchresult-found">';
        if ($number == 1) {
            $html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.searchresult_one', 'importstudip');
        } else {
            $html .= $number.' '.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.searchresult_more', 'importstudip');
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseuserinstitute($elementId = '') {
        $username = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('username');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('selected');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $institutes = StudipConnector::getUserInstitutes($username);
        $institutes = $institutes['collection']['work'] ?: array();
        usort($institutes, function($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });
        return ConfigForm::chooseUserInstituteForm($institutes, $inputname, $selected);
    }

    public function coursesearch($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        return ConfigForm::getCourseSearch();
    }

    public function coursesearchform($elementId = '') {
        $searchterm = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('searchterm');
        $semester = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('semester');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $data = json_decode(StudipConnector::searchCourse($searchterm, $semester), true);
        return ConfigForm::getCourseSearchForm($data, $inputname, $selected);
    }

    public function coursesearchresults($elementId = '') {
        $number = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('number');
        $html = '<div class="tx-importstudip-searchresult-found">';
        if ($number == 1) {
            $html .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.searchresult_one', 'importstudip');
        } else {
            $html .= $number.' '.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.searchresult_more', 'importstudip');
        }
        $html .= '</div>';
        return $html;
    }

    public function choosecourseinstitute($elementId = '') {
        $course_id = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('courseid');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $institutes = json_decode(StudipConnector::getCourse($course_id), true);
        $institutes = array($institutes['home_institute']);
        return ConfigForm::chooseCourseInstituteForm($institutes, $inputname, $selected);
    }

    public function additionalfilters($elementId = '') {
        return ConfigForm::getadditionalFilters();
    }

    public function aggregationform($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        return ConfigForm::getAggregationForm($inputname, $value);
    }

    public function participatingform($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        return ConfigForm::getParticipatingForm($inputname, $value);
    }

    public function coursetypeform($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        return ConfigForm::getCourseTypeForm(json_decode(StudipConnector::getCourseTypes($institute), true),
            $inputname, $value);
    }

    public function subjectsform($elementId = '') {
        $parent_id = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('parent');
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $selected = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        //return StudipConnector::getSubjects($parent_id, 2);
        return ConfigForm::getSubjectForm(json_decode(StudipConnector::getSubjects($parent_id, 2), true),
            $inputname, $selected);
    }

    public function statusgroupform($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        $institute = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('institute');
        return ConfigForm::getStatusgroupForm(json_decode(StudipConnector::getStatusgroupNames($institute), true),
            $inputname, $value);
    }

    public function smallnewsform($elementId = '') {
        $inputname = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('inputname');
        $value = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('value');
        return ConfigForm::getSmallNewsForm($inputname, $value);
    }

}
