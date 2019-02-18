<?php

namespace UniPassau\Importstudip\ViewHelpers;

class CourseTypeSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

    public function render()
    {
        $this->tag->addAttribute('name', $this->getName());

        $content = $this->tag->render();

        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            $content .= $this->renderOptionTag($value, $label, FALSE) . chr(10);
        }

        $options = $this->arguments['options'];

        $classes = [];
        foreach ($options as $option) {
            $classes[$option['typeclass']] = $option['classname'];
        }
        ksort($classes);

        foreach ($classes as $id => $classname) {
            $content .= '<optgroup label="' . htmlspecialchars($classname) . '">';

            $types = array_filter($options, function ($o) use ($id) {
                return $o['typeclass'] == $id;
            });

            foreach ($types as $type) {
                $content .= $this->renderOptionTag($type['id'], $type['type'], $this->isSelected($type['id']));
            }

            $content .= '</optgroup>';
        }

        return $content;
    }

}
