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
 * Обмен данных с внешними источниками. Класс работы с дополнительными файлами источника.
 * 
 * В обмене данных могут участвовать поля, содержащие в себе данные 
 * о хранимых файлах(например - импорт\экспорт изображений профиля персон).
 * Данный класс является точкой для работы с такими файлами. Он содержит временную зону,
 * в которую перемещабтся файлы для последующего импорта\экспорта.
 *
 * @package    modlib
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_transmit_source_filemanager
{
    /**
     * Контроллер ЭД
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Массив полей
     *
     * @var array
     */
    protected $source = null;
    
    /**
     * Файловая зона 
     * 
     * @var string
     */
    private static $draftfilearea = 'transmit_source_filemanager_draft';
    
    /**
     * Крон
     *
     * @return void
     */
    public static function cron()
    {
        // Удаление временных файлов прошлого дня
        global $DOF;
        $DOF->modlib('filestorage')->delete_files_area_by_lifetime(
            DAYSECS, self::$draftfilearea);
    }
    
    /**
     * Преобразование класса в строковый тип
     * 
     * @return string
     */
    public function __toString() 
    {
        return '';    
    }
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - Контроллер Деканата
     * @param dof_modlib_transmit_source_base $source - Источник
     *
     * @return void
     */
    public function __construct(dof_control $dof, dof_modlib_transmit_source_base $source)
    {
        $this->dof = $dof;
        $this->source = $source;
    }
    
    /**
     * Проверка доступности файла
     * 
     * Источник пытается найти файл с использованием указанного идентификатора
     * 
     * @param string $fileidentifier - Индентификатор файла в источнике
     * 
     * @return bool
     */
    public function file_exists($fileidentifier)
    {
        // Запрос файла у источника
        return $this->source->file_exists($fileidentifier);
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
        // Запрос на копирование файла
        return $this->source->file_copy($fileidentifier, $filearea, $filepath, $itemid);
    }
    
    /**
     * Копирование файла из внешенго источника в драфтовую зону пользователя
     *
     * @param string $fileidentifier - Индентификатор файла в источнике
     *
     * @return int|null - Идентификатор драфтовой зоны или null
     */
    public function file_copy_to_draft($fileidentifier)
    {
        // Получение свободного идентификатора подзоны
        $itemid = $this->dof->modlib('filestorage')->get_new_itemid(self::$draftfilearea);
        
        // Копирование файла из источника во временную зону менеджера файлов
        $file = $this->source->file_copy(
            $fileidentifier,
            self::$draftfilearea, 
            $fileidentifier, 
            $itemid
        );
        if ( $file === null )
        {// Ошибка копирования файла
            return null;
        }
        
        // Миграция файла в пользовательскую драфтовую зону
        $draftitemid = $this->dof->modlib('filestorage')->copy_file_to_draftarea(
            self::$draftfilearea,
            $itemid
        );
        if ( $draftitemid === false )
        {// Ошибка сохранения файла в драфтовую зону
            return null;
        }

        return $draftitemid;
    }
}