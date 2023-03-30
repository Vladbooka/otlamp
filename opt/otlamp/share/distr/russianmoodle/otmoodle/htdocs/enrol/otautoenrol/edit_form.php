<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * otautoenrol enrolment plugin.
 *
 * This plugin automatically enrols a user onto a course the first time they try to access it.
 *
 * @package    enrol
 * @subpackage otautoenrol
 * @date       July 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class enrol_otautoenrol_edit_form
 */
class enrol_otautoenrol_edit_form extends moodleform {

    protected $conditions = [];

    /**
     *
     */
    public function definition() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/otautoenrol/locallib.php');
        list($instance, $plugin, $context) = $this->_customdata;


        $defaultvalues = fullclone($instance);
        $this->data_preprocessing($defaultvalues);

        $this->add_hidden_fields();
        $this->add_general_section($instance, $plugin, $context);
        $this->add_filtering_section($instance);
        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($defaultvalues);
    }

    function validation($data, $files) {
        $errors = [];

        if ( ! empty($data['conditionfieldval']) )
        {
            $emptycount = 0;
            foreach ( $data['conditionfieldval'] as $val)
            {
                if ( empty($val) )
                {
                    $emptycount++;
                }
            }
            if ( $emptycount > 1 )
            {
                $errors['customint2'] = get_string('only_one_empty_value', 'enrol_otautoenrol');
            }
        }

        return $errors;
    }

    /**
     * @param $instance
     * @param $plugin
     * @param $context
     *
     * @throws coding_exception
     */
    protected function add_general_section($instance, $plugin, $context) {
        global $CFG, $OUTPUT;

        // Основные настройки
        $this->_form->addElement('header', 'generalsection', get_string('general', 'enrol_otautoenrol'));
        $this->_form->setExpanded('generalsection');

        // Предупреждение
        $this->_form->addElement(
                'static', 'description', html_writer::tag('strong', get_string('warning', 'enrol_otautoenrol')),
                get_string('warning_message', 'enrol_otautoenrol'));

        // Название способа записи
        $this->_form->addElement('text', 'customchar2', get_string('instancename', 'enrol_otautoenrol'));
        $this->_form->setType('customchar2', PARAM_TEXT);
        $this->_form->setDefault('customchar2', '');
        $this->_form->addHelpButton('customchar2', 'instancename', 'enrol_otautoenrol');

        // Роль
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $this->_form->addElement('select', 'customint3', get_string('role', 'enrol_otautoenrol'), $roles);
        $this->_form->addHelpButton('customint3', 'role', 'enrol_otautoenrol');
        if (!has_capability('enrol/otautoenrol:method', $context)) {
            $this->_form->disabledIf('customint3', 'customchar3');
        }
        $this->_form->setDefault('customint3', $plugin->get_config('defaultrole'));
        $this->_form->setType('customint3', PARAM_INT);

        // В какой момент подписывать
        $method = array(get_string('m_site', 'enrol_otautoenrol'), get_string('m_course', 'enrol_otautoenrol'));
        $this->_form->addElement('select', 'customint1', get_string('method', 'enrol_otautoenrol'), $method);
        if (!has_capability('enrol/otautoenrol:method', $context)) {
            $this->_form->disabledIf('customint1', 'customchar3');
        }
        $this->_form->setType('customint1', PARAM_INT);
        $this->_form->addHelpButton('customint1', 'method', 'enrol_otautoenrol');

        // Подписывать даже если подписан
        $this->_form->addElement('selectyesno', 'customint8', get_string('alwaysenrol', 'enrol_otautoenrol'));
        $this->_form->setType('customint8', PARAM_INT);
        $this->_form->setDefault('customint8', 0);
        $this->_form->addHelpButton('customint8', 'alwaysenrol', 'enrol_otautoenrol');

        // Ограничение количества подписок
        $this->_form->addElement('text', 'customint5', get_string('countlimit', 'enrol_otautoenrol'));
        $this->_form->setType('customint5', PARAM_INT);
        $this->_form->setDefault('customint5', 0);
        $this->_form->addHelpButton('customint5', 'countlimit', 'enrol_otautoenrol');
    }

    /**
     * @throws coding_exception
     */
    protected function add_filtering_section($instance) {
        $this->_form->addElement('header', 'filtersection', get_string('filtering', 'enrol_otautoenrol'));
        $this->_form->setExpanded('filtersection');

        // Поля для фильтрации
        $fields = [get_string('g_none', 'enrol_otautoenrol')];
        $special_field_name = ['auth', 'lang'];
        $userfields = otautoenrol_get_userfields();
        if( ! empty($userfields) )
        {
            foreach($userfields as $userfield)
            {
                if( ! in_array($userfield, $special_field_name))
                {
                    $fields[$userfield] = get_user_field_name($userfield);
                } else
                {
                    $fields[$userfield] = get_string($userfield, 'enrol_otautoenrol');
                }
            }
        }
        $customfields = otautoenrol_get_customfields();
        if( ! empty($customfields) )
        {
            foreach($customfields as $customfield)
            {
                $fields['profile_field_' . $customfield->shortname] = $customfield->name;
            }
        }

        // условия подписки в курс, количество настраивается клиентом
        $repeatarray = [];

        // враппер для условия
        $repeatarray[] = $this->_form->createElement('html','<div class="fields_filter_setting">');

        // поле для проверки условия
        $repeatarray[] = $this->_form->createElement(
            'select',
            'conditionfield',
            get_string('groupon', 'enrol_otautoenrol'),
            $fields
        );
        // значение для сравнения с полем профиля
        $repeatarray[] = $this->_form->createElement(
            'text',
            'conditionfieldval',
            get_string('filter', 'enrol_otautoenrol')
        );
        // использовать ли строгое соответствие
        $repeatarray[] = $this->_form->createElement(
            'selectyesno',
            'conditionfieldsoftmatch',
            get_string('softmatch', 'enrol_otautoenrol')
        );
        // /враппер для условия
        $repeatarray[] = $this->_form->createElement('html','</div>');

        // количество сохраненных групп элементов условий
        if( ! empty($this->conditions) )
        {
            $repeatno = count($this->conditions);
        } else
        {// по умолчанию предоставляем одну группу элементов для заполнения
            $repeatno = 1;
        }

        // настройки полей
        $repeateloptions = [];
        $repeateloptions['conditionfield']['type'] = PARAM_TEXT;
        $repeateloptions['conditionfield']['helpbutton'] = ['groupon', 'enrol_otautoenrol'];
        $repeateloptions['conditionfieldval']['disabledif'] = ['conditionfield', 'eq', 0];
        $repeateloptions['conditionfieldval']['type'] = PARAM_TEXT;
        $repeateloptions['conditionfieldval']['helpbutton'] = ['filter', 'enrol_otautoenrol'];
        $repeateloptions['conditionfieldsoftmatch']['type'] = PARAM_BOOL;
        $repeateloptions['conditionfieldsoftmatch']['default'] = 0;
        $repeateloptions['conditionfieldsoftmatch']['disabledif'] = ['conditionfield', 'eq', 0];
        $repeateloptions['conditionfieldsoftmatch']['helpbutton'] = ['softmatch', 'enrol_otautoenrol'];

        // повторение элементов
        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            1,
            null,
            true
        );


        // Отписывать, если поле профиля не совпадает условию
        $this->_form->addElement('advcheckbox', 'customint7', get_string('unenrol_users', 'enrol_otautoenrol'));
        $this->_form->setType('customint7', PARAM_INT);
        $this->_form->setDefault('customint7', 1);



        // Заголовок Добавление в группы
        $this->_form->addElement('header', 'groupsection', get_string('groupping', 'enrol_otautoenrol'));
        $this->_form->setExpanded('groupsection');



        // Записывать в группы по значению в поле профиля (блокирует другие способы)
        $this->_form->addElement('checkbox', 'has_groups_field', get_string('has_groups_field', 'enrol_otautoenrol'));
        $this->_form->setType('has_groups_field', PARAM_INT);
        $this->_form->setDefault('has_groups_field', 0);
        $this->_form->addHelpButton('has_groups_field', 'has_groups_field', 'enrol_otautoenrol');

        // Выбор поля, в котором хранится группа
        $this->_form->addElement('select', 'groups_field', get_string('groups_field', 'enrol_otautoenrol'), $fields);
        $this->_form->hideIf('groups_field', 'has_groups_field', 'notchecked');
        $this->_form->addHelpButton('groups_field', 'groups_field', 'enrol_otautoenrol');

        // Создавать ли группу, найденную в поле профиля, если в курсе такой еще нет
        $this->_form->addElement('checkbox', 'groups_field_autocreate', get_string('groups_field_autocreate', 'enrol_otautoenrol'));
        $this->_form->setType('groups_field_autocreate', PARAM_INT);
        $this->_form->setDefault('groups_field_autocreate', 1);
        $this->_form->hideIf('groups_field_autocreate', 'has_groups_field', 'notchecked');
        $this->_form->hideIf('groups_field_autocreate', 'groups_field', 'eq', 0);

        // Внести правки в регулярное выражение
        $this->_form->addElement('checkbox', 'groups_field_edit_regex', get_string('groups_field_edit_regex', 'enrol_otautoenrol'));
        $this->_form->setType('groups_field_edit_regex', PARAM_INT);
        $this->_form->setDefault('groups_field_edit_regex', 0);
        $this->_form->hideIf('groups_field_edit_regex', 'has_groups_field', 'notchecked');
        $this->_form->hideIf('groups_field_edit_regex', 'groups_field', 'eq', 0);

        // Регулярка для вычленения группы и курса для каждой группы из поля профиля
        $this->_form->addElement('text', 'groups_field_regex', get_string('groups_field_regex', 'enrol_otautoenrol'), $fields);
        $this->_form->setType('groups_field_regex', PARAM_RAW);
        $this->_form->setDefault('groups_field_regex', '/(?<group_idnumber>[^,]*)@(?<course_shortname>[^,]*)/');
        $this->_form->hideIf('groups_field_regex', 'has_groups_field', 'notchecked');
        $this->_form->hideIf('groups_field_regex', 'groups_field', 'eq', 0);
        $this->_form->hideIf('groups_field_regex', 'groups_field_edit_regex', 'notchecked');
        $this->_form->addHelpButton('groups_field_regex', 'groups_field_regex', 'enrol_otautoenrol');





        // Добавлять в группу согласно фильтрации
        $this->_form->addElement('advcheckbox', 'customint2', get_string('addtogroup_by_profile_field_name', 'enrol_otautoenrol'));
        $this->_form->setType('customint2', PARAM_INT);
        $this->_form->setDefault('customint2', 1);
        $this->_form->hideIf('customint2', 'has_groups_field', 'checked');

        // Подписка в выбранные группы из существующих
        $groups = groups_get_all_groups($instance->courseid, 0, 0, 'g.id, g.name');
        if (!empty($groups)) {

            // Список групп
            $options = array_combine(array_column($groups, 'id'), array_column($groups, 'name'));
            $this->_form->addElement('select', 'customtext1', get_string('addtogroup', 'enrol_otautoenrol'), $options)->setMultiple(true);
            $this->_form->hideIf('customtext1', 'has_groups_field', 'checked');

            // Случайное распределение
            $this->_form->addElement('advcheckbox', 'customint6', get_string('random_distribution_by_groups', 'enrol_otautoenrol'));
            $this->_form->setType('customint6', PARAM_INT);
            $this->_form->setDefault('customint6', 0);
            $this->_form->hideIf('customint6', 'has_groups_field', 'checked');
        }
    }

    function data_preprocessing(&$defaultvalues)
    {
        global $DB;
        if ( ! empty($defaultvalues->customtext3) )
        {
            $conditions = json_decode($defaultvalues->customtext3);
            if( ! is_null($conditions) && is_array($conditions) )
            {
                $this->conditions = $conditions;
                foreach ($conditions as $conditionnum=>$condition)
                {
                    $fieldprop = 'conditionfield['.$conditionnum.']';
                    $fieldvalprop = 'conditionfieldval['.$conditionnum.']';
                    $fieldsoftmatchprop = 'conditionfieldsoftmatch['.$conditionnum.']';
                    $defaultvalues->$fieldprop = $condition->conditionfield;
                    $defaultvalues->$fieldvalprop = $condition->conditionfieldval;
                    $defaultvalues->$fieldsoftmatchprop = !empty($condition->conditionfieldsoftmatch);
                }
            }
        }

        if (!empty($defaultvalues->customtext4) )
        {
            $config = json_decode($defaultvalues->customtext4, true);
            if (array_key_exists('groups_field_config', $config)) {
                $groupsfieldcfg = $config['groups_field_config'];
                if (array_key_exists('field', $groupsfieldcfg)) {
                    // сохранено поле профиля, в котором требуется выполнять поиск

                    $defaultvalues->has_groups_field = true;
                    $defaultvalues->groups_field = $groupsfieldcfg['field'];

                    if (!empty($groupsfieldcfg['custom'])) {
                        // регулярка была переопределена при помощи настройки
                        // подставим значение переопределенной регулярки
                        $defaultvalues->groups_field_edit_regex = true;
                        $defaultvalues->groups_field_regex = $groupsfieldcfg['regex'];
                    }
                    $defaultvalues->groups_field_autocreate = !empty($groupsfieldcfg['autocreate']);
                }
            }
        }
    }

    /**
     *
     */
    protected function add_hidden_fields() {
        $this->_form->addElement('hidden', 'id');
        $this->_form->setType('id', PARAM_INT);
        $this->_form->addElement('hidden', 'courseid');
        $this->_form->setType('courseid', PARAM_INT);
    }

    /**
     * @type occurrence
     */
    protected $_customdata;
}
