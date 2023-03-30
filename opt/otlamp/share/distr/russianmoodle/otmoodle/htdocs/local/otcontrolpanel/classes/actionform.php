<?php

namespace local_otcontrolpanel;

use local_otcontrolpanel\action\abstract_action;
use local_otcontrolpanel\entity\abstract_entity;
use local_otcontrolpanel\filter\property_in;

require_once $CFG->libdir.'/formslib.php';

class actionform extends \moodleform {

    /** @var \local_otcontrolpanel\action\abstract_action $action */
    public $action;
    public $viewcode;
    public $ids;
    protected $final_report_messages=[];
    protected $header;

    public function get_header() {
        return $this->header;
    }

    public function set_header($header)
    {
        $this->header = $header;
    }

    protected function add_selected_objects() {

        global $OUTPUT;

        $currentview = view::get_view_by_code($this->viewcode);

        // нам нужно создать свою энтитю со своими полями и фильтрами
        $entity = abstract_entity::instance($currentview->entity->get_storagename());
        $field = $entity->add_default_field();
        if (!is_null($field))
        {// у сущности есть настроенное дефолтное поле для отражения - ура! нам будет что отобразить
            $entity->add_filter(new property_in($entity, 'id', $this->ids));
            $entitytable = $entity->get_table();
            $rowscount = $entitytable->get_rows_count();
            if ($rowscount > 0)
            {// и строки были найдены после применения фильтра! юху!
                $mform =& $this->_form;
                // отобразим заголовок
                $a = (object)['objects_count' => $rowscount];
                $headerstr = get_string('selected_objects_header', 'local_otcontrolpanel', $a);
                $mform->addElement('header', 'selected_objects_header', $headerstr);
                $mform->setExpanded('selected_objects_header', false);
                // отобразим список элементов
                $context = $entitytable->export_for_template($OUTPUT);
                $templatename = 'local_otcontrolpanel/list';
                $mform->addElement('html', $OUTPUT->render_from_template($templatename, $context));
            }
        }
    }

    public function definition() {

        $mform =& $this->_form;
        $mform->updateAttributes(['id' => spl_object_hash($this)]);
        $mform->updateAttributes(['class' => $mform->getAttribute('class') . ' actionform']);
        $this->set_display_vertical();
        $this->set_header(get_string('choose_action_header', 'local_otcontrolpanel'));

        $this->viewcode = $this->_customdata['viewcode'] ?? null;
        $this->ids = $this->_customdata['ids'] ?? [];

        $actions = [];
        foreach(abstract_action::get_list_actions($this->viewcode) as $fullcode => $action)
        {
            $actions[$fullcode] = $action->get_display_name();
        }
        if (empty($actions))
        {
            $this->add_final_report_message(get_string('choose_action_noactions', 'local_otcontrolpanel'));
        }

        $this->add_selected_objects();

        $mform->addElement('header', 'choose_action_header', get_string('choose_action_header', 'local_otcontrolpanel'));
        $actionlabel = get_string('choose_action_field', 'local_otcontrolpanel');
        $mform->addElement('select', 'action', $actionlabel, $actions);
    }

    /**
     * Checks if a parameter was passed in the previous form submission
     *
     * @param string $name the name of the page parameter we want
     * @param mixed  $default the default value to return if nothing is found
     * @param string $type expected type of parameter
     * @return mixed
     */
    public function optional_param($name, $default, $type) {
        if (isset($this->_ajaxformdata[$name])) {
            if (is_array($this->_ajaxformdata[$name])) {
                return clean_param_array($this->_ajaxformdata[$name], $type);
            } else {
                return clean_param($this->_ajaxformdata[$name], $type);
            }
        } else {
            return optional_param($name, $default, $type);
        }
    }

    public function definition_after_data() {

        $mform =& $this->_form;

        if (!$this->is_cancelled() and $this->is_submitted())
        {
            $alreadyselected = $this->optional_param('selectedaction', '', PARAM_ALPHANUMEXT);
            $selectedaction = $this->optional_param('action', $alreadyselected, PARAM_ALPHANUMEXT);
            $mform->addElement('hidden', 'selectedaction', $selectedaction);
            $mform->removeElement('choose_action_header');
            $mform->hideIf('action', 'selectedaction', 'eq', $selectedaction);

            foreach(abstract_action::get_list_actions($this->viewcode) as $fullcode => $action)
            {
                if($fullcode == $selectedaction)
                {
                    $this->action = $action;
                    break;
                }
            }

            if (is_a($this->action, '\\local_otcontrolpanel\\action\\abstract_action'))
            {
                if (method_exists($this->action, 'definition_after_data'))
                {
                    $settingsheader = get_string('action_settings_header', 'local_otcontrolpanel');
                    $mform->addElement('header', 'action_settings', $settingsheader);
                    $this->action->definition_after_data($this, $mform);
                }
            }

        } else {
            $this->add_action_buttons(false, get_string('choose_action_submit', 'local_otcontrolpanel'));
        }
    }

    public function validation($data, $files)
    {
        $errors = [];
        if (is_a($this->action, '\\local_otcontrolpanel\\action\\abstract_action'))
        {
            if (method_exists($this->action, 'validation'))
            {
                $errors = array_merge_recursive($errors, $this->action->validation($this, $data, $files));
            }
        }
        return $errors;
    }

    public function process_form_data()
    {
        $formdata = $this->get_data();
        if (is_a($this->action, '\\local_otcontrolpanel\\action\\abstract_action'))
        {
            if (method_exists($this->action, 'process_form_data'))
            {
                $this->action->process_form_data($this, $formdata);
            }
        }
    }

    public function add_final_report_message(string $message)
    {
        $this->final_report_messages[] = $message;
    }

    public function render() {
        global $OUTPUT;

        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }

        if (count($this->final_report_messages) > 0)
        {
            return $OUTPUT->render_from_template(
                'local_otcontrolpanel/final_report',
                [
                    'messages' => $this->final_report_messages,
                    'messages_count' => count($this->final_report_messages)
                ]
            );
        } else
        {
            ob_start();
            $this->_form->display();
            $out = ob_get_contents();
            ob_end_clean();
            return $out;
        }
    }
}