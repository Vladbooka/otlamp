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
 * Обмен данных с внешними источниками. Класс источника типа zip
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_transmit_source_file_zipcsv extends dof_modlib_transmit_source_file_csv
{
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
        return false;
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
        $configdata['fileformats'] = ['.zip'];
        
        return $configdata;
    }
    
    /** РАБОТА С ДАННЫМИ ДЛЯ ОБМЕНА **/
    
    /**
     * Получить местоположение файла обмена данными
     *
     * @return null|string
     */
    protected function get_transmit_filepath()
    {
        // Получение архива
        $archive = parent::get_transmit_filepath();
        if ( $archive == null )
        {// Архив не найден
            return null;
        }
        
        // Очистка зоны экспорта
        $this->dof->modlib('filestorage')->delete_files_area(
            $this->get_transmitfilearea().'_unzip',
            $this->get_configitem('fileitemid')
        );
        
        // Распаковка архива
        $files = $this->dof->modlib('filestorage')->unpack_zip(
            $archive,
            $this->get_transmitfilearea().'_unzip',
            $this->get_configitem('fileitemid')
        );
        
        // Проверка, что в архиве присутствует файл CSV
        $availableformats = parent::config_defaults()['fileformats'];
        foreach ( $files as $hash => $file )
        {
            $format = mimeinfo_from_type('extension', $file->get_mimetype());
            if ( array_search($format, $availableformats) !== false )
            {// Файл поддерживается источником
                return $hash;
            }
        }
        // Удаление распакованных файлов
        $this->dof->modlib('filestorage')->delete_files_area(
            $this->get_transmitfilearea().'_unzip',
            $this->get_configitem('fileitemid')
        );
        return null;
    }
    
    /**
     * Проверка доступности файла
     *
     * @param string $fileidentifier - Индентификатор файла
     *
     * @return bool
     */
    public function file_exists($fileidentifier)
    {
        // Поиск файла в архиве
        $file = $this->dof->modlib('filestorage')->get_file_by_path(
            (string)$fileidentifier, 
            $this->get_configitem('fileitemid'), 
            $this->get_transmitfilearea().'_unzip'
        );
        if ( $file )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Копирование файла из внешенго источника в указанную зону
     *
     * @param string $fileidentifier - Индентификатор файла в источнике
     * @param string $filearea - Файловая зона, в которую требуется скопировать файл
     * @param string $filepath - Путь до файла внутри зоны
     * @param number $itemid - Идентификатор подзоны
     *
     * @return stored_file|null - Скопированный файл или null
     */
    public function file_copy($fileidentifier, $filearea, $filepath, $itemid = 0)
    {
        // Поиск файла в разархивированной зоне
        $file = $this->dof->modlib('filestorage')->get_file_by_path(
            (string)$fileidentifier,
            $this->get_configitem('fileitemid'),
            $this->get_transmitfilearea().'_unzip'
        );
        if ( $file )
        {// Файл найден
            
            // Копирование файла
            $newfile = $this->dof->modlib('filestorage')->copy_file(
                $file->get_pathnamehash(),
                $filearea,
                $filepath,
                $itemid
            );
            if ( $newfile )
            {
                return $newfile;
            }
        }
        return null;
    }
    
    /**
     * Сброс конфигурации источника
     *
     * @return array
     */
    public function config_reset()
    {
        // Удаление распакованных файлов обмена
        $this->dof->modlib('filestorage')->delete_files_area(
            $this->get_configitem('fileitemid'),
            $this->get_transmitfilearea().'_unzip'
        );
        parent::config_reset();
    }
}