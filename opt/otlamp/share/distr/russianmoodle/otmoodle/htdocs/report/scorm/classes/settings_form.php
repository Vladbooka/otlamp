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
 * Отчет по результатам SCORM. Форма локальных настроек плагина.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_scorm;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

use moodleform; 
use moodle_url;
use stdClass;

class settings_form extends moodleform 
{
    /**
     * ID модуля курса
     * 
     * @var int
     */
    private $cmid;
    
    /**
     * Объект менеджера модулей SCORM
     * 
     * @var cmmanager
     */
    private $cmmanager;
    
    /**
     * Объявление полей формы
     */
    public function definition() 
    {
        // Базовая инициализация
        $mform = &$this->_form;
        $this->cmid = $this->_customdata->cmid;
        $this->cmmanager = new cmmanager();
        
        // Получить опции оцениваемых элементов SCORM-пакета
        $gradeelements = $this->cmmanager->get_gradeelements_data($this->cmid);
        // Получить процент выполнения
        $passpercent = $this->cmmanager->get_passpercent($this->cmid);

        // Число полей для оцениваемых элементов в форме
        $this->gradeelementscount = count($gradeelements) + 1;
        if ( ! empty($this->_customdata->countrows) && 
             $this->_customdata->countrows > $this->gradeelementscount )
        {// Число полей переопределено
            $this->gradeelementscount = $this->_customdata->countrows;
        }
        // Нормализация числа полей в форме
        if ( $this->gradeelementscount - count($gradeelements) > 10 )
        {
            $this->gradeelementscount = count($gradeelements) + 10;
        }
        
        // Заголовок
        $mform->addElement(
            'header', 
            'settings_form_header', 
            get_string('settings_form_header', 'report_scorm')
        );
        
        // Скрытые поля
        $mform->addElement('hidden', 'countrows', $this->gradeelementscount);
        $mform->setType('countrows', PARAM_INT);
        
        // Описание
        $mform->addElement(
            'html', 
            get_string('settings_form_description', 'report_scorm')
        );
        
        // Процент выполнения SCORM для прохождения
        $defaultpersent = (float)get_config('report_scorm', 'passpercent');
        $stringvar = new stdClass();
        $stringvar->defaultpersent = round($defaultpersent, 2);
        $persent = $mform->createElement(
            'text',
            'settings_form_passpercent',
            get_string('settings_form_passpercent', 'report_scorm'),
            [
                'placeholder' => get_string('settings_form_passpercent_placeholder', 'report_scorm', $stringvar),
                'size' => '3'
            ]
        );
        $mform->setType('settings_form_passpercent', PARAM_RAW_TRIMMED);
        if ( $passpercent === null )
        {// Установка глобального значения в качестве параметра
            $mform->setDefault('settings_form_passpercent', round($defaultpersent, 2));
        } else
        {// Установка значения из конфигурации отчета
            $mform->setDefault('settings_form_passpercent', round($passpercent, 2));
        }
        $postfix = $mform->createElement(
            'static',
            'settings_form_passpercent_postfix',
            '',
            get_string('settings_form_passpercent_postfix', 'report_scorm')
        );
        $mform->addGroup(
            [$persent, $postfix], 
            'settings_form_passpercent_group', 
            get_string('settings_form_passpercent', 'report_scorm'), 
            ' ',
            false
        );
        
        // Описание таблицы оцениваемых элементов SCORM
        $mform->addElement(
            'html',
            get_string('settings_form_gradeelements_description', 'report_scorm')
        );

        for ( $counter = 0; $counter < $this->gradeelementscount; $counter++ )
        {
            // Получить данные для строки
            (array)$data = array_shift($gradeelements);
            
            // Нормализация данных
            if ( ! isset($data['weight']) ) 
            {// Вес не указан
                $data['weight'] = '';   
            } else 
            {
                $data['weight'] = floatval($data['weight']);
            }
            if ( empty($data['identifier']) )
            {// Идентификатор не указан
                $data['identifier'] = '';
            }
            
            // Добавление полей
            $group = [];
            
            $groupname = 'settings_form_gradeelement_'.$counter;
            // Идентификатор
            $group[] = $mform->createElement(
                'text',
                'id',
                get_string('settings_form_gradeelement_id', 'report_scorm'),
                ['placeholder' => get_string('settings_form_gradeelement_id', 'report_scorm')]
            );         
            $mform->setDefault($groupname.'[id]', $data['identifier']);
            $mform->setType($groupname.'[id]', PARAM_RAW_TRIMMED);
            
            // Вес вопроса
            $group[] = $mform->createElement(
                'text',
                'weight',
                get_string('settings_form_gradeelement_weight', 'report_scorm'),
                ['placeholder' => get_string('settings_form_gradeelement_weight', 'report_scorm')]
            );
            $mform->setDefault($groupname.'[weight]', $data['weight']);
            $mform->setType($groupname.'[weight]', PARAM_RAW_TRIMMED);
            
            // Тип оценивания вопроса
            $group[] = $mform->createElement(
                'select',
                'gradetype',
                '',
                [
                    1 => get_string('settings_form_gradeelement_gradetype_view', 'report_scorm'), 
                    2 => get_string('settings_form_gradeelement_gradetype_correct_answer', 'report_scorm')
                ]
            );
            if ( ! isset($data['gradetype']) || empty($data['gradetype']) )
            {
                $mform->setDefault($groupname.'[gradetype]', 2);
            } else 
            {
                $mform->setDefault($groupname.'[gradetype]', $data['gradetype']);
            }
            $mform->addGroup($group, $groupname);
        }
        
        // Скрытая submit-кнопка для перехвата нажатия Enter на клавиатуре
        $mform->addElement(
            'submit',
            'settings_form_gradeelement_hidden',
            '',
            ['style' => 'display: none']
        );
        
        // Кнопка добавления строк
        $mform->addElement(
            'submit', 
            'settings_form_gradeelement_addrow', 
            get_string('settings_form_gradeelement_addrow', 'report_scorm')
        );
        
        // Действия
        $buttonarray = [];
        $buttonarray[] = $mform->createElement(
            'submit', 
            '', 
            get_string('settings_form_gradeelement_submit', 'report_scorm')
        );
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
    
    /**
     * Процесс обработки формы
     */
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Форма отправлена и проверена
            
            // Сохранение процента прохождения
            if ( $formdata->settings_form_passpercent === '' )
            {// Значение по умолчанию
                $defaultpersent = (float)get_config('report_scorm', 'passpercent');
                $this->cmmanager->set_passpercent($this->cmid, $defaultpersent);
            } else 
            {//Указанное в форме значение
                $this->cmmanager->set_passpercent($this->cmid, $formdata->settings_form_passpercent);
            }
            
            // Формирование данных для сохранения
            $data = [];
            for ( $counter = 0; $counter < $formdata->countrows; $counter++ )
            {
                $groupname = 'settings_form_gradeelement_'.$counter;
                
                if ( isset($formdata->$groupname) )
                {
                    $node = $formdata->$groupname;
                    if ( ! empty($node['id']) )
                    {// Идентификатор найден
                        $data[$node['id']] = [];
                        if ( ! empty($node['weight']) )
                        {// Вес найден
                            $data[$node['id']]['weight'] = $node['weight'];
                        } else
                        {// Вес 0
                            $data[$node['id']]['weight'] = 0;
                        }
                        // Сохраняем тип оценивания
                        $data[$node['id']]['gradetype'] = $node['gradetype'];
                    }
                }
            }

            $this->cmmanager->set_gradeelements_data($this->cmid, $data);
            
            if ( isset($formdata->settings_form_gradeelement_addrow) )
            {// Задача на добавление строки
                $url = new moodle_url('/report/scorm/cmsettings.php', 
                    [
                        'cmid' => $this->cmid, 
                        'countrows' => $formdata->countrows + 1
                    ]);
                redirect($url);
            } else 
            {
                $url = new moodle_url('/report/scorm/cmsettings.php',
                    [
                        'cmid' => $this->cmid
                    ]);
                redirect($url);
            }
        }
    }
}
