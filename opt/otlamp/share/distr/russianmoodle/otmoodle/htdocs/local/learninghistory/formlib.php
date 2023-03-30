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
 * История обучения. Класс формы дополнительных настроек курса
 *
 * Для добавления новых свойств курса необходимо:
 * - Объявить поле в методе definition
 * - Если поле сложное(значение поля нельзя сразу записать в БД),
 * то необходимо добавить логику сохранения поля и заполнения значения по умолчанию
 * - Если поле простое, то необходмо добавить его низвание в массив $configs обработчика
 * формы.
 * Сохраниение и заполнение поля в форме установленным значением произойдет автоматически.
 *
 * @package    local_learninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
// Подключим библиотеки
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/local/learninghistory/lib.php');

class activetime_settings_form extends moodleform {

    // Свойства класса
    protected $course;
    protected $returnto;

    /**
     * Объявление формы
     */
    function definition() {
        global $CFG, $PAGE;

        // Получим данные
        $mform    = $this->_form;
        $course   = $this->_customdata['course'];
        $returnto = $this->_customdata['returnto'];
        // Свойства класса
        $this->course  = $course;
        $this->returnto = $returnto;

        // Заголовок формы
        $mform->addElement('header','coursesettings', get_string('activetime_settings_title', 'local_learninghistory'));
        // Скрытые поля
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        

        if( has_capability('local/learninghistory:activetimemanage', context_course::instance($this->course->id)) )
        {
            $yesno = [
                0 => get_string('no'),
                1 => get_string('yes')
            ];
            // Отображать категорию курса
            $mform->addElement(
                'select',
                'activetime',
                get_string('activetime_enable', 'local_learninghistory'),
                $yesno
            );
            $mform->addHelpButton('activetime', 'activetime_enable', 'local_learninghistory');
            
            $modes = [
                0 => get_string('mainlogs_enable', 'local_learninghistory'),
                1 => get_string('additionallogs_enable', 'local_learninghistory')
            ];
            $mform->addElement(
                'select',
                'mode',
                get_string('mode', 'local_learninghistory'),
                $modes
            );
            $mform->addHelpButton('mode', 'mode', 'local_learninghistory');
            
            $mform->disabledIf('mode', 'activetime', 0);
            
            $mform->addElement('duration', 'delay', get_string('delay', 'local_learninghistory'), [
                'optional' => false,
                'defaultunit' => 60
            ]);
            $mform->addHelpButton('delay', 'delay', 'local_learninghistory');
            $mform->disabledIf('delay', 'mode', 0);
            $mform->disabledIf('delay', 'activetime', 0);
            $mform->setDefault('delay', 60);
            
            $mform->addElement('duration', 'delaybetweenlogs', get_string('delaybetweenlogs', 'local_learninghistory'), [
                'optional' => false,
                'defaultunit' => 60
            ]);
            $mform->addHelpButton('delaybetweenlogs', 'delaybetweenlogs', 'local_learninghistory');
            $mform->disabledIf('delaybetweenlogs', 'mode', 0);
            $mform->disabledIf('delaybetweenlogs', 'activetime', 0);
            $currentdelay = local_learninghistory_get_course_config($this->course->id, 'delay');
            if ($currentdelay) {
                $mform->setDefault('delaybetweenlogs', (int)$currentdelay + 10);
            } else {
                $mform->setDefault('delaybetweenlogs', 70);
            }
            
            $mform->addElement('advcheckbox', 'timer', get_string('timer_enable', 'local_learninghistory'));
            $mform->addHelpButton('timer', 'timer', 'local_learninghistory');
            $mform->disabledIf('timer', 'activetime', 0);
            
            $mform->addElement('duration', 'available_time', get_string('available_time', 'local_learninghistory'), [
                'optional' => false,
                'defaultunit' => 3600
            ]);
            $mform->disabledIf('available_time', 'timer', 0);
            $mform->disabledIf('available_time', 'activetime', 0);
            $mform->addHelpButton('available_time', 'available_time', 'local_learninghistory');
            
            $mform->addElement('duration', 'timer_refresh', get_string('timer_refresh', 'local_learninghistory'), [
                'optional' => false,
                'defaultunit' => 60
            ]);
            $mform->disabledIf('timer_refresh', 'timer', 0);
            $mform->disabledIf('timer_refresh', 'activetime', 0);
            $mform->addHelpButton('timer_refresh', 'timer_refresh', 'local_learninghistory');
            $mform->setDefault('timer_refresh', 900);
            
            $selectregions = [
                'side-pre' => get_string('side-pre', 'local_learninghistory'), 
                'side-post' => get_string('side-post', 'local_learninghistory')
            ];
            $mform->addElement('select', 'region', get_string('region', 'local_learninghistory'), $selectregions);
            $mform->disabledIf('region', 'timer', 0);
            $mform->disabledIf('region', 'activetime', 0);
            $mform->addHelpButton('region', 'region', 'local_learninghistory');            
        } else 
        {
            $mform->addElement('static', 'nopermission', '', get_string('nopermission', 'local_learninghistory'));
        }

        // Кнопка сохранения
        $this->add_action_buttons();

        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');

        // Установим данные формы
        $this->set_data($course);
    }

    /**
     * Значения по умолчанию для формы
     */
    function set_data($default_values) {

        global $DB, $CFG;

        if( is_object($default_values) )
        {// Конвертируем в массив данные из формы
            $default_values = (array)$default_values;
        }

        // Получить все свойства курса
        $coursedata = $DB->get_records(
            'llhistory_properties',
            ['courseid' => $this->course->id]
        );

        foreach($coursedata as $config)
        {
            // Каждое полученное свойство добавляем в автозаполнение
            $default_values[$config->name] = $config->value;
        }

        // Заполняем форму данными
        parent::set_data($default_values);
    }
    
    /**
     * Валидация формы
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if( $data['activetime'] )
        {
            if( $data['mode'] == 1 && $data['delay'] < 20 )
            {
                $errors['delay'] = get_string('not_valid_delay', 'local_learninghistory');
            }
            if( $data['timer'] == 1 && $data['timer_refresh'] <= 0 )
            {
                $errors['timer_refresh'] = get_string('not_valid_timer_refresh', 'local_learninghistory');
            }
            if ($data['delaybetweenlogs'] < $data['delay']) {
                $errors['delaybetweenlogs'] = get_string('not_valid_delaybetweenlogs', 'local_learninghistory');
            }
        }
        return $errors;
    }

    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB, $CFG;

        if( $this->is_cancelled() )
        {// Отменили форму
            switch( $this->returnto )
            {// Куда вернуть пользователя
                default: // Вернем на страницу плагина
                    $url = new moodle_url(
                        $CFG->wwwroot.'/course/view.php',
                        ['id' => $this->course->id]
                    );
                    break;
            }
            redirect($url);
        }

        if( $this->is_submitted() AND confirm_sesskey() AND
            $this->is_validated() AND $formdata = $this->get_data()
            )
        {// Форма отправлена и проверена
            // Конвертируем в массив объект формы
            if ( is_object($formdata) )
            {
                $formdata = (array)$formdata;
            }

            // ПРОСТЫЕ СВОЙСТВА
            // Массив свойств для сохранения без дополнительной обработки
            $configs = [
                'activetime',
                'mode',
                'delay',
                'timer',
                'available_time',
                'timer_refresh',
                'region'
            ];
            // Cохранение свойств
            $result = $this->save_config($formdata, $configs);

            $url = new moodle_url(
                $CFG->wwwroot.'/course/view.php',
                ['id' => $this->course->id]
            );
            redirect($url);
        }
    }

    /**
     * Сохранить простые опции для курса
     *
     * Метод для сохранения нетекстовых опций, состоящих из одной записи в БД
     *
     * @param array $formdata - данные формы
     * @param array $configs - массив имен свойств
     *
     * @return bool - результат сохранения
     */
    private function save_config($formdata, $configs)
    {
        global $DB;

        // Получим все свойства текущего курса
        $courseconfigs = $DB->get_records_menu(
            'llhistory_properties',
            ['courseid' => $this->course->id],
            '',
            'id, name'
            );
        $result = true;
        // Сохраним свойства
        foreach($configs as $config)
        {
            if( isset($formdata[$config]) )
            {// Если текущее свойство имеется среди данных формы
                // Ищем конфиг
                $key = array_search($config, $courseconfigs);
                if( empty($key) )
                {// Свойство новое - добавим его
                    $configobj = new stdClass;
                    $configobj->name = $config;
                    $configobj->courseid = $this->course->id;
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->insert_record('llhistory_properties', $configobj) );
                } else
                {// Если свойство уже определено - обновим запись
                    $configobj = new stdClass;
                    $configobj->id = $key;
                    $configobj->name = $config;
                    $configobj->courseid = $this->course->id;
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->update_record('llhistory_properties', $configobj) );
                }
            }
        }
        // Результат сохранения
        return $result;
    }
}

class activetime_refresh_form extends moodleform
{
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'description', '', get_string('activetime_refresh_form_description', 'local_learninghistory'));
        $this->add_action_buttons(false, get_string('activetime_refresh_form_add_task', 'local_learninghistory'));
    }
    
    /**
     * Добавляет одноразовую задачу на пересчет времени непрерывного изучения курса
     *
     * @return boolean
     */
    public function process()
    {
        if( $this->get_data() )
        {
            // Let's set up the adhoc task.
            $task = new \local_learninghistory\task\activetime_refresh();
            // Queue it.
            return \core\task\manager::queue_adhoc_task($task);
        }
        return false;
    }
}