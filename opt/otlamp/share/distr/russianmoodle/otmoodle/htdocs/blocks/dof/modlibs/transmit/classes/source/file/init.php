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
 * Обмен данных с внешними источниками. Базовый класс файловых источников данных
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlib_transmit_source_file extends dof_modlib_transmit_source_base implements Iterator
{
    /**
     * Контент файла
     *
     * @var string
     */
    protected $filecontent;

    /**
     * Первичная инициализация формы импорта данных
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function configform_definition_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform)
    {
        parent::configform_definition_import($form, $mform);
        
        // Подготовка файлового менеджера для файлов импорта
        $options = [
            'accepted_types' => $this->get_configitem('fileformats')
        ];
        // Получение свободного ITEMID в зоне ручного обмена данными
        $itemid = $this->dof->modlib('filestorage')->definion_filemanager(
            'file_filemanager', 
            $this->get_configitem('fileitemid'),
            $this->get_transmitfilearea()
        );
        $mform->addElement(
            'filemanager',
            'file',
            $this->dof->get_string('source_configform_file_title', 'transmit', null, 'modlib'),
            null,
            $options
        );
        $mform->setDefault('file', $itemid);
        $mform->addRule(
            'file', 
            $this->dof->get_string('source_configform_file_error_empty', 'transmit', null, 'modlib'), 
            'required', 
            null, 
            'server'
        );
        
        // Кодировка файла
        $choices = core_text::get_encodings();
        $mform->addElement(
            'select',
            'encoding',
            $this->dof->get_string('source_configform_encoding_title', 'transmit', null, 'modlib'),
            $choices
        );
        $mform->setDefault('encoding', $this->get_configitem('encoding'));
    }
    
    /**
     * Установка настроек источника на основе данных формы
     * 
     * @param dof_modlib_transmit_configurator_configform_base $form
     * @param MoodleQuickForm $mform
     * @param stdClass $formdata
     *
     * @return void
     */
    public function configform_setupconfig_import(dof_modlib_transmit_configurator_configform_base &$form, MoodleQuickForm &$mform, $formdata)
    {
        // Сохранение файлов ручного импорта
        $this->dof->modlib('filestorage')->process_filemanager(
            'file_filemanager',
            $formdata->file,
            $this->get_configitem('fileitemid'), 
            $this->get_transmitfilearea(),
            ['filemanageroptions' => ['accepted_types' => $this->get_configitem('fileformats')]]
        );
        
        // Установка кодировки файла
        $this->set_configitem('encoding', $formdata->encoding);
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Получить файловую зону для хранения файла обмена
     */
    protected function get_transmitfilearea()
    {
        return 'modlib_transmit_storage_'.$this->get_code();
    }
    
    /**
     * Получить местоположение файла обмена данными
     *
     * @return null|string
     */
    protected function get_transmit_filepath()
    {
        // Проверка наличия файла обмена
        $files = (array)$this->dof->modlib('filestorage')->get_pathnamehashes(
            $this->get_configitem('fileitemid'),
            $this->get_transmitfilearea()
        );
        // Поиск файла доступного формата
        $availableformats = $this->get_configitem('fileformats');
        foreach ( $files as $hash )
        {
            // Получение расширения файла, соответствующего его типу
            $file = $this->dof->modlib('filestorage')->get_file_by_pathnamehash($hash);
            $format = mimeinfo_from_type('extension', $file->get_mimetype());
            if ( array_search($format, $availableformats) !== false )
            {// Файл поддерживается источником
                return $hash;
            }
        }
        // Валидный файл обмена не найден
        return null;
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
        
        // ID блока в файловой зоне для хранения файла обмена
        $configdata['fileitemid'] = 0;
        
        // Кодировка файла
        $configdata['encoding'] = 'UTF-8';
        
        // Формат доступных для обмена файлов
        $configdata['fileformats'] = [];
        
        return $configdata;
    }
    
    /**
     * Сброс конфигурации источника
     *
     * @return array
     */
    public function config_reset()
    {
        // Удаление файлов обмена
        $this->dof->modlib('filestorage')->delete_files_area(
            $this->get_transmitfilearea(),
            $this->get_configitem('fileitemid')
        );
        parent::config_reset();
    }
}