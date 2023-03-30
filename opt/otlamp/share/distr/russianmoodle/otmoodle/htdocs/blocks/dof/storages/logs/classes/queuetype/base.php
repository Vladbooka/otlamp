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
 * Хранилище очередей логов Деканата. Базовый класс очереди логов.
 *
 * @package    storage
 * @subpackage logs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_storage_logs_queuetype_base
{
    /**
     * Контроллер деканата
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Данные очереди логов
     *
     * @var stdClass
     */
    protected $dbinstance = null;
    
    /**
     * Уровни сообщений очереди
     * 
     * @var array
     */
    protected $levels = [];
    
    /**
     * Уровень по умолчанию
     *
     * @var array
     */
    protected $leveldefault = null;
    
    /**
     * Уровни сообщений очереди
     *
     * @var array
     */
    protected $actions = [];
    
    /**
     * Уровень по умолчанию
     *
     * @var array
     */
    protected $actiondefault = null;
    
    /**
     * Уровни сообщений очереди
     *
     * @var array
     */
    protected $statuses = [];
    
    /**
     * Уровень по умолчанию
     *
     * @var array
     */
    protected $statusdefault = null;
    
    /**
     * Получение кода очереди логов
     *
     * @return string
     */
    public static final function get_code()
    {
        return str_replace('dof_storage_logs_queuetype_', '', static::class);
    }
    
    /**
     * Получить локализованное имя очереди логов
     *
     * @return string
     */
    public static function get_name_localized()
    {
        GLOBAL $DOF;
        return $DOF->get_string('queuetype_'.static::get_code().'_name', 'logs', null, 'storage');
    }
    
    /**
     * Получить локализованное описание очереди логов
     *
     * @return string
     */
    public static function get_description_localized()
    {
        GLOBAL $DOF;
        return $DOF->get_string('queuetype_'.static::get_code().'_description', 'logs', null, 'storage');
    }
    
    /**
     * Задача по обслуживанию очереди логов текущего типа
     *
     * @return string
     */
    public static function cron()
    {
    }
    
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
        $this->dof = $dof;
        if ( $dbinstance->logtype !== self::get_code() )
        {// Данные не валидны
            return null;
        }
        $this->dbinstance = $dbinstance;
        
        // Установка доступных уровней логов
        $this->levels = [
            'message' => $this->dof->get_string('level_message', 'logs', null, 'storage'),
            'notice' => $this->dof->get_string('level_notice', 'logs', null, 'storage'),
            'warning' => $this->dof->get_string('level_warning', 'logs', null, 'storage'),
            'error' => $this->dof->get_string('level_error', 'logs', null, 'storage')
        ];
        $this->leveldefault = 'message';
        
        // Установка доступных действий
        $this->actions = [
            'undefined' => $this->dof->get_string('action_undefined', 'logs', null, 'storage'),
            'insert' => $this->dof->get_string('action_insert', 'logs', null, 'storage'),
            'update' => $this->dof->get_string('action_update', 'logs', null, 'storage'),
            'delete' => $this->dof->get_string('action_delete', 'logs', null, 'storage'),
            'get' => $this->dof->get_string('action_get', 'logs', null, 'storage'),
            'view' => $this->dof->get_string('action_view', 'logs', null, 'storage'),
            'open' => $this->dof->get_string('action_open', 'logs', null, 'storage'),
            'close' => $this->dof->get_string('action_close', 'logs', null, 'storage'),
            'import' => $this->dof->get_string('action_import', 'logs', null, 'storage'),
            'export' => $this->dof->get_string('action_export', 'logs', null, 'storage')
        ];
        $this->actiondefault = 'undefined';
        
        // Установка доступных статусов
        $this->statuses = [
            'undefined' => $this->dof->get_string('status_undefined', 'logs', null, 'storage'),
            'success' => $this->dof->get_string('status_success', 'logs', null, 'storage'),
            'error' => $this->dof->get_string('status_error', 'logs', null, 'storage')
        ];
        $this->statusdefault = 'undefined';
    }
    
    /**
     * Получить идентификатор очереди
     *
     * @return int
     */
    public function get_id()
    {
        return (int)$this->dbinstance->id;
    }
    
    /**
     * Добавить запись в очередь логов
     * 
     * @return void
     */
    public function addlog($level = null, $action = null, $targetname = null, $targetid = null, $status = null, array $additionaldata = [], $comment = null)
    {
        // Генерация записи лога
        $logdata = new stdClass();
        
        // Уровень лога
        $logdata->level = (string)$level;
        if ( ! isset($this->levels[$logdata->level]) )
        {
            $logdata->level = $this->leveldefault;
        }
        // Действие
        $logdata->action = (string)$action;
        if ( ! isset($this->actions[$logdata->action]) )
        {
            $logdata->action = $this->actiondefault;
        }
        // Статус
        $logdata->status = (string)$status;
        if ( ! isset($this->statuses[$logdata->status]) )
        {
            $logdata->status = $this->statusdefault;
        }
        $logdata->targetname = (string)$targetname;
        $logdata->targetid = (string)$targetid;
        $logdata->additionaldata = serialize($additionaldata);
        $logdata->comment = (string)$comment;
        $logdata->time = time();
        
        // Запись лога
        $this->write_log($logdata);
    }
    
    /**
     * Записать лог в очередь
     *
     * @return void
     */
    protected function write_log($logdata)
    {
    }
    
    /**
     * Закрытие очереди логов
     * 
     * @return void
     */
    public function finish()
    {
    }
    
    /**
     * Удаление очереди логов
     *
     * @return void
     */
    public function delete()
    {
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
        return $data;
    }
    
    /**
     * Доступные экшены
     *
     * @return array
     */
    public final function get_available_actions()
    {
        return $this->actions;
    }
    
    /**
     * Доступные статусы
     *
     * @return array
     */
    public final function get_available_statuses()
    {
        return $this->statuses;
    }
}