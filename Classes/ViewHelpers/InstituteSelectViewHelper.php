<?php

namespace UniPassau\Importstudip\ViewHelpers;

class InstituteSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

    protected function getOptions() {
        $options = [];

        foreach ($this->arguments['options'] as $option) {
            $options = array_merge($options, $this->buildOptions($option));
        }

        return $options;
    }

    private function buildOptions($parent, $indent = '') {
        $options = [
            $parent['id'] === 'studip' ? '' : $parent['id'] => $indent . $parent['name']
        ];

        if (is_array($parent['children']) && count($parent['children']) > 0) {
            foreach ($parent['children'] as $child) {
                $options = array_merge($options,
                    $this->buildOptions($child, $indent . html_entity_decode('&nbsp;&nbsp;')));
            }
        }

        return $options;
    }

}
