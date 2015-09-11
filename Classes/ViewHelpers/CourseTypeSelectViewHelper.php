<?php

namespace UniPassau\Importstudip\ViewHelpers;

class CourseTypeSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

    protected function renderOptionTags($options) {
        $output = '';

        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            $output .= $this->renderOptionTag($value, $label, FALSE) . chr(10);
        }

        foreach ($options as $o) {
            $classes[$o['typeclass']] = $o['classname'];
        }
        ksort($classes);
        foreach ($classes as $cid => $cname) {
            $output .= $this->renderOptionGroupTag($cname, array_filter($options, function($e) use ($cid) {
                return $e['typeclass'] == $cid;
            }));
        }
        return $output;
    }

    protected function renderOptionTag($value, $label, $isSelected) {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . htmlspecialchars($label) . '</option>';
        return $output;
    }

    protected function renderOptionGroupTag($label, $options) {
        $output = '<optgroup label="' . htmlspecialchars($label) . '">';
        foreach ($options as $o) {
            $output .= $this->renderOptionTag($o['id'], $o['type'], $this->isSelected($o['id']));
        }
        $output .= '</optgroup>';
        return $output;
    }

}
