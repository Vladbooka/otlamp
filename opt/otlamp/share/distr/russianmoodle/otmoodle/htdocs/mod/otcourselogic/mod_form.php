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
 * Модуль Логика курса. Класс формы создания элемента курса.
 *
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot. '/course/moodleform_mod.php');
require_once($CFG->libdir. '/filelib.php');

/**
 * Форма редактирования элемента модуля курса
 */
class mod_otcourselogic_mod_form extends moodleform_mod 
{
    /**
     * Объявление полей формы
     */
    public function definition() 
    {
        global $CFG, $DB;

        // Запись модуля курса
        $cm = $this->get_coursemodule();
        
        $mform = $this->_form;

        // Получение глобальных настроек плагина
        $globalsettings = get_config('mod_otcourselogic');
        
        // Получение преподавателей
        $coursecontacts = $this->get_course_contacts();
        
        // Блок отображения элемента курса
        $mform->addElement(
            'header',
            'header_activity_display',
            get_string('form_header_activity_display', 'mod_otcourselogic')
        );
        
        // Название элемента курса
        $mform->addElement(
            'text',
            'name',
            get_string('form_name_label', 'mod_otcourselogic')
        );
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->setDefault('name', get_string('modulename', 'mod_otcourselogic'));
        
        // Скрывать элемент курса от учеников
        $options = [
            0 => get_string('form_display_to_students_no', 'mod_otcourselogic'),
            1 => get_string('form_display_to_students_yes', 'mod_otcourselogic')
        ];
        $mform->addElement(
            'select',
            'display_to_students',
            get_string('form_display_to_students_label', 'mod_otcourselogic'),
            $options
        );
        $mform->setType('display_to_students', PARAM_INT);
        $mform->setDefault('display_to_students', 1);
        $mform->addHelpButton('display_to_students', 'form_display_to_students_help', 'mod_otcourselogic');
        
//         // Установка активности элемента в зависимости от доступности пользователю
//         $options = [
//             1 => get_string('form_active_state_available', 'mod_otcourselogic'),
//             0 => get_string('form_active_state_notavailable', 'mod_otcourselogic')
//         ];
//         $mform->addElement(
//                 'select',
//                 'active_state',
//                 get_string('form_active_state_label', 'mod_otcourselogic'),
//                 $options
//                 );
//         $mform->setType('active_state', PARAM_INT);
//         $mform->setDefault('active_state', 1);
//         $mform->addHelpButton('active_state', 'form_active_state_help', 'mod_otcourselogic');
        
//         // Период отсрочки активации логики курса
//         $mform->addElement('duration', 'activating_delay',
//                 get_string('form_activating_delay_label', 'mod_otcourselogic'),
//                 [
//                     'optional' => false,
//                     'defaultunit' => 86400
//                 ]
//                 );
//         $mform->setDefault('activating_delay', 0);
//         $mform->addHelpButton('activating_delay', 'form_activating_delay_help', 'mod_otcourselogic');
        
        // Статус ОД
        if ( empty($cm->availability) )
        {
            $availability_status = get_string('availability_empty', 'mod_otcourselogic');
            $style = 'padding: 5px;text-align: center;border: 1px solid #f00;color: #f00;';
            $mform->addElement('html', html_writer::div($availability_status, '', ['style' => $style]));
        }
            
        // Блок оценивания
        $mform->addElement(
            'header',
            'header_grading',
            get_string('form_header_grading', 'mod_otcourselogic')
        );
            // Опция для включения оценивания 
            $mform->addElement(
                'checkbox',
                'grading_enabled',
                get_string('form_grading_enabled', 'mod_otcourselogic')
            );
            
        // Стандартные блоки формы
        $this->standard_coursemodule_elements();
        
        // Блок состояния элемента курса
        $mform->addElement(
                'header',
                'header_activity_state',
                get_string('form_header_activity_state', 'mod_otcourselogic')
                );
        
        // Период регулярной проверки состояния элемента курса
        $options = [
            0 => get_string('form_check_period_every_start', 'mod_otcourselogic'),
            900 => get_string('form_check_period_every_15', 'mod_otcourselogic'),
            1800 => get_string('form_check_period_every_30', 'mod_otcourselogic'),
            3600 => get_string('form_check_period_every_60', 'mod_otcourselogic'),
            10800 => get_string('form_check_period_every_180', 'mod_otcourselogic'),
            86400 => get_string('form_check_period_every_1440', 'mod_otcourselogic'),
            -1 => get_string('form_check_period_never', 'mod_otcourselogic')
        ];
        $mform->addElement(
                'select',
                'check_period',
                get_string('form_check_period_label', 'mod_otcourselogic'),
                $options
                );
        $mform->setType('check_period', PARAM_INT);
        $mform->setDefault('check_period', 86400);
        $mform->addHelpButton('check_period', 'form_check_period_help', 'mod_otcourselogic');
        
        
        // Проверка при смене состояния другого элемента курса
        $options = [
            0 => get_string('form_check_event_state_switched_no', 'mod_otcourselogic'),
            1 => get_string('form_check_event_state_switched_yes', 'mod_otcourselogic')
        ];
        $mform->addElement(
                'select',
                'check_event_state_switched',
                get_string('form_check_event_state_switched_label', 'mod_otcourselogic'),
                $options
                );
        $mform->setType('check_event_state_switched', PARAM_INT);
        $mform->setDefault('check_event_state_switched', 0);
        $mform->addHelpButton('check_event_state_switched', 'form_check_event_state_switched_help', 'mod_otcourselogic');
        
        // Проверка состояния при входе пользователя в курс
        $options = [
            0 => get_string('form_check_event_course_viewed_no', 'mod_otcourselogic'),
            1 => get_string('form_check_event_course_viewed_yes', 'mod_otcourselogic')
        ];
        $mform->addElement(
                'select',
                'check_event_course_viewed',
                get_string('form_check_event_course_viewed_label', 'mod_otcourselogic'),
                $options
                );
        $mform->setType('check_event_course_viewed', PARAM_INT);
        $mform->setDefault('check_event_course_viewed', 0);
        $mform->addHelpButton('check_event_course_viewed', 'form_check_event_course_viewed_help', 'mod_otcourselogic');
        
        // Дополнительные настройки
        $mform->addElement(
                'header',
                'header_additional',
                get_string('form_header_additional', 'mod_otcourselogic')
                );

        // Сообщение при переходе по ссылке из уведомлений
        $mform->addElement(
                'editor',
                'delivery_redirect_message',
                get_string('form_delivery_redirect_message_label', 'mod_otcourselogic')
                );
        $mform->setType('delivery_redirect_message', PARAM_RAW_TRIMMED);
        $mform->setDefault('delivery_redirect_message', '');
        $mform->addHelpButton('delivery_redirect_message', 'form_delivery_redirect_message_help', 'mod_otcourselogic');
        
        // URL перехода по ссылке из уведомлений
        $mform->addElement(
                'text',
                'delivery_redirect_url',
                get_string('form_delivery_redirect_url_label', 'mod_otcourselogic')
                );
        $mform->setType('delivery_redirect_url', PARAM_URL);
        $mform->setDefault('delivery_redirect_url', '');
        $mform->addHelpButton('delivery_redirect_url', 'form_delivery_redirect_url_help', 'mod_otcourselogic');
        
        // Перемещение вкладки ограничения доступа
        $elem = $mform->getElement('availabilityconditionsheader');
        $el = $mform->getElement('availabilityconditionsjson');
        
        $mform->removeElement('availabilityconditionsheader');
        $mform->removeElement('availabilityconditionsjson');
        
        $mform->insertElementBefore($el, 'header_grading');
        $mform->insertElementBefore($elem, 'availabilityconditionsjson');
        
        $mform->setExpanded('availabilityconditionsheader', true);
        
        // Защита от случайных срабатываний (обработчики не сработают, если нет ограничений доступа)
        $protect = $mform->createElement('selectyesno', 'protect', get_string('form_protect_label', 'mod_otcourselogic'));
        $mform->insertElementBefore($protect, 'availabilityconditionsjson');
        $mform->setType('protect', PARAM_INT);
        $mform->setDefault('protect', 1);
        $mform->addHelpButton('protect', 'form_protect_label', 'mod_otcourselogic');
        
        $this->add_action_buttons();
    }
        
    /**
     * Предобработка
     */
    public function data_preprocessing(&$default_values) 
    {
        parent::data_preprocessing($default_values);

        if ( ! empty($this->current->instance) )
        {// Заполнение данными формы
            
//             // Условие активности элемента
//             switch ( $this->current->activecond )
//             {
//                 case 'active' :
//                     $default_values['active_state'] = 1;
//                     break;
//                 default :
//                     $default_values['active_state'] = 0;
//                     break;
//             }
            
//             // Период отсрочки активации
//             if ( $this->current->activatingdelay == null )
//             {// Не откладывать активацию
//                 $default_values['activating_delay'] = 0;
//             } else
//             {// Указан период
//                 $default_values['activating_delay'] = (int)$this->current->activatingdelay;
//             }
            
            // Защита от случайных срабатываний
            if ( ! empty($this->current->protect) )
            {
                $default_values['protect'] = 1;
            } else
            {
                $default_values['protect'] = 0;
            }
            
            // Период проверки состояния
            if ( $this->current->checkperiod == null )
            {// Не делать периодических проверок
                $default_values['check_period'] = -1;
            } else
            {// Указан период
                $default_values['check_period'] = (int)$this->current->checkperiod;
            }
            
            // Дополнительные условия проверки
            $default_values['check_event_state_switched'] = (int)$this->current->catchstatechange;
            $default_values['check_event_course_viewed'] = (int)$this->current->catchcourseviewed;
            
            // Название
            $default_values['name'] = $this->current->name;
            
            // Отображение элемента курса студентам
            $default_values['display_to_students'] = (int)$this->current->studentshide;
            
            // Сообщение при редиректе из уведомлений
            $default_values['delivery_redirect_message']['text'] = $this->current->redirectmessage;
            
            // URL редиректа из уведомлений
            $default_values['delivery_redirect_url'] = $this->current->redirecturl;
            
            $default_values['completionstate'] = $this->current->completionstate;
            if ( $this->current->completionstate === null )
            {
                $default_values['completionstateenabled'] = 0;
            } else
            {
                $default_values['completionstateenabled'] = 1;
            }
            
            // Оценивание
            if ( ! empty($this->current->grading) )
            {// Оценивание включено
                $default_values['grading_enabled'] = true;
            } else
            {// Оценивание выключено
                $default_values['grading_enabled'] = false;
            }
        }
    }
    
    /**
     * Добавление уникальных условий выполнения модуля
     * 
     * @return array - Массив кодов условий выполнения
     */
    public function add_completion_rules() 
    {
        $mform = $this->_form;
        
        // Новые условия выполнения
        $items = [];

        // Выполнение в зависимости от состояния элемнта курса
        $group = [];
        $options = [
            0 => get_string('form_completionstate_active', 'mod_otcourselogic'),
            1 => get_string('form_completionstate_notactive', 'mod_otcourselogic'),
        ];
        $group[] = $mform->createElement(
            'select', 
            'completionstate', 
            get_string('form_completionstate_label', 'mod_otcourselogic'),
            $options
        );
        $mform->setType('completionstate', PARAM_INT);
        $group[] = $mform->createElement(
            'checkbox',
            'completionstateenabled',
            '',
            get_string('form_completionstate_enabled_label', 'mod_otcourselogic')
        );
        $mform->addGroup(
            $group, 
            'completionstategroup', 
            get_string('form_completionstategroup_label', 'mod_otcourselogic'), 
            [' '], 
            false
        );
        $mform->disabledIf('completionstate', 'completionstateenabled', 'notchecked');
        $items[] = 'completionstategroup';
        
        return $items;
    }
    
    /**
     * Проверка активности условий выполнения
     *
     * @param array $data - Данные формы
     * 
     * @return bool 
     */
    function completion_rule_enabled($data) 
    {
        if ( isset($data['completionstateenabled']) )
        {
            return ( ! empty($data['completionstateenabled']) );
        }
        return false;
    }
    
    function get_data() 
    {
        $data = parent::get_data();
        if ( $data) 
        {
            if ( ! empty($data->completionunlocked) ) 
            {// Условия выполнения не заблокированны
                $autocompletion = ! empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
                if (  empty($data->completionstateenabled) || ! $autocompletion ) 
                {
                    $data->completionstate = null;
                }
            }
        }
        return $data;
    }
    
    protected function get_course_contacts()
    {
        $contacts = [];
        
        // Получение курса
        $course = $this->get_course();
        $course = new \core_course_list_element($course);

        if ( $course->has_course_contacts() )
        {// Есть контакты курса
            $users = $course->get_course_contacts();
            
            // Заполнение информации о контактах курса
            foreach ( $users as $userid => $userinfo )
            {
                $contacts[$userid] = "{$userinfo['username']}({$userinfo['rolename']})";
            }
            
        }
        
        return $contacts;
    }
    
    public function get_course()
    {
        if ( method_exists('moodleform_mod', 'get_course') )
        {// Метод найден
            // Вызов родительского метода
            return parent::get_course();
        } else 
        {// Получить курс
            return $this->courseformat->get_course();
        }
    }
    
}