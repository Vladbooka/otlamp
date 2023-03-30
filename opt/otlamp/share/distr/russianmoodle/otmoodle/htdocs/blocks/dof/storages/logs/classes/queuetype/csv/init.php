<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
 * Хранилище очередей логов Деканата. Класс очереди логов CSV-формата.
 *
 * @package    storage
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_logs_queuetype_csv extends dof_storage_logs_queuetype_base
{
    /**
     * Путь до файла очереди логов
     * 
     * @var string
     */
    protected $filepath = null;
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - Контроллер деканата
     * @param stdClass $dbinstance - Данные очереди
     *
     * @return void
     */
    public function __construct($dof, $dbinstance)
    {
        // Базовый конструктор
        parent::__construct($dof, $dbinstance);
        
        // Генерация пути до файла
        $filename = $this->get_filename();
        $filepath = $this->dof->plugin_path('storage', 'logs', '/dat/'.self::get_code().'/');
        $this->filepath = $filepath.$filename;
        
        // Создание файла
        if ( ! file_exists($this->filepath) )
        {
            $file = fopen($this->filepath, 'w');
            fputcsv($file, [
                'level',
                'action', 
                'targetname', 
                'targetid', 
                'status', 
                'additionaldata', 
                'comment', 
                'time'
            ], ';');
            fclose($file);
        }
    }
    
    /**
     * Получение имени файла очереди логов
     *
     * @return string
     */
    protected function get_filename()
    {
        $filename = $this->dbinstance->id.
                    $this->dbinstance->ptype.
                    $this->dbinstance->pcode.
                    $this->dbinstance->subcode.
                    $this->dbinstance->objid;
       return md5($filename);
    }
    
    /**
     * Записать лог в очередь
     *
     * @return void
     */
    protected function write_log($logdata)
    {
        $file = fopen($this->filepath, 'a');
        fputcsv($file, [
            $logdata->level,
            $logdata->action,
            $logdata->targetname,
            $logdata->targetid,
            $logdata->status,
            $logdata->additionaldata,
            $logdata->comment,
            $logdata->time
        ], ';');
        fclose($file);
    }
     
    /**
     * Удаление очереди логов
     *
     * @return void
     */
    public function delete()
    {
        unlink($this->filepath);
    }
    
    /**
     * Получение очереди логов
     *
     * @return array - Данные очереди логов
     */
    public function get_logs($limitfrom = 0, $limitnum = 0)
    {
        // Результат
        $data = [];

        $file = fopen($this->filepath, 'r');
        $data[] = fgetcsv($file, 2048, ';');
        while ( ( $row = fgetcsv($file, 2048, ';') ) !== false )
        {// Складывание строк в массив
            $data[] = [
                $this->actions[$row[1]],
                $this->dof->get_string('title', $row[2], null, 'storage'),
                $row[3],
                $this->statuses[$row[4]],
                $row[5],
                $row[6],
                $row[7]
            ];
        }
        fclose($file);
        
        return $data;
    }
}