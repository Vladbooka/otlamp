<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//
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
 * Обмен данных с внешними источниками. Класс файлового источника CSV-формата
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Подключение библиотеки работы с CSV
require_once($CFG->libdir . '/csvlib.class.php');

class dof_modlib_transmit_source_file_csv extends dof_modlib_transmit_source_file
{
    /**
     * Обработчик 
     * 
     * @var csv_import_reader
     */
    protected $csvreader = null;
    
    /**
     * Обработчик экспорта
     *
     * @var csv_export_writer
     */
    protected $csvwriter = null;
    
    /**
     * Максимальная длина строки (Требует интерфейс итератора)
     *
     * @var int
     */
    protected $row_length = 2048;
    
    /**
     * Текущая строка итератора
     *
     * @var int
     */
    protected $row_counter = 0;
    
    /**
     * Текущая строка итератора
     *
     * @var string
     */
    protected $current_element = null;
    
    /**
     * Поддержка импорта
     *
     * @return bool
     */
    public static function support_import()
    {
        return true;
    }
    
    /**
     * Поддержка экспорта
     *
     * @return bool
     */
    public static function support_export()
    {
        return true;
    }
    
    /** РЕАЛИЗАЦИЯ ИТЕРАТОРА **/
    
    /**
     * Iterator next()
     *
     * @return void
     */
    public function next()
    {
        $this->row_counter++;
        $this->current_element = $this->csvreader->next();
    }
    
    /**
     * Iterator valid()
     *
     * @return bool
     */
    public function valid()
    {
        if ( ! $this->current_element )
        {
            $this->csvreader->close();
            return false;
        }
        return true;
    }
    
    /**
     * Iterator current()
     *
     * @return array
     */
    public function current()
    {
        return $this->current_element;
    }
    
    /**
     * Iterator rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->row_counter = 0;
        $this->csvreader->init();
        $this->current_element = $this->csvreader->next();
    }
    
    /**
     * Iterator key()
     *
     * @return int
     */
    public function key()
    {
        return $this->row_counter;
    }
    
    /**
     * Получение итератора
     *
     * @return Iterator
     */
    public function get_dataiterator()
    {
        // Получение пути до файла
        $thansmitfilehash = $this->get_transmit_filepath();
        
        // Получение контента файла
        $this->filecontent = $this->dof->modlib('filestorage')->
            get_file_by_pathnamehash($thansmitfilehash)->get_content();
        
        // Загрузка ридера данных 
        $iid = csv_import_reader::get_new_iid($this->get_transmitfilearea());
        $this->csvreader = new csv_import_reader($iid, $this->get_transmitfilearea());
        $this->csvreader->load_csv_content(
            $this->filecontent, 
            $this->get_configitem('encoding'), 
            $this->get_configitem('delimiter')
        );
        
        // Поля файла
        $this->datafields = $this->csvreader->get_columns();
        
        return $this;
    }
    
    /** РАБОТА С КОНФИГУРАЦИЕЙ ИСТОЧНИКА **/
    
    /**
     * Получение конфигурации по умолчанию для текущего источника
     *
     * @return array
     */
    protected function config_defaults()
    {
        // Конфигурация для базового источника
        $configdata = parent::config_defaults();

        // Формат доступных для обмена файлов
        $configdata['fileformats'] = ['.csv'];
        
        // Разделитель в CSV файле
        $configdata['delimiter'] = ['semicolon'];
        
        return $configdata;
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Запуск процесса экспорта данных
     *
     * @return void
     */
    public function export_start_process()
    {
        // Объект csv экспортера
        $this->csvwriter = new csv_export_writer();
        
        // Разделитель
        $this->csvwriter->delimiter = $this->get_configitem('delimiter');
    }
    
    /**
     * Процесс экспорта данных одного элемента
     *
     * @param $fields - Поля экспорта
     * @param $data - Данные экспорта
     *
     * @return void
     */
    public function export(array $fields, array $data)
    {
        static $firstline = true;
        if ( $firstline )
        {// Добавление полей в файл с данными
            $firstline = false;
            // Объявление полей данных
            $this->csvwriter->add_data($fields);
        }
        // Добавление данных
        $this->csvwriter->add_data($data);
    }
    
    /**
     * Завершение процесса экспорта данных
     *
     * @return void
     */
    public function export_finish_process()
    {
        // Название файла
        $this->csvwriter->set_filename('export');
        
        // Скачивание csv файла
        $this->csvwriter->download_file();
    }
    
    
    /** РАБОТА С ФОРМАМИ НАСТРОЙКИ ОБМЕНА **/
    
    /**
     * Заполнить форму дополнительными настройками
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Базовое определение формы
        parent::configform_definition_import($form, $mform);
        
        // Разделитель данных в CSV-файле
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement(
            'select', 
            'delimiter',
            $this->dof->get_string('source_configform_delimiter_title', 'transmit', null, 'modlib'),
            $choices
        );
        $defaultvalue = $this->get_configitem('delimiter');
        $mform->setDefault('delimiter', $defaultvalue);
    }
    
    /**
     * Установка конфигурации источника данными из формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     *
     * @return void
     */
    public function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        // Базовая обработка формы
        parent::configform_setupconfig_import($form, $mform, $formdata);
        
        // Установка разделителя
        $this->set_configitem('delimiter', $formdata->delimiter);
    }
    
    /**
     * Заполнить форму дополнительными настройками
     *
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        // Базовое определение формы
        parent::configform_definition_export($form, $mform);
        
        // Разделитель данных в CSV-файле
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement(
            'select',
            'delimiter',
            $this->dof->get_string('source_configform_delimiter_title', 'transmit', null, 'modlib'),
            $choices
        );
        $defaultvalue = $this->get_configitem('delimiter');
        $mform->setDefault('delimiter', $defaultvalue);
    }
    
    /**
     * Установка конфигурации источника данными из формы
     *
     * @param dof_modlib_transmit_configurator_configform_base $form - Форма настройки
     * @param MoodleQuickForm $mform - Контроллер формы
     * @param stdClass $formdata - Данные формы
     *
     * @return void
     */
    public function configform_setupconfig_export(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        // Базовая обработка формы
        parent::configform_setupconfig_export($form, $mform, $formdata);
        
        // Установка разделителя
        $this->set_configitem('delimiter', $formdata->delimiter);
    }
}