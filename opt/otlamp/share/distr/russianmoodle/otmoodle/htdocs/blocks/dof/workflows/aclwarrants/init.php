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


/** Доверенности
 * 
 */
class dof_workflow_aclwarrants implements dof_workflow
{
    /**
     * Хранит методы ядра деканата
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return true;
    }
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2017031500;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'guppy_a';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'workflow';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'aclwarrants';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('aclwarrants'=>2011040501));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage','plugincode'=>'aclwarrants','eventcode'=>'insert'));
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }
    /** 
     * Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        if ( $gentype==='storage' AND $gencode === $this->get_storage() AND $eventcode === 'insert' )
        {
            // Отлавливаем добавление нового объекта
            // Инициализируем плагин
            return $this->init($intvar);
        }
        return true;
    }
    /** 
     * Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
       /** 
        * Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'aclwarrants';
    }
    /** 
     * Возвращает массив всех состояний,   
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list()
    {
        return array('draft'   => $this->dof->get_string('status:draft',   $this->code(),NULL,$this->type()),
                     'active'  => $this->dof->get_string('status:active',  $this->code(),NULL,$this->type()),
                     'archive' => $this->dof->get_string('status:archive', $this->code(),NULL,$this->type()));
    }
    /** 
     * Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
     */
    public function get_name($status)
    {
        $list = $this->get_list();
        if ( isset($list[$status]) )
        {
            return $list[$status];
        }
        return '';
    }
    /** 
     * Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект из ages
        if ( ! $obj = $this->dof->storage($this->get_storage())->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            case 'draft':       // переход из статуса "запланирован"
                $statuses = array('active'=>$this->get_name('active'), 'archive'=>$this->get_name('archive'));
            break;
            
            case 'active':   // переход из статуса "идет"
                $statuses = array('archive'=>$this->get_name('archive'));
            break;
            case 'archive':  // архив - конечный статус
                $statuses = array();
            break;
            default: $statuses = array('draft'=>$this->get_name('draft'));
        }
        
        return $statuses;
    }
    
    /** Возвращает массив метастатусов
     * @param string $type - тип списка метастатусов
     *               'active' - активный 
     *               'actual' - актуальный
     *               'real' - реальный
     *               'junk' - мусорный
     * @return array
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return array('active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'actual':
                return array('active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'real':
                return array('draft'  => $this->dof->get_string('status:draft', $this->code(), NULL, 'workflow'),
                             'active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'junk':
                return array('archive' => $this->dof->get_string('status:archive', $this->code(), NULL, 'workflow'));
            default:
                dof_debugging('workflow/' . $this->code() . ' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }    

    /**
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * @param int id - id экземпляра объекта
     * @param string newstatus - название состояния, в которое переводится объект
     * @param array options - массив дополнительных опций
     * @return boolean  true - удалось перевести в указанное состояние,
     *                  false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $newstatus, $options=null)
    {
        // Увеличение лимитов памяти и времени исполнения
        dof_hugeprocess();
    
        $id = intval($id);
        $storage = $this->dof->storage($this->get_storage());
        if (! $object = $storage->get($id))
        { // Не удалось получить объект
            return false;
        }
        if (! $list = $this->get_available($id))
        { // Ошибка получения статуса для объекта;
            return false;
        }
        if (! isset($list[$newstatus]))
        { // Переход в данный статус из текущего невозможен
            return false;
        }

        // Объект для обновления статуса
        $updateobject = new stdClass();
        $updateobject->id = $id;
        $updateobject->status = $newstatus;
        
        // Изменение статуса
        $result = $storage->update($updateobject);
        
        if ( $result )
        {// запись обновилась
            // обновим статусы потомков
            if ( $list = $storage->get_records(array('parentid'=>$id)) )
            {// если таковы есть
                foreach($list as $record)
                {
                    $result = ( $result AND $this->change($record->id, $newstatus, $options) );
                }
            }
            // обновим статус применениям
            switch($newstatus)
            {
                case 'active':
                    $wastatus = 'draft';
                break;
                case 'archive':
                    $wastatus = ['draft','active'];
                break;
                default: 
                    $wastatus = [null];
                break;
            }
            if ( $list = $this->dof->storage('aclwarrantagents')->get_records(array('aclwarrantid'=>$id,'status'=>$wastatus)) 
                 AND isset($options['changestatuswa']) AND $options['changestatuswa'] )
            {// если они есть и это необходимо
                foreach($list as $record)
                {
                    $result = ( $result AND $this->dof->workflow('aclwarrantagents')->change($record->id, $newstatus, $options) );
                }
            }
        }else
        {// не удалось обновить
            return false;
        }
        
        if ( ! $result )
        {// какому-то применению полномочий не удалось изменить статус - вернем мандате исходное состояние
            $storage->update($object);
            // сообщим о неудачной операции
            return false;
        }
        
        // Запись в историю изменения статусов
        $this->dof->storage('statushistory')->change_status(
            $this->get_storage(),
            $id,
            $newstatus,
            $object->status,
            $options
        );
        
        return $result;
    }
    /** 
     * Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id)
    {
        // Получаем объект из справочника
        if (!$obj = $this->dof->storage($this->get_storage())->get($id))
        {// Объект не найден
            return false;
        }
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'draft';
        return $this->dof->storage($this->get_storage())->update($obj);
    }
    
    
    
    // **********************************************
    // Собственные методы
    // **********************************************
    /** 
     * Конструктор
     * @param dof_control $dof - это $DOF
     * объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /** Изменить статус для списка доверенностей
     * 
     * @return bool
     * @param array $warants - список доверенностей, которым нужно изменить статус
     */
    public function set_status_to_list($warrants, $newstatus)
    {
        $result = true;
        if ( empty($warrants) OR ! is_array($warrants) )
        {// не нужно менять статус
            return true;
        }
        
        foreach ( $warrants as $id => $warrant )
        {// активируем все доверенности
            $result = ($result & $this->change($id, $newstatus));
        }
        
        return $result;
    }
}

?>