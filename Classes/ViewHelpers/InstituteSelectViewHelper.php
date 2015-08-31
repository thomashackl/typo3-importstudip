<?php

class Tx_ImportStudip_ViewHelpers_InstituteSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

    protected function renderOptionTags($options) {
        $output = '';
        foreach ($options as $faculty) {
            $output .= $this->renderOptionTag($faculty['id'], $faculty['name'], $this->isSelected($faculty['id']));
            foreach ($faculty['children'] as $c) {
                $output .= $this->renderOptionTag($c['id'], '  '.$c['name'], $this->isSelected($c['id']), true);
            }
        }
        return $output;
    }

    protected function renderOptionTag($value, $label, $isSelected, $isChild = false) {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . ($isChild ? '&nbsp;&nbsp;' : '') . htmlspecialchars($label) . '</option>';
        return $output;
    }

}
