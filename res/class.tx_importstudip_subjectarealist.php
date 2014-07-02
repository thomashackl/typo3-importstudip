<?php

require_once('class.tx_importstudip_soap.php');

/**
 * Provides functionality for building a custom select box with a subject 
 * hierarchy from Stud.IP. The normal TYPO3 API isn't sufficient here because
 * the hierarchy is nested.
 */
class tx_importstudip_subjectarealist {
	
	/**
	 * Builds a select box with a "select all" entry at first place. The
	 * recursive hierarchy traversal is done in buildSubjectAreaList.
	 *
	 * @param array $PA the current page and flexform configuration data
	 * @param array $fobj some TYPO3 stuff
	 * @return HTML code with the subject area hierarchy wrapped in a 
	 * select box with appropriate indenting.
	 */
	function getSubjectAreaList($PA, $fobj) {
		$isSOAP = new tx_importstudip_soap();
		$parameters = array('items' => array(), 'row' => array('pi_flexform' => $PA['row']['pi_flexform']));
		// Get the subject area hierarchy from Stud.IP with a SOAP call
		$subjectAreas = $isSOAP->getSubjectAreasForInstitute($parameters);
		// Select box
		$result = '<select name="'.$PA['itemFormElName'].'">';
		// First entry is "select all"
		$result .= '<option value="">-- alle --</option>';
		// Recursive traversal of the hierarchy
		$result .= tx_importstudip_subjectarealist::buildSubjectAreaList($subjectAreas, $PA['itemFormElValue']);
		$result .= '</select>';
		return $result;
	}
	
	/**
	 * Helper function for recursively building HTML optgroups and option 
	 * fields from the given subject hierarchy.
	 *
	 * @param array $subjectAreas the hierarchy
	 * @param string $currentValue the currently selected value
	 * @param int $level level value for tracing the recursion level and 
	 * setting text indentation
	 * @return HTML code with the hierarchy values wrapped in optgroups 
	 * and options.
	 */
	function buildSubjectAreaList($subjectAreas, $currentValue, $level=0) {
		$result = '';
		$prefix = '';
		// Make text indentation according to current recursion level
		for ($i=0 ; $i<$level ; $i++) {
			$prefix .= '&nbsp;&nbsp;';
		}
		// Traverse the subject areas
		foreach ($subjectAreas as $subjectArea) {
			// Child elements found -> make an (non-selectable) optgroup
			if ($subjectArea['children']) {
				$result .= '<optgroup label="'.$prefix.$subjectArea['name'].'">';
				$result .= tx_importstudip_subjectarealist::buildSubjectAreaList($subjectArea['children'], $level+1);
				$result .= '</optgroup>';
			// No child elements -> make a (selectable) option
			} else {
				// Current value is selected
				if ($subjectArea['id'] == $currentValue)
					$selected = ' selected="selected"';
				else
					$selected = '';
				$result .= '<option value="'.$subjectArea['id'].'"'.$selected.'>'.$prefix.$subjectArea['name'].'</option>';
			}
		}
		return $result;
	}
	
}

?>