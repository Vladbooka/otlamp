<?php

trait group_export_for_template {

    public function export_for_template(renderer_base $output) {

        $context = $this->export_for_template_base($output);

        $this->_renderedfromtemplate = true;

        include_once('HTML/QuickForm/Renderer/Default.php');

        $elements = [];
        $name = $this->getName();
        $i = 0;
        foreach ($this->_elements as $key => $element) {
            $elementname = '';
            if ($this->_appendName) {
                $elementname = $element->getName();
                if (isset($elementname)) {
                    $element->setName($name . '['. (strlen($elementname) ? $elementname : $key) .']');
                } else {
                    $element->setName($name);
                }
            }
            $element->_generateId();

            // В ОТЛИЧИЕ ОТ СТАНДАРТНОГО МЕТОДА МЫ ИСПОЛЬЗУЕМ СВОЙ РЕНДЕРЕР
            $outputrenderer = new dof_modlib_widgets_form_renderer();
            $out = $outputrenderer->mform_element($element, false, false, '', true);

            if (empty($out)) {
                $renderer = new HTML_QuickForm_Renderer_Default();
                $renderer->setElementTemplate('{element}');
                $element->accept($renderer);
                $out = $renderer->toHtml();
            }

            // Replicates the separator logic from 'pear/HTML/QuickForm/Renderer/Default.php'.
            $separator = '';
            if ($i > 0) {
                if (is_array($this->_separator)) {
                    $separator = $this->_separator[($i - 1) % count($this->_separator)];
                } else if ($this->_separator === null) {
                    $separator = '&nbsp;';
                } else {
                    $separator = (string) $this->_separator;
                }
            }

            $elements[] = [
                'separator' => $separator,
                'html' => $out
            ];

            // Restore the element's name.
            if ($this->_appendName) {
                $element->setName($elementname);
            }

            $i++;
        }

        $context['groupname'] = $name;
        $context['elements'] = $elements;
        $context['elementrawhtml'] = $this->toHtml();
        return $context;
    }
}