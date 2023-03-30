<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Интерфейс логов. Классы форм
 *
 * @package    im
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотеки форм
GLOBAL $DOF;
$DOF->modlib('widgets')->webform();

class dof_im_logs_report extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Логгер лога
     * 
     * @var dof_storage_logs_queuetype_base
     */
    protected $logger;
    
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;

        if ( $logger = $this->dof->storage('logs')->init_from_id($this->_customdata->id) )
        {// Установим логгер
            $this->logger = $logger;
            
            // Заголовок формы
            $header[] = $mform->createElement(
                    'header',
                    'form_header_elem',
                    $this->dof->get_string('download_report','logs')
                    );
            $mform->addGroup($header, 'form_header', '', [' '], false);
    
            // Параметры выгрузки полей
            $actions = [
                1 => $this->dof->get_string('yes', 'logs'),
                0 => $this->dof->get_string('no', 'logs')
            ];
            
            // Выгрузка поля "Данные"
            $mform->addElement(
                    'select',
                    'field_data',
                    $this->dof->get_string('pick_data', 'logs'),
                    $actions
                    );
            
            // Выгрузка поля "Сущность"
            $mform->addElement(
                    'select',
                    'field_storage',
                    $this->dof->get_string('pick_storage', 'logs'),
                    $actions
                    );
            
            // Выгрузка поля "Идентификатор"
            $mform->addElement(
                    'select',
                    'field_id',
                    $this->dof->get_string('pick_id', 'logs'),
                    $actions
                    );
            
            // Выгрузка поля "Действие"
            $action_fields = [];
            $action_fields[] = $mform->createElement(
                    'select',
                    'field_action',
                    $this->dof->get_string('pick_action', 'logs'),
                    $actions
                    );
            // Формирование локализованных строк
            $action_strings = [];
            foreach ( $this->logger->get_available_actions() as $act )
            {
                $action_strings[$act] = $this->dof->get_string($act, 'logs');
            }
            $action_fields[] = $mform->createElement(
                    'select',
                    'field_action_option',
                    '',
                    array_merge(['all' => $this->dof->get_string('all', 'logs')], $action_strings)
                    );
            $mform->addGroup(
                    $action_fields,
                    'group_field_action',
                    $this->dof->get_string('pick_action', 'logs'),
                    [' ']
                    );
            
            // Выгрузка поля "Статус"
            $status_fields = [];
            $status_fields[] = $mform->createElement(
                    'select',
                    'field_status',
                    $this->dof->get_string('pick_status', 'logs'),
                    $actions
                    );
            // Формирование локализованных строк
            $status_strings = [];
            foreach ( $this->logger->get_available_statuses() as $act )
            {
                $status_strings[$act] = $this->dof->get_string($act, 'logs');
            }
            $status_fields[] = $mform->createElement(
                    'select',
                    'field_status_option',
                    '',
                    array_merge(['all' => $this->dof->get_string('all', 'logs')], $status_strings)
                    );
            $mform->addGroup(
                    $status_fields,
                    'group_field_status',
                    $this->dof->get_string('pick_status', 'logs'),
                    [' ']
                    );
            
            // Выгрузка поля "Комментарий"
            $mform->addElement(
                    'select',
                    'field_comment',
                    $this->dof->get_string('pick_comment', 'logs'),
                    $actions
                    );
            
            // Кнопка подтвержедния
            $buttonarray = [];
            $buttonarray[] = $mform->createElement(
                    'select',
                    'export_format',
                    '',
                    [
                        'html' => 'HTML',
                        'xls' => 'EXCEL',
                        'pdf' => 'PDF',
                    ]
                    );
            $buttonarray[] = $mform->createElement(
                    'submit',
                    'form_submit',
                    $this->dof->get_string('report_submit', 'logs')
                    );
            $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        }
    }
    
    /**
     * Заполнение формы данными
     */
    public function definition_after_data()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( ! $this->is_submitted() )
        {
            // Заголовок формы
            $header[] = $mform->createElement(
                    'header',
                    'form_report',
                    $this->dof->get_string('report','logs')
                    );
            $mform->addGroup($header, 'form_report_header', '', [' '], false);
            
            // Запросим html отчет у интерфейса логов
            $mform->addElement('html', $this->dof->im('logs')->get_logreport($this->logger->get_id(), 'html'));
        }
    }
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() &&
                confirm_sesskey() &&
                $this->is_validated() &&
                $formdata = $this->get_data()
                )
        {// Сохранение данных
            // Поле "Данные"
            if ( $formdata->field_data )
            {
                $data = true;
            } else
            {
                $data = false;
            }
            // Поле "Сущность"
            if ( $formdata->field_storage )
            {
                $storage = true;
            } else
            {
                $storage = false;
            }
            // Поле "Идентификатор"
            if ( $formdata->field_id )
            {
                $id = true;
            } else
            {
                $id = false;
            }
            // Поле "Действие"
            if ( $formdata->group_field_action['field_action'] )
            {
                $action = $formdata->group_field_action['field_action_option'];
            } else
            {
                $action = false;
            }
            // Поле "Статус"
            if ( $formdata->group_field_status['field_status'] )
            {
                $status = $formdata->group_field_status['field_status_option'];
            } else
            {
                $status = false;
            }
            // Поле "Комментарий"
            if ( $formdata->field_comment )
            {
                $comment = true;
            } else
            {
                $comment = false;
            }
            
            if ( $formdata->export_format == 'html' )
            {// Отображение html
                // Заголовок формы
                $header[] = $mform->createElement(
                        'header',
                        'form_report',
                        $this->dof->get_string('report','logs')
                        );
                $mform->addGroup($header, 'form_report_header', '', [' '], false);
                
                // Запросим html отчет у интерфейса логов
                $mform->addElement('html', $this->dof->im('logs')->get_logreport(
                        $this->logger->get_id(), 
                        $formdata->export_format,
                        $data,
                        $storage,
                        $id,
                        $action,
                        $status,
                        $comment
                        ));
            } else 
            {// Прямое скачивание файла
                $this->dof->im('logs')->get_logreport(
                        $this->logger->get_id(),
                        $formdata->export_format,
                        $data,
                        $storage,
                        $id,
                        $action,
                        $status,
                        $comment
                        );
            }
        }
    }
}