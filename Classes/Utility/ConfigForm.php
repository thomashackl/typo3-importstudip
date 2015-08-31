<?php

/**
 * Input specification for flexform values.
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

namespace UniPassau\ImportStudip\Utility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Utility/StudipConnector.php');

class ConfigForm {

    public function getExternConfigTypes($parameters, $config) {
        $result = '<div id="tx-importstudip-pagetypes">';
        $result .= '<script type="text/javascript" src="'.
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').
            'Resources/Public/JavaScript/tx_importstudip.js"></script>';
        $result .= '<style type="text/css">
            @import url("'.
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('importstudip').
            'Resources/Public/Css/tx_importstudip.css")
            </style>';
        $types = StudipConnector::getExternConfigTypes();
        foreach ($types as $type) {
            $result .= '<input type="radio" id="' .$type[1].
                '" onclick="Tx_ImportStudip.changeSelection(\'pagetype\')" name="'.
                $parameters['itemFormElName'].'" value="'.$type[1].'"'.
                ($type[1] == $parameters['itemFormElValue'] ? ' checked="checked"' : '').
                '/><label for="'.$type[1].'">'.trim($type[0]).'</label><br/>';
        }
        $result .= '<input type="radio" id="searchpage" '.
            'onclick="Tx_ImportStudip.changeSelection(\'pagetype\')" name="'.
            $parameters['itemFormElName'].'" value="searchpage"'.
            ($parameters['itemFormElValue'] == 'searchpage' ? ' checked="checked"' : '').
            '/><label for="searchpage">'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'backend.label.searchpage',
                'importstudip')).'</label><br/>';
        $result .= '</div>';
        return $result;
    }

    public function getInstitutes($parameters, $config) {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        $hierarchy = $extConf['studip_use_hierarchy'];
        $result = '<div id="tx-importstudip-institutes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'" data-inst-treetype="'.$hierarchy.'" '.
            'data-loading-text="'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.loading', 'importstudip')).'">';
        if ($parameters['itemFormElValue']) {
            $result .= self::getInstituteForm(json_decode(
                StudipConnector::getInstitutes($hierarchy)),
                $parameters['itemFormElName'], $parameters['itemFormElValue'], $hierarchy);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#tx-importstudip-institutes").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getInstituteForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<ul class="tx-importstudip-tree">';
        foreach ($data as $entry) {
            $id = 'tx-importstudip-institute-'.($entry->id ?: $entry->tree_id);
            $html .= '<li class="'.
                ($entry->children ? 'tx-importstudip-treebranch' : 'tx-importstudip-treeleaf').'">';
            if ($entry->children) {
                $path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
                $html .= '<img class="tx-importstudip-openclose" src="'.$path.
                    'gfx/ol/plusonly.gif" data-swap-img="'.$path.
                    'gfx/ol/minusonly.gif"/>';
            }
            if ($entry->selectable) {
                $html .= '<input type="radio" class="tx-importstudip-selector" '.
                    'name="'.$inputname.'" value="'.$entry->id.
                    '" onclick="Tx_ImportStudip.changeSelection(\'institute\')"'.
                    ($entry->id == $selected ? ' checked="checked"' : '').
                    '/>';
            }
            $html .= '<label for="'.$id.'">'.$entry->name.'</label>'.
                '<input type="checkbox" class="tx-importstudip-treeinput" id="'.
                $id.'"/>';
            if ($entry->children != null) {
                $html .= self::getInstituteForm($entry->children, $inputname, $selected, $parameters);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getExternConfigurations($parameters, $config) {
        $result = '<div id="tx-importstudip-externconfig" data-input-name="'.
            $parameters['itemFormElName'].'" data-loading-text="'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.loading', 'importstudip')).
            '">';
        if ($parameters['itemFormElValue']) {
            $config = self::getConfig($parameters);
            $result .= self::getExternConfigurationsForm(
                json_decode(StudipConnector::getExternConfigurations(
                $config['settings.institute'], $config['settings.pagetype'])),
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#tx-importstudip-externconfig").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getExternConfigurationsForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<select name="'.$inputname.'" size="1" onchange="Tx_ImportStudip.setModuleName()">';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->id.'" data-module="'.
                $entry->type.'"'.
                ($entry->id==$selected ? ' selected="selected"' : '').'>'.
                $entry->name.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getModule($parameters, $config) {
        $result = '<div id="tx-importstudip-module" data-input-name="'.
            $parameters['itemFormElName'].'">';
        $result .= '<input type="hidden" name="'.
            $parameters['itemFormElName'].'" value="'.
            $parameters['itemFormElValue'].'"/>';
        $result .= '</div>';
        return $result;
    }

    public function getModuleForm($value, $inputname) {
        return $html;
    }

    public function getPersonSearch($parameters, $config) {
        $html = '<div id="tx-importstudip-personsearch" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= '<input type="text" id="tx-importstudip-personsearchterm" size="40" maxlength="255" placeholder="'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.placeholder.personsearch', 'importstudip')).
            '">';
        $html .= '<button type="button" id="tx-importstudip-execute-personsearch" onclick="Tx_ImportStudip.performPersonSearch()">'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.execute_search', 'importstudip')).
            '</button>';
        $html .= '<div>'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.personsearch', 'importstudip').'</div>';
        $html .= '<div id="tx-importstudip-personsearch-result">';
        if ($parameters['itemFormElValue']) {
            $selected = StudipConnector::getUser($parameters['itemFormElValue']);
            $html .= self::getPersonSearchForm($selected, $parameters['itemFormElName'],
                $parameters['itemFormElValue'], $parameters);
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function getPersonSearchForm($data, $inputname, $value, $parameters=array()) {
        $html = '<select name="'.$inputname.'" id="tx-importstudip-choose-user" data-input-name="'.$inputname.
            '" data-input-value="'.$value.'" size="1" onchange="Tx_ImportStudip.getPersonInstitutes()">';
        foreach ($data as $entry) {
            $fullname = $entry['lastname'].', '.$entry['firstname'];
            if ($entry['prefix']) {
                $fullname = $entry['prefix'].' '.$fullname;
            }
            if ($entry['suffix']) {
                $fullname = $fullname.', '.$entry['suffix'];
            }
            $fullname .= ' ('.$entry['username'].')';
            $html .= '<option value="'.$entry['username'].'" data-user-id="'.$entry['user_id'].'"'.
                ($entry['username'] == $value ? ' selected="selected"' : '').'>'.$fullname.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function chooseUserInstitute($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-choose-user-institute" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $institutes = json_decode(StudipConnector::getUserInstitutes($config['settings.personsearch']), true);
            // We need only non-selfassigned institutes.
            $institutes = $institutes['collection']['work'];
            usort(
                $institutes,
                function($a, $b) {
                    return strnatcasecmp($a['name'], $b['name']);
                }
            );
            $html .= self::chooseUserInstituteForm($institutes, $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseUserInstituteForm($data, $inputname, $value, $parameters=array())
    {
        $html = '<select name="'.$inputname.'" onchange="Tx_ImportStudip.getExternConfigurations(\'user-select\')">';
        foreach ($data as $i) {
            $html .= '<option value="'.$i['institute_id'].'"'.
                ($i['institute_id'] == $value ? ' selected="selected"' : '').
                '>'.$i['name'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getCourseSearch($parameters, $config) {
        $html = '<div id="tx-importstudip-coursesearch" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= '<input type="text" id="tx-importstudip-coursesearchterm" size="40" maxlength="255" placeholder="'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.placeholder.personsearch', 'importstudip')).
            '">';
        $html .= '<select id="tx-importstudip-semester" size="1">';
        $html .= '<option value="">'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.allsemesters', 'importstudip').'</option>';
        foreach (json_decode(StudipConnector::getAllSemesters()) as $semester) {
            $html .= '<option value="'.$semester->semester_id.'">'.$semester->description.'</option>';
        }
        $html .= '</select>';
        $html .= '<button type="button" id="tx-importstudip-execute-coursesearch" onclick="Tx_ImportStudip.performCourseSearch()">'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.execute_search', 'importstudip')).
            '</button>';
        $html .= '<div>'.trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.text.coursesearch', 'importstudip')).'</div>';
        $html .= '<div id="tx-importstudip-coursesearch-result">';
        if ($parameters['itemFormElValue']) {
            $html .= self::getCourseSearchForm(
                array(json_decode(StudipConnector::getCourse($parameters['itemFormElValue']), true)),
                $parameters['itemFormElName'],
                $parameters['itemFormElValue'], $parameters);
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function getCourseSearchForm($data, $inputname, $value, $parameters) {
        $html = '<select name="'.$inputname.'" size="1" id="tx-importstudip-choose-course" '.
            'onchange="Tx_ImportStudip.getCourseInstitutes()">';
        foreach ($data as $entry) {
            $fullname = $entry['name'];
            if ($entry['number']) {
                $fullname = $entry['number'].' '.$fullname;
            }
            $fullname .= ' ('.$entry['type'].', '.$entry['semester'].')';
            $html .= '<option value="'.$entry['course_id'].'"'.
                ($entry['course_id'] == $value ? ' selected="selected"' : '').'>'.
                $fullname.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function chooseCourseInstitute($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-choose-course-institute" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($parameters['itemFormElValue']) {
            $institute = json_decode(StudipConnector::getInstitute($parameters['itemFormElValue']), true);
            $institutes = array($institute);
            $html .= self::chooseUserInstituteForm($institutes, $parameters['itemFormElName'],
                $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function chooseCourseInstituteForm($data, $inputname, $value, $parameters=array()) {
        $html = '<select name="'.$inputname.'" onchange="Tx_ImportStudip.getExternConfigurations(\'course-select\')">';
        foreach ($data as $i) {
            $html .= '<option value="'.$i['institute_id'].'"'.
                ($i['institute_id']==$value ? ' selected="selected"' : '').
                '>'.$i['name'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getAdditionalFilters() {
        $html = '<div id="tx-importstudip-filters">';
        $html .= '</div>';
        return $html;
    }

    public function getAggregation($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-aggregate" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        if ($config['settings.pagetype'] == 'courses' || $config['settings.pagetype'] == 'persons') {
            $html .= self::getAggregationForm($parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function getAggregationForm($inputname, $value) {
        $html = '<input type="checkbox" name="'.$inputname.'"'.
            ($value ? ' checked="checked"' : '').'/>';
        return $html;
    }

    public function getCourseTypes($parameters, $config) {
        $html = '<div id="tx-importstudip-coursetypes" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' ||
                \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'courses') {
            $html .= self::getCourseTypeForm(
                json_decode($config['settings.institute']),
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $html .= '</div>';
        return $html;
    }

    public function getCourseTypeForm($data, $inputname, $selected) {
        $html = '<select name="'.$inputname.'" size="1">';
        $html .= '<option value="">'.\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'tx_importstudip.backend.label.select', 'importstudip').'</option>';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry->id.'"'.
                ($entry->id==$selected ? ' selected="selected"' : '').'>'.
                $entry->type.' ('.$entry->classname.')</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getSubjects($parameters, $config) {
        $html = '<div id="tx-importstudip-subjects" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'courses' ||
                \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'courses') {
            $html .= self::getSubjectForm(
                json_decode(StudipConnector::getSubjects('root', 1, $parameters['itemFormElValue'])),
                $parameters['itemFormElName'], $parameters['itemFormElValue'],
                $parameters);
        }
        $html .= '</div>';
        return $html;
    }

    public function getSubjectForm($data, $inputname, $selected, $parameters=array()) {
        $html = '<ul class="tx-importstudip-tree">';
        foreach ($data as $entry) {
            $id = 'tx-importstudip-subject-'.$entry->tree_id;
            $html .= '<li class="'.
                ($entry->num_children ? 'tx-importstudip-treebranch' : 'tx-importstudip-treeleaf').'">';
            if ($entry->num_children > 0) {
                $path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
                $html .= '<img class="tx-importstudip-openclose" src="'.$path.
                    'gfx/ol/plusonly.gif" data-swap-img="'.$path.
                    'gfx/ol/minusonly.gif"/>';
            }
            if ($entry->id) {
                $html .= '<input type="radio" class="tx-importstudip-selector" '.
                    'name="'.$inputname.'" value="'.$entry->id.'"'.
                    ($entry->id == $selected ? ' checked="checked"' : '').
                    '/>';
            }
            $html .= '<label for="'.$id.'">'.$entry->name.'</label>'.
                '<input type="checkbox" class="tx-importstudip-treeinput" id="'.
                $id.'"';
            if ($entry->num_children && !$entry->children) {
                $html .= 'onclick="return Tx_ImportStudip.getSubjects(\''.$entry->id.'\')"';
            }
            $html .= '/>';
            if ($entry->children) {
                $html .= self::getSubjectForm($entry->children, $inputname, $selected, $parameters);
            } else if ($entry->num_children) {
                $html .= '<ul class="tx-importstudip-tree"><li data-loading-text="'.
                    trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'tx_importstudip.backend.label.loading', 'importstudip')).
                    '">&nbsp;</li></ul>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getStatusgroups($parameters, $config) {
        $html = '<div id="tx-importstudip-statusgroups" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $config = self::getConfig($parameters);
        if ($config['settings.pagetype'] == 'persons' ||
                \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('configtype') == 'persons') {
            $html .= self::getStatusgroupForm(
                json_decode(StudipConnector::getStatusgroupNames($config['settings.institute'])),
                $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $html .= '</div>';
        return $html;
    }

    public function getStatusgroupForm($data, $inputname, $selected) {
        $html = '<select name="'.$inputname.'" size="1">';
        $html .= '<option value="">'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'tx_importstudip.backend.label.all', 'importstudip')).
            '</option>';
        foreach ($data as $entry) {
            $html .= '<option value="'.$entry.'"'.
                ($entry==$selected ? ' selected="selected"' : '').'>'.
                $entry.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getPreselectedInstitute($parameters, $config) {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['importstudip']);
        $result = '<div id="tx-importstudip-preselectinst" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'" data-inst-treetype="institute" '.
            'data-loading-text="'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.label.loading', 'importstudip')).'">';
        if ($parameters['itemFormElValue']) {
            $result .= self::getInstituteForm(json_decode(
                StudipConnector::getInstitutes('institute')),
                $parameters['itemFormElName'], $parameters['itemFormElValue']);
        }
        $result .= '</div>';
        if (!$parameters['itemFormElValue']) {
            $result .= '<script type="text/javascript">
            //<!--
            TYPO3.jQuery("#tx-importstudip-institutes").closest(".t3-form-field-container").hide();
            //-->
            </script>';
        }
        return $result;
    }

    public function getLinkingOptions() {
        $html = '<div id="tx-importstudip-linkingoptions">';
        $html .= '</div>';
        return $html;
    }

    public function getMakeLink($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-makelink" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= self::getMakeLinkForm($parameters['itemFormElName'], $parameters['itemFormElValue']);
        $html .= '</div>';
        return $html;
    }

    public function getMakeLinkForm($inputname, $value) {
        $html = '<input type="checkbox" name="'.$inputname.'"'.
            ($value ? ' checked="checked"' : '').'/>';
        return $html;
    }

    public function getLinkText($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-linktext" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= self::getLinkTextForm($parameters['itemFormElName'], $parameters['itemFormElValue']);
        $html .= '</div>';
        return $html;
    }

    public function getLinkTextForm($inputname, $value) {
        $html = '<input type="text" name="'.$inputname.'" size="60" maxlength="255" value="'.$value.'"/>';
        return $html;
    }

    public function getLinkFormat($parameters, $config) {
        $config = self::getConfig($parameters);
        $html = '<div id="tx-importstudip-linkformat" data-input-name="'.
            $parameters['itemFormElName'].'" data-input-value="'.
            $parameters['itemFormElValue'].'">';
        $html .= self::getLinkFormatForm($parameters['itemFormElName'], $parameters['itemFormElValue']);
        $html .= '</div>';
        return $html;
    }

    public function getLinkFormatForm($inputname, $value) {
        $html = '<select name="'.$inputname.'" value="'.$value.'"/>';
        $html .= '<option value=""'.($value == '' ? ' selected="selected"' : '').'>'.
            trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'tx_importstudip.backend.label.normaltext', 'importstudip')).'</option>';
        for ($i = 1 ; $i <= 4 ; $i++) {
            $html .= '<option value="h'.$i.'"'.($value == 'h'.$i ? ' selected="selected"' : '').'>'.
                trim(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'tx_importstudip.backend.label.h'.$i, 'importstudip')).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public static function getConfig($data) {
        $result = array();
        // Extract already configured flexform values.
        $xml = simplexml_load_string($data['row']['pi_flexform']);
        $json = json_encode($xml);
        $fullConfig = json_decode($json, true);
        $fullConfig = $fullConfig['data']['sheet']['language']['field'];
        foreach ($fullConfig as $c) {
            $result[$c['@attributes']['index']] = $c['value'];
        }
        return $result;
    }

}
