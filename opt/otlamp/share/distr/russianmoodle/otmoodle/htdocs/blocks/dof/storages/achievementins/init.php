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

require_once $DOF->plugin_path('storage','config','/config_default.php');

/**
 * Справочник достижений персон
 * 
 * @package    storage
 * @subpackage achievementins
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_storage_achievementins 
        extends dof_storage 
        implements dof_storage_config_interface, dof_storage_deadline_interface
{
    /**
     * Объект деканата для доступа к общим методам
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
     *
     * @return boolean
     */
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
        
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        global $CFG, $DB;
        $result = true;
        // Методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        
        if( $oldversion < 2018012900 )
        {
            // Заменим старые права im_achievements_achievementins/delete на storage_achievementins_delete
            // Заменим старые права im_achievements_achievementins/edit на storage_achievementins_edit
            $corewarrants = [];
            $rolesfordelete = $this->dof->storage('aclwarrants')->get_default_roles();
            foreach($rolesfordelete as $rolecode)
            {
                $warrant = $this->dof->storage('aclwarrants')->get_core_warrant_by_code($rolecode);
                if( ! empty($warrant) )
                {
                    // Замену проведем во всех доверенностях, отнаследованных от базовых и ниже
                    $corewarrants[$warrant->id] = $warrant->id;
                }
            }
            $this->dof->storage('acl')->acl_replacement_all(
                'im', 
                'achievements', 
                'achievementins/delete', 
                $corewarrants, 
                'storage', 
                'achievementins', 
                'delete'
            );
            $this->dof->storage('acl')->acl_replacement_all(
                'im',
                'achievements',
                'achievementins/edit',
                $corewarrants,
                'storage',
                'achievementins',
                'edit'
            );
        }
        if( $oldversion < 2018051100)
        {// добавим поле goaldeadline - сценарий использования шаблона (битмаск)
            $field = new xmldb_field(
                'goaldeadline',
                XMLDB_TYPE_INTEGER,
                '11',
                false,
                false,
                null,
                null,
                'userpoints'
            );
            
            if ( ! $dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
        }
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2019090300;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'storage';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'achievementins';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
            'storage' => [
                'config' => 2011080900,
                'acl' => 2011041800,
                'achievements' => 2016041800,
                'deadline' => 2018041000
            ],
            'modlib' => [
                'messager' => 2018041000
            ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *                                    
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array( 
		        'storage' => array(
		                'config'       => 2011080900,
		                'acl'          => 2011041800,
		                'achievements' => 2016041800,
		        )
		);
    }

    /**
     * Список обрабатываемых плагином событий
     *
     * @return array -
     *         array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'storage',
                'plugincode' => 'achievements',
                'eventcode' => 'update'
            ],
            [
                'plugintype' => 'storage',
                'plugincode' => 'achievementins',
                'eventcode' => 'update'
            ]
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        // Запуск не требуется 
        return true;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = null, $userid = null, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для администраторов
            return true;
        }
        
        // Получение ID текущей персоны, для которой производится проверка прав
        $currentpersonid = (int)$this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Дополнительные проверки прав
        switch ( $do )
        {
            // Право создания достижений
            case 'create' :
                // Владелец достижения
                $targetpersonid = $objid;
                if ( empty($targetpersonid) )
                {// Пользователь не указан
                    // Текущий пользователь - владелец достижения
                    $targetperson = $this->dof->storage('persons')->get_bu();
                    $targetpersonid = (int)$targetperson->id;
                }
                if ( $targetpersonid == $currentpersonid )
                {// Попытка создать достижение для себя
                    
                    // Проверка на право создания своего достижения
                    if ( $this->is_access('create/owner', null, $userid, $depid) )
                    {
                        return true;
                    }
                } else
                {// Попытка создать достижение другому пользователю
                    if ( ! $this->dof->storage('achievementcats')->is_access_use_any($userid, $depid) )
                    {// Текущему пользователю не доступен ни один из разделов достижений
                        return false;
                    }
                }
                break;
            // Право создания достижений для себя
            case 'create/owner' :
                // Проверка права использования любой из доступных пользователю разделов
                if ( $this->dof->storage('achievementcats')->is_access_use_any($userid, $depid) )
                {// Пользователю доступен один из разделов портфолио
                    break;
                } else 
                {// Пользователь не имеет права использовать ни один из разделов достижений
                    return false;
                }
                break;
            // Право на редактирование любых достижений/целей
            case 'edit':
                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }
                    
                    // Получение реальных статусов достижения
                    $statuses_achievement = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
                    $statuses_goal = $this->dof->workflow('achievementins')->get_meta_list('goal_real');
                    if ( ! array_key_exists($achievementin->status, $statuses_achievement) )
                    {
                        if ( array_key_exists($achievementin->status, $statuses_goal) )
                        {
                            return $this->is_access_goal('edit', $achievementin->achievementid, $achievementin->userid, $userid, $depid);
                        }
                    }
                    
                    if ( $currentpersonid == $achievementin->userid )
                    {// Владелец
                        if( $this->is_access('edit/owner', $objid) )
                        {// Есть право редактировать свои достижения
                            return true;
                        }
                    }
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
            // Право редактировать свои достижения
            case 'edit/owner':
                if ( ! empty($objid) )
                {// Достижение указано
                    // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    {// Достижение не найдено в системе
                        return false;
                    }
                    
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('achievement_real');
                    if ( ! array_key_exists($achievementin->status , $statuses) )
                    {// Статус достижения не является активным
                        return false;
                    }
                    
                    if ( $currentpersonid == $achievementin->userid )
                    {// Владелец
                        $objid = NULL;
                    } else 
                    {
                        return false;
                    }
                } else
                {// Шаблон не указан
                    return false;
                }
                break;
                // Право на редактирование любых достижений
            case 'delete':
                if ( ! empty($objid) ) 
                { // Достижение указано
                  // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) ) 
                    { // Достижение не найдено в системе
                        return false;
                    }
                    
                    // Поддержка ручного удаления достижения
                    if ( ! $this->can_manual_delete($achievementin) )
                    {// Ручное удаление не поддерживается текущим достижением
                        return false;
                    }
                    
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status, $statuses) ) 
                    { // Статус достижения не является активным
                        return false;
                    }
                    
                    if ( $currentpersonid == $achievementin->userid ) 
                    { // Владелец
                        if ( $this->is_access('delete/owner', $objid) ) 
                        { // Есть право редактировать свои достижения
                            return true;
                        }
                    }
                } else 
                { // Шаблон не указан
                    return false;
                }
                break;
            // Право редактировать свои достижения
            case 'delete/owner':
                if ( ! empty($objid) ) 
                { // Достижение указано
                  // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) ) 
                    { // Достижение не найдено в системе
                        return false;
                    }
                    
                    // Поддержка ручного удаления достижения
                    if ( ! $this->can_manual_delete($achievementin) )
                    {// Ручное удаление не поддерживается текущим достижением
                        return false;
                    }
                    
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status, $statuses) ) 
                    { // Статус достижения не является активным
                        return false;
                    }
                    
                    if ( $currentpersonid == $achievementin->userid ) 
                    { // Владелец
                        $objid = NULL;
                    } else 
                    {
                        return false;
                    }
                } else 
                { // Шаблон не указан
                    return false;
                }
                break;
            // Право на архивацию достижений
            case 'archive':
                if ( ! empty($objid) )
                { // Достижение указано
                    // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    { // Достижение не найдено в системе
                        return false;
                    }
                
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status, $statuses) )
                    { // Статус достижения не является активным
                        return false;
                    }
                
                    if ( $currentpersonid == $achievementin->userid )
                    { // Владелец
                        if ( $this->is_access('archive/owner', $objid) )
                        { // Есть право на архивацию своих достижений
                            return true;
                        }
                    }
                } else
                { // Шаблон не указан
                    return false;
                }
                break;
            // Право на архивацию своих достижений
            case 'archive/owner':
                if ( ! empty($objid) )
                { // Достижение указано
                    // Поиск достижения
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    { // Достижение не найдено в системе
                        return false;
                    }
                
                    // Получение реальных статусов достижения
                    $statuses = $this->dof->workflow('achievementins')->get_meta_list('real');
                    if ( ! array_key_exists($achievementin->status, $statuses) )
                    { // Статус достижения не является активным
                        return false;
                    }
                
                    if ( $currentpersonid == $achievementin->userid )
                    { // Владелец
                        $objid = NULL;
                    } else
                    {
                        return false;
                    }
                } else
                { // Шаблон не указан
                    return false;
                }
                break;
            // Право просмотра комментариев к достижению
            case 'view_comments' :
                // ID достижения, для которого проверяется право
                $achievementinid = (int)$objid;
                if ( $achievementinid > 0 )
                {// Указан идентификатор достижения - Подключение дополнительных условий
                    
                    // Получение достижения
                    $achievementin = $this->dof->storage('achievementins')->get($achievementinid);
                    if ( ! empty($achievementin) )
                    {// Достижение найдено
                        
                        // Владелец достижения
                        $targetpersonid = $achievementin->userid;
                        
                        // Получение шаблона достижения
                        $achievementid = 0;
                        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
                        if ( ! empty($achievement->id) )
                        {// Шаблон найден
                            $achievementid = $achievement->id;
                        }
                        
                        // Получение раздела достижений
                        $achievementcatid = 0;
                        if ( ! empty($achievement->catid) )
                        {
                            $achievementcatid = $achievement->catid;
                        }
                        
                        // Проверки прав для владельца достижения
                        if ( $targetpersonid == $currentpersonid )
                        {// Текущий пользователь является владельцем достижения
                            
                            // Проверка права просмотра комментариев владельцем
                            if( $this->is_access('view_comments_achievementid/owner', $achievementid, $userid, $depid) )
                            {// Право для владельца указано
                                return true;
                            }
                            
                            // Проверка права просмотра комментариев владельцем в целевом разделе
                            if( $this->is_access('view_comments_achievementcatid/owner', $achievementcatid, $userid, $depid) )
                            {// Право для владельца указано
                                return true;
                            }
                        }
                        
                        // Проверка права просматривать комментарии для целевого достижения
                        if ( $this->is_access('view_comments_achievementid', $achievementid, $userid, $depid) )
                        {// Право для персоны указано
                            return true;
                        }
                        
                        // Проверка права просматривать комментарии достижений в целевом разделе 
                        if ( $this->is_access('view_comments_achievementcatid', $achievementcatid, $userid, $depid) )
                        {// Право для персоны указано
                            return true;
                        }
                    }
                }
                break;
                
            // Право создания комментария к достижению
            case 'create_comments' :
                
                // ID достижения, для которого проверяется право
                $achievementinid = (int)$objid;
                if ( $achievementinid > 0 )
                {// Указан идентификатор достижения - Подключение дополнительных условий
                
                    // Получение достижения
                    $achievementin = $this->dof->storage('achievementins')->get($achievementinid);
                    if ( ! empty($achievementin) )
                    {// Достижение найдено
                    
                        // Владелец достижения
                        $targetpersonid = $achievementin->userid;
                        
                        // Получение шаблона достижения
                        $achievementid = 0;
                        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
                        if ( ! empty($achievement->id) )
                        {// Шаблон найден
                        $achievementid = $achievement->id;
                        }
                        
                        // Получение раздела достижений
                        $achievementcatid = 0;
                        if ( ! empty($achievement->catid) )
                        {
                            $achievementcatid = $achievement->catid;
                        }
                        
                        // Проверки прав для владельца достижения
                        if ( $targetpersonid == $currentpersonid )
                        {// Текущий пользователь является владельцем достижения
                        
                            // Проверка права просмотра комментариев владельцем
                            if( $this->is_access('create_comments_achievementid/owner', $achievementid, $userid, $depid) )
                            {// Право для владельца указано
                                return true;
                            }
                            
                            // Проверка права просмотра комментариев владельцем в целевом разделе
                            if( $this->is_access('create_comments_achievementcatid/owner', $achievementcatid, $userid, $depid) )
                            {// Право для владельца указано
                                return true;
                            }
                        }
                        
                        // Проверка права просматривать комментарии для целевого достижения
                        if ( $this->is_access('create_comments_achievementid', $achievementid, $userid, $depid) )
                        {// Право для персоны указано
                            return true;
                        }
                        
                        // Проверка права просматривать комментарии достижений в целевом разделе
                        if ( $this->is_access('create_comments_achievementcatid', $achievementcatid, $userid, $depid) )
                        {// Право для персоны указано
                            return true;
                        }
                    }
                }
                break;
                
            // одобрять цель
            case 'approve_goal':
                if ( ! empty($objid) )
                {// Цель указана
                    // Получение цели
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    {// Цель не найдена в системе
                        return false;
                    }
                    
                    if ( $achievementin->status != 'wait_approval' )
                    {// цель не ожидает одобрения
                        return false;
                    }
                    
                    return $this->is_access_goal('approve', $achievementin->achievementid, $achievementin->userid, null, $depid);
                } else
                {// шаблон не указан
                    return false;
                }
                break;
                
            // подтверждать выполнение цели
            case 'achieve_goal':
                if ( ! empty($objid) )
                {// Цель указана
                    // Получение цели
                    $achievementin = $this->get($objid);
                    if ( empty($achievementin) )
                    {// Цель не найдена в системе
                        return false;
                    }
                    
                    if ( $achievementin->status != 'wait_completion' )
                    {// Цель не ожидает подтверждения выполнения
                        return false;
                    }
                    
                    return $this->is_access_goal('achieve', $achievementin->achievementid, $achievementin->userid, null, $depid);
                } else
                {// шаблон не указан
                    return false;
                }
                break;
                
            default:
                break;
        }
        
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $currentpersonid, $depid);
        
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        return false;
    }
    
	/** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /** 
     * Обработать событие
     * 
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        if ( $gentype === 'storage' AND $gencode === 'achievements' AND $eventcode === 'update' )
        {// Обновление шаблона достижения
            // Получение объекта класса обновленного шаблона
            $achievement = $this->dof->storage('achievements')->object($intvar);
            if ( ! empty($achievement) )
            {// Объект получен
                // Получение достижений шаблона
                $achievementins = (array)$this->get_achievementins($intvar, null, ['metastatus' => 'real']);
                
                // Корректировка данных пользовательского достижения при обновлении шаблона
                foreach ( $achievementins as $achievementin )
                {
                    $achievementinsdata = unserialize($achievementin->data);
                    $person = $this->dof->storage('persons')->get($achievementin->userid);
                    $options = [
                        'oldachievement' => $mixedvar['old'],
                        'userid' => $person->mdluser,
                        'instance' => $achievementin
                    ];
                    
                    // Сохранение баллов пользовательского достижения
                    $savedachievementin = new stdClass();
                    $savedachievementin->id = $achievementin->id;
                    $savedachievementin->achievementid = $achievementin->achievementid;
                    $savedachievementin->userid = $achievementin->userid;
                    $savedachievementin->userpoints = $achievement->instance_calculate_userpoints($achievementinsdata, $options);
                    $savedachievementin->goaldeadline = $achievementin->goaldeadline;
                    $savedachievementin->data = $achievementin->data;
                    $this->save($savedachievementin, [], true);
                }
            }
        }
        
        if ( $gentype === 'storage' AND $gencode === 'achievementins' AND $eventcode === 'update' )
        {
            if ( $mixedvar['new']->status == 'deleted' )
            {
                // очистка дедлайнов при удалении достижения
                $this->process_goal_deadlines($this->dof->storage('achievements')->get($mixedvar['new']->achievementid), $mixedvar['new']);
            }
        }
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        switch($code) {
            case 'clean_empty_achievementins':
                $this->clean_empty_achievementins($intvar, $mixedvar);
                break;
            default:
                break;    
        }
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
   
    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_achievementins';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
        
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
     * 
     * Функция вынесена сюда, чтобы постоянно не писать 
     * длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right(
                            $acldata->plugintype, 
                            $acldata->plugincode, 
                            $acldata->code, 
                            $acldata->personid, 
                            $acldata->departmentid, 
                            $acldata->objectid
        );
    }    
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        
        // Создавать достижения для пользователя
        $a['create'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // Создавать достижения для себя
        $a['create/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student',
                'user'
            ]
        ];
        // Просматривать комментарии к достижениям, созданным по указанному шаблону
        $a['view_comments_achievementid'] = [
            'roles' => []
        ];
        // Просматривать комментарии к достижениям, созданным по шаблонам указанной категории
        $a['view_comments_achievementcatid'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        // Комментировать достижения, созданные по указанному шаблону
        $a['create_comments_achievementid'] = [
            'roles' => []
        ];
        //  Комментировать достижения, созданные по шаблонам указанной категории
        $a['create_comments_achievementcatid'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        // Просматривать комментарии к собственным достижениям, созданным по указанному шаблону
        $a['view_comments_achievementid/owner'] = [
            'roles' => []
        ];
        // Просматривать комментарии к собственным достижениям, созданным по шаблонам указанной категории
        $a['view_comments_achievementcatid/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        // Комментировать собственные достижения, созданные по указанному шаблону
        $a['create_comments_achievementid/owner'] = [
            'roles' => []
        ];
        // Комментировать собственные достижения, созданные по шаблонам указанной категории
        $a['create_comments_achievementcatid/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];

       // Архивация любых достижений
        $a['archive'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Архивация своих достижений
        $a['archive/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // Удаление любых достижений
        $a['delete'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Удаление своих достижений
        $a['delete/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // Редактирование любых достижений
        $a['edit'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        // Редактирование своих достижений
        $a['edit/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // Регистрировать новые цели для себя
        $a['create_goal/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // Регистрировать новые цели для персоны
        $a['create_goal_to_person'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // Регистрировать новые цели в указанном шаблоне
        $a['create_goal_by_template'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // Редактировать свои цели
        $a['edit_goal/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // Редактировать цели персоны
        $a['edit_goal_to_person'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // Редактировать цели по шаблону
        $a['edit_goal_by_template'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // одобрять свои цели
        $a['approve_goal/owner'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // одобрять цель конкретного пользователя
        $a['approve_goal_to_person'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // одобрять цели по шаблону
        $a['approve_goal_by_template'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // одобрять цели по шаблону всем, кроме себя
        $a['approve_goal_by_tempate/except_myself'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // подтверждать выполнение своей цели
        $a['achieve_goal/owner'] = [
            'roles' => [
                'manager',
                'methodist',
                'teacher',
                'parent',
                'student'
            ]
        ];
        
        // подтверждать выполнение цели конкретного пользователя
        $a['achieve_goal_to_person'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // подтверждать выполнение цели по шаблону
        $a['achieve_goal_by_template'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        // подтверждать выполнение цели по шаблону всем, кроме себя
        $a['achieve_goal_by_template/except_myself'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        
        return $a;
    }

    /** 
     * Функция получения настроек для плагина 
     */
    public function config_default($code=null)
    {
        $configs = [];
        
        // уведомлять модераторам в подразделении пользователя без иерархии
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'notificate_onlypersondep_moderators';
        $obj->value = '0';
        $configs[$obj->code] = $obj;
        
        // единовременное уведомление модераторам о новой цели
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_stat_promptly_goal';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>Пользователь «{STUDENTFULLNAME}» добавил новую цель по шаблону «{ACHIEVEMENTNAME}», требуется одобрение цели.<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // единовременное уведомление модераторам о новом достижении
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_stat_promptly_achievement';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>Пользователь «{STUDENTFULLNAME}» добавил новое достижение по шаблону «{ACHIEVEMENTNAME}», требуется подтверждение достижения.<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление пользователю за N дней до дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_before_user';
        $obj->value = 'Здравствуйте, {STUDENTFULLNAME}!<br/><br/>До крайней даты сдачи Вашей цели «{ACHIEVEMENTNAME}» осталось {DAYS} дней!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление куратору за N дней до дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_before_curator';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>До крайней даты выполнения цели «{ACHIEVEMENTNAME}» у пользователя «{STUDENTFULLNAME}» осталось {DAYS} дней!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление пользователю через N дней после дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_after_user';
        $obj->value = 'Здравствуйте, {STUDENTFULLNAME}!<br/><br/>С момента крайней даты выполнения Вашей цели «{ACHIEVEMENTNAME}» прошло {DAYS} дней!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление куратору через N дней после дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_after_curator';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>С момента крайней даты выполнения цели «{ACHIEVEMENTNAME}» у пользователя «{STUDENTFULLNAME}» прошло {DAYS} дней!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление пользователю в день дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_inday_user';
        $obj->value = 'Здравствуйте, {STUDENTFULLNAME}!<br/><br/>Сегодня крайняя дата выполнения Вашей цели «{ACHIEVEMENTNAME}»!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление куратору в день дедлайна
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_inday_curator';
        $obj->value = 'Здравствуйте, {USERFULLNAME}!<br/><br/>Сегодня крайняя дата выполнения цели «{ACHIEVEMENTNAME}» у пользователя «{STUDENTFULLNAME}»!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление пользователю при одобрении цели
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_user_approve';
        $obj->value = 'Здравствуйте, {STUDENTFULLNAME}!<br/><br/>Ваша цель «{ACHIEVEMENTNAME}» была одобрена!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        // уведомление пользователю при отклонении цели
        $obj = new stdClass();
        $obj->type = 'textarea';
        $obj->code = 'notification_user_reject';
        $obj->value = 'Здравствуйте, {STUDENTFULLNAME}!<br/><br/>Ваша цель «{ACHIEVEMENTNAME}» была отклонена!<br/>Для перехода нажмите на ссылку {URL}<br/><br/>С уважением, администрация сайта «{SITENAME}»!';
        $configs[$obj->code] = $obj;
        
        return $configs;
    }       

    /**
     * массив объявленных уведомлений
     * 
     * @return string[]
     */
    public function registered_notification_types()
    {
        $messager = $this->dof->modlib('messager');
        return [
            // уведомления о недостижении цели
            'goal_achieve_failed' => $messager::MESSAGE_PROVIDER_URGENT,
            
            // уведомления об одобрении цели
            'goal_approve' => $messager::MESSAGE_PROVIDER_URGENT,
            
            // уведомления об отклонении цели
            'goal_reject' => $messager::MESSAGE_PROVIDER_URGENT,
            
            // уведомление о новой цели/достижении
            'achievementin_new' => $messager::MESSAGE_PROVIDER_URGENT,
        ];
    }
    
    // **********************************************
    //              Собственные методы
    // ********************************************** 
    
    /**
     * Сохранить пользовательское достижение
     *
     * @param object $object - Объект достижения
     *                  Обязательные поля:
     *                  ->achievementid - ID шаблона достижения
     *                  ->userid - ID персоны, которой принадлежит достижение
     *                  Необязательные поля:
     *                  ->moderatorid - ID персоны, проверившая достижение
     *                  ->timechecked - Время проверки достижения
     *                  ->userpoints - Пользовательские баллы по достижению
     *                  ->data - Данные пользователя по достижению
     *
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID достижения в случае успеха
     */
    public function save( $object = null, $options = [], $achievementdatachanged = false )
    {
        // Проверка входных данных
        if ( empty($object) || ! is_object($object) )
        {// Проверка не пройдена
            return false;
        }
        
        // Создаем объект для сохранения
        $saveobj = clone $object;
        // Убираем автоматически генерируемые поля
        unset($saveobj->status);
    
        // Проверка шаблона
        if ( isset($saveobj->achievementid) )
        {
            if ( $saveobj->achievementid <= 0 )
            {// Шаблон достижения не установлен
                return false;
            } else
            {// Проверка на существование шаблона
                $achievement = $this->dof->storage('achievements')->get($saveobj->achievementid);
                if ( empty($achievement) )
                {// Шаблон не найден
                    return false;
                }
            }
        } else
        {// Шаблон не установлен
            return false;
        }
        // Проверка пользователя
        if ( isset($saveobj->userid) )
        {
            if ( $saveobj->userid <= 0 )
            {// Пользователь не установлен
                return false;
            } else
            {// Проверка на существование персоны
                $person = $this->dof->storage('persons')->get($saveobj->userid);
                if ( empty($person) )
                {// Персона не найдена
                    return false;
                }
            }
        }
    
        if ( ! isset($saveobj->timechecked) )
        {
            $saveobj->timechecked = 0;
        }
        if ( ! isset($saveobj->userpoints) )
        {
            $saveobj->userpoints = null;
        }
        
        if ( isset($saveobj->id) && $saveobj->id > 0 )
        {// Обновление записи
            
            // Получим запись из БД
            $oldobject = $this->get($saveobj->id);
            if ( empty($oldobject) )
            {// Запись не найдена
                return false;
            }
            
            unset($saveobj->timecreated);
            // Обработка перед сохранением
            $saveobj = $this->beforesave_process($saveobj, $oldobject);
            
            // Обновляем запись
            $res = $this->update($saveobj);
            
            if ( empty($res) )
            {// Обновление не удалось
                return false;
            } else
            {// Обновление удалось
                
                $this->dof->send_event('storage', 'achievementins', 'item_saved', $saveobj->id);
                if( in_array($oldobject->status, ['wait_approval', 'wait_completion', 'fail_approve']) )
                {// совершено действие над целью
                    
                    // Изменились ли пользовательские данные
                    $datachanged = ( $oldobject->data != $saveobj->data );
                    $deadlinechanged = ( $oldobject->goaldeadline != $saveobj->goaldeadline );
                    // Требует ли шаблон одобрения
                    $approverequired = $this->dof->storage('achievements')->is_approval_required($achievement->scenario);
                    
                    if ( (($datachanged || $deadlinechanged) && $approverequired) || $achievementdatachanged )
                    {// Изменилась одобренная цель, ожидающая достижения
                        $this->init_goal($saveobj->id);
                    }
                    if ( $deadlinechanged )
                    {
                        $this->process_goal_deadlines($achievement, $this->get($saveobj->id));
                    }
                }
                else
                {// совершено действие над достижением
                    $this->update_achievementin_state($saveobj->id);
                }
                return $saveobj->id;
            }
        } else
        {// Создание записи
            // Убираем автоматически генерируемые поля
            unset($saveobj->id);
            
            if ( ! isset($saveobj->userid) )
            {// Пользователь не указан
                $person = $this->dof->storage('persons')->get_bu();
                if ( empty($person) )
                {
                    return false;
                }
                $saveobj->userid = $person->id;
            }
            
            // Добавляем дату создания
            $saveobj->timecreated = time();
            // Обработка перед сохранением
            $saveobj = $this->beforesave_process($saveobj);
            
            // Добавляем запись
            $res = $this->insert($saveobj);
            
            if ( empty($res) )
            {// Добавление не удалось
                return false;
            } else
            {// Добавление удалось
                
                $this->dof->send_event('storage', 'achievementins', 'item_saved', $res);
                
                if ( ! empty($options['create_goal']) )
                {
                    $this->init_goal($res);
                    
                    // добавление дедлайнов в storage/deadlines
                    $this->process_goal_deadlines($achievement, $this->get($res));
                } else
                {
                    $this->update_achievementin_state($res);
                }
                
                return $res;
            }
        }
    }
    
    /**
     * Получить пользовательские данные
     *  
     * @param array $userdata - Пользовательские данные по достиждению
     * @param array $achievementdata - Данные шаблона достижения
     * @param array $opt - Массив дополнительных параметров
     */
    public function get_user_formatted_data($userdata, $achievementdata, $opt = [])
    {
        $result = [];
        if ( isset($achievementdata['criteria']) && ! empty($achievementdata['criteria']) )
        {// Критерии установлены
            foreach ($achievementdata['criteria'] as $id => $criteria )
            {
                $item = new stdClass();
                switch ( $criteria->type )
                {
                    case 'text' :
                    case 'info' :
                        $item->significant = $criteria->significant;
                        $userkey = 'criteria'.$id.'_confirm';
                        
                        if ( isset($userdata[$userkey]) && ! empty($userdata[$userkey]) && $criteria->significant )
                        {// Элемент подтвержден
                            $item->significant = 2;
                        }
                        $userkey = 'criteria'.$id.'_value';
                        if ( isset($userdata[$userkey]) && ! is_null($userdata[$userkey]) )
                        {// Найдены данные по данному критерию
                            $item->value = $userdata[$userkey];
                        } else
                        {// Данных не найдено
                            continue;
                        }
                        $item->name = $criteria->name;
                        if ( isset($criteria->rate) )
                        {// Коэфициент установлен
                            $item->rate = $criteria->rate;
                        } else
                        {// Данных не найдено
                            $item->rate = $this->dof->get_string('criteria_rate_not_set', 'achievementins', null, 'storage');
                        }
                        $item->confirm = '';
                        
                        if ( isset($criteria->confirmfield) && ! empty($criteria->confirmfield) )
                        {// Требуется поле подтверждения
                            $userkey = 'criteria'.$id.'_file';
                            if ( isset($userdata[$userkey]) )
                            {// Данные по полю переданы
                                $item->confirm = $this->dof->modlib('filestorage')->link_files($userdata[$userkey]);
                            }
                        }
                        $result[$id] = $item; 
                        break;
                    case 'select' :
                        $item->significant = $criteria->significant;
                        $userkey = 'criteria'.$id.'_confirm';
                        if ( isset($userdata[$userkey]) && ! empty($userdata[$userkey]) && $criteria->significant )
                        {// Элемент подтвержден
                            $item->significant = 2;
                        }
                        $userkey = 'criteria'.$id.'_value';
                        if ( isset($userdata[$userkey]) && ! is_null($userdata[$userkey]) )
                        {// Найдены данные по данному критерию
                            $optionid = $userdata[$userkey];
                            if ( isset($criteria->options[$optionid]->name) )
                            {// Получена опция
                                $item->value = $criteria->options[$optionid]->name;
                            } else
                            {// Выбранная опция не найдена
                                continue;
                            }
                        } else
                        {// Данных не найдено
                            continue;
                        }
                        $item->name = $criteria->name;
                        if ( isset($criteria->options[$optionid]->rate) )
                        {// Получена опция
                            $item->rate = $criteria->options[$optionid]->rate;
                        } else
                        {// Выбранная опция не найдена
                            $item->rate = $this->dof->get_string('criteria_rate_not_set', 'achievementins', null, 'storage');
                        }
                        $item->confirm = '';
                        if ( isset($criteria->options[$optionid]->confirmfield) && ! empty($criteria->options[$optionid]->confirmfield) )
                        {// Требуется поле подтверждения
                            $userkey = 'criteria'.$id.'_option'.$optionid.'_file';
                            if ( isset($userdata[$userkey]) )
                            {// Данные по полю переданы
                                $item->confirm = $this->dof->modlib('filestorage')->link_files($userdata[$userkey]);
                            }
                        }
                        $result[$id] = $item; 
                        break;
                    default:
                        break;
                }
            }
        }
        return $result;
    }
    
    /**
     * Подтвердить элемент пользовательского достижения
     *
     * @param int $id - ID пользовательского достижения
     *
     * @param array $options - Дополнительные опции, определяющие подтверждающий элемент
     *              ['additionalid'] - Дополнительный параметр INTEGER
     *              ['additionalname'] - Дополнительный параметр STRING
     *              ['additionalid2'] - Дополнительный параметр INTEGER
     *              ['confirmall] - флаг подтвержденя всех критериев
     *
     * @return bool - Результат
     */
    public function moderate_confirm($id, $options = []) 
    {
        // Получаем достижение
        $instance = $this->get($id);
        if ( empty($instance) )
        {// Достижение не найдено
            return false;
        }
        
        // Получение объекта класса шаблона достижения
        $achievement = $this->dof->storage('achievements')->object($instance->achievementid);
        if ( empty($achievement) )
        {
            return false;
        }
        if ( $achievement->is_autocompletion() )
        {
            // автоматическая модерация
            return false;
        }
        
        $achievementrecord = $this->dof->storage('achievements')->get($instance->achievementid);
        // данные достижения
        $options['achievement_data'] = unserialize($achievementrecord->data);
        // Формирование данных пользователя по достижению
        $userdata = unserialize($instance->data);
        // Подтверждение данных
        $newuserdata = $achievement->moderate_confirm($userdata, $options);
        if ( $newuserdata === false )
        {
            return false;
        }
        $update = new stdClass();
        $update->id = $instance->id;
        $update->userid = $instance->userid;
        $update->achievementid = $instance->achievementid;
        $update->data = serialize($newuserdata);
        $update->timechecked = time();
        return $this->save($update);
    }
    
    /**
     * Вычислить баллы по достижению
     * 
     * @param int $id - $id пользовательского достижения
     * 
     * @return float|bool - Результат рассчета или false
     */
    public function calculate_userpoints($id)
    {
        // Получение пользовательского достижения
        $instance = $this->get($id);
        if ( empty($instance) )
        {// Достижение не получено
            return false;
        }
        
        $person = $this->dof->storage('persons')->get($instance->userid);
        
        // Получение объекта класса шаблона
        $achievement = $this->dof->storage('achievements')->object($instance->achievementid);
        if ( empty($achievement) )
        {// Объект получен
            return false;
        }
        
        // Получение данных пользователя
        $userdata = unserialize($instance->data);
        // Подсчет баллов
        $userpoints = $achievement->instance_calculate_userpoints($userdata, ['userid' => $person->mdluser, 'instance' => $instance]);
        
        $instance = new stdClass();
        $instance->id = $id;
        $instance->userpoints = $userpoints;
        $this->update($instance);
        
        return $userpoints;
    }
    
    /**
     * Произвести действия над пользовательским достижением перед сохранением
     * 
     * @param object $newinstance - Объект пользовательского достижения, готового к обновлению
     * @param object $oldinstance - Объект пользовательского достижения до обновления
     * 
     * @return object|bool $newinstance - Отредактированный объект пользовательского достижения 
     *                                    или false в случае ошибки
     */
    public function beforesave_process($newinstance, $oldinstance = null)
    {
        // Проверка входных данных
        if ( ! is_object($newinstance) && ( ! is_null($oldinstance) && ! is_object($oldinstance)) )
        {// Неправильные входные данные
            return false;
        }
    
        if ( ! isset($newinstance->achievementid) )
        {// Шаблон не установлен
            if ( isset($oldinstance->achievementid) )
            {// Шаблон найден
                $aid = $oldinstance->achievementid;
            } else
            {// Шаблон не найден
                return $newinstance;
            }
        } else 
        {// Шаблон устновлен
            $aid = $newinstance->achievementid;
        }
        
        // Получение объекта класса шаблона
        $achievement = $this->dof->storage('achievements')->object($aid);
        if ( empty($achievement) )
        {// Объект не получен
            return false;
        }

        // Предобработка 
        return $achievement->beforesave_process($newinstance, $oldinstance);
    }
    
    /**
     * Проверить на необходимость модерации достижения
     * 
     * @param int $instanceid - ID достижения
     * 
     * @return bool - TRUE - Достижение не требует модерации
     *                FALSE - Достижение требует модерации
     *                null - Ошибка
     */
    public function is_completely_confirmed($instanceid)
    {
        // Получить достижение
        $instance = $this->get($instanceid);
        if ( empty($instance) )
        {
            return null;
        }
        
        // Получение объекта класса шаблона
        $achievement = $this->dof->storage('achievements')->object($instance->achievementid);
        if ( empty($achievement) )
        {// Объект не получен
            return null;
        }
        
        // Получение достижения
        $instance = $this->get($instanceid);
        $data = unserialize($instance->data);
        
        return $achievement->is_completely_confirmed($data, $instance);
    }
    
    /**
     * Действия над достижением перед его полным подтверждением
     *
     * @param int $instanceid - ID достижения
     *
     * @return void
     */
    public function before_completely_confirmed_process($instanceid)
    {
        // Получить достижение
        $instance = $this->get($instanceid);
        if ( empty($instance) )
        {
            return;
        }
    
        // Получение объекта класса шаблона
        $achievement = $this->dof->storage('achievements')->object($instance->achievementid);
        if ( empty($achievement) )
        {// Объект не получен
            return;
        }
    
        // Получение достижения
        $instance = $this->get($instanceid);
        $data = unserialize($instance->data);
        $data['userid'] = $instance->userid;
    
        $achievement->before_completely_confirmed_process($data);
    }
    
    /**
     * Получить информацию о рейтинге пользователя
     *
     * @param int $personid - ID персоны
     * @params array $options - Дополнительные опции 
     *      ['status'] - Массив статусов достижений, по которым следует делать рассчет 
     *
     * @return stdClass $info - Информация о рейтинге пользователя
     */
    public function get_userrating_info($personid, $options = [])
    {
        if ( empty($personid) )
        {// Персона не передана
            return null;
        }
        $where = ['1=1'];
        if ( isset($options['status']) )
        {
            foreach ( $options['status'] as &$status )
            {
                $status = '\''.$status.'\'';
            }
            $statuses = implode(',', $options['status']);
            $where[] = 'ai.status IN ('.$statuses.')';
        }
        if ( isset($options['achievementids']) )
        {
            foreach ( $options['achievementids'] as &$achievement )
            {
                $achievement = '\''.$achievement.'\'';
            }
            $aids = implode(',', $options['achievementids']);
            $where[] = 'ai.achievementid IN ('.$aids.')';
        }
        if ( empty($options['alluserpoints']) )
        {
            $where[]='ac.affectrating=1';
        }

        $sql = 'SELECT t1.userid, SUM(t1.userpoints) AS points 
            FROM 
                (
                 SELECT ai.id, ai.userid, ai.achievementid, ai.userpoints
                 FROM '.$this->prefix().'block_dof_s_achievementins AS ai
                 LEFT JOIN '.$this->prefix().'block_dof_s_achievements AS a ON a.id=ai.achievementid
                 LEFT JOIN '.$this->prefix().'block_dof_s_achievementcats AS ac ON ac.id=a.catid 
                 WHERE ' . implode(' AND ', $where) . '
                 GROUP BY ai.userid, ai.achievementid, ai.userpoints, ai.id
                ) AS t1 
            GROUP BY t1.userid 
            ORDER BY points DESC, userid ASC';
        
        $result = $this->get_records_sql($sql);
        
        if ( empty($result) )
        {// Рейтинг не удалось сформировать
            $object = new stdClass();
            $object->userid = $personid;
            $object->rating = 1;
            $object->points = 0;
            return $object;
        } else
        {
            $rating = 1;
            foreach($result as $key => $val)
            {
                $result[$key]->rating = $rating;
                $rating++;
            }
        }
        
        if ( isset($result[$personid]) )
        {// Рейтинг пользователя найден
            return $result[$personid];
        } else 
        {// Пользователь вне рейтинга
            $object = array_pop($result);
            $object->userid = $personid;
            $object->rating = $object->rating + 1;
            $object->points = 0;
            return $object;
        }
    }
    
    /**
     * Получить информацию о рейтинге пользователя
     *
     * @param int $limitfrom - Смещение
     * @param int $limitnum - Число записей
     * @param array $options - Дополнительные опции 
     *      ['status'] - Массив статусов достижений, по которым следует делать рассчет 
     *      
     * @return array - Массив пользователей
     */
    public function get_rating($limitfrom = 0, $limitnum = 50, $options = [])
    {
        // Нормализация
        if ( $limitfrom < 0 )
        {
            $limitfrom = 0;
        }
        if ( ! is_null($limitnum) && $limitnum < 1 )
        {
            $limitnum = 50;
        }
        $where = ['1=1'];
        if ( isset($options['status']) )
        {
            foreach ( $options['status'] as &$status )
            {
                $status = "'".$status."'";
            }
            $statuses = implode(',', $options['status']);
            $where[] = 'ai.status IN ('.$statuses.')';
        }
        if ( isset($options['persons']) )
        {// Фильтрация по персонам
            if ( empty($options['persons']) )
            {
                $personids = '0';
            } else
            {
                $ids = array_keys($options['persons']);
                $personids = implode(',', $ids);
            }
            
            $where[] = 'ai.userid IN ('.$personids.')';
        }
        if ( isset($options['achievementins']) )
        {// Фильтрация по достижениям
            if ( empty($options['achievementins']) )
            {
                $achievementins = '0';
            } else 
            {
                $ids = array_keys($options['achievementins']);
                $achievementins = implode(',', $ids);
            }
            $where[] = 'ai.id IN ('.$achievementins.')';
        }
        if ( isset($options['achievementcats']) )
        {// Фильтрация по категориям
            if ( is_array($options['achievementcats']) )
            {
                $ids = array_keys($options['achievementcats']);
                $where[] = 'ac.id IN ('.implode(',', $ids).')';
            }
        }
        $where[]='ac.affectrating=1';
        
        $sql = 'SELECT t1.userid, SUM(t1.userpoints) AS points
            FROM
                (
                 SELECT ai.id, ai.userid, ai.achievementid, ai.userpoints
                 FROM '.$this->prefix().'block_dof_s_achievementins AS ai
                 LEFT JOIN '.$this->prefix().'block_dof_s_achievements AS a ON a.id=ai.achievementid
                 LEFT JOIN '.$this->prefix().'block_dof_s_achievementcats AS ac ON ac.id=a.catid 
                 WHERE ' . implode(' AND ', $where) . '
                 GROUP BY ai.userid, ai.achievementid, ai.userpoints, ai.id
                ) AS t1
            GROUP BY t1.userid
            ORDER BY points DESC, userid ASC';
        
        $result = $this->get_records_sql($sql);
        
        if( ! empty($result) )
        {// Отфильтруем и покажем тех, кто участвует в рейтинге
            $rating = 1;
            foreach($result as $key => $val)
            {
                $check = $this->dof->storage('cov')->get_option(
                        'im',
                        'achievements',
                        $key,
                        'rating_included',
                        null,
                        ['emptyreturn' => 'not_set']
                        );
                if ( empty($check) && ($check !== 'not_set') )
                {
                    unset($result[$key]);
                } else
                {
                    $result[$key]->rating = $rating;
                    $rating++;
                }
            }
        }
        
        $result = array_slice($result, $limitfrom, $limitnum, true);
        return $result;
    }
    
    /**
     * Получить информацию о количестве пользователей в рейтинге
     * @params array $options - Дополнительные опции 
     *      ['status']  - Массив статусов достижений, по которым следует делать рассчет 
     *      ['persons'] - Массив персон, по которым следует вести подсчет
     *      ['achievementins'] - Массив достижений, которые следует учесть
     *      
     * @return int - Общее число пользователей
     */
    public function get_rating_count($options)
    {
        
        $where = '';
        if ( isset($options['status']) )
        {
            foreach ( $options['status'] as &$status )
            {
                $status = "'".$status."'";
            }
            $statuses = implode(',', $options['status']);
            $where .= ' WHERE status IN ('.$statuses.') ';
        }
        if ( isset($options['persons']) )
        {// Фильтрация по персонам
        if ( empty($options['persons']) )
        {
            $personids = '0';
        } else
        {
            $ids = array_keys($options['persons']);
            $personids = implode(',', $ids);
        }
        
        if ( empty($where) )
        {
            $where .= ' WHERE ';
        } else
        {
            $where .= ' AND ';
        }
        $where .= ' userid IN ('.$personids.') ';
        }
        if ( isset($options['achievementins']) )
        {// Фильтрация по достижениям
        if ( empty($options['achievementins']) )
        {
            $achievementins = '0';
        } else
        {
            $ids = array_keys($options['achievementins']);
            $achievementins = implode(',', $ids);
        }
        
        if ( empty($where) )
        {
            $where .= ' WHERE ';
        } else
        {
            $where .= ' AND ';
        }
        $where .= ' id IN ('.$achievementins.') ';
        }
        
        $sql = 'SELECT t1.userid, SUM(t1.userpoints) AS points
            FROM
                (
                 SELECT id, userid, achievementid, userpoints
                 FROM mdl_block_dof_s_achievementins
                 ' . $where . '
                 GROUP BY userid, achievementid, userpoints, id
                ) AS t1
            GROUP BY t1.userid
            ORDER BY points DESC, userid ASC';
        
        $result = $this->get_records_sql($sql);
        
        if( ! empty($result) )
        {// Отфильтруем и покажем тех, кто участвует в рейтинге
            $rating = 1;
            foreach($result as $key => $val)
            {
                $check = $this->dof->storage('cov')->get_option(
                        'im',
                        'achievements',
                        $key,
                        'rating_included',
                        null,
                        ['emptyreturn' => 'not_set']
                        );
                if ( empty($check) && ($check !== 'not_set') )
                {
                    unset($result[$key]);
                } else
                {
                    $result[$key]->rating = $rating;
                    $rating++;
                }
            }
        }
        
        $count = count($result);
        return $count;
    }
    
    
    /**
     * Получить форматированные пользовательские данные по достижению
     * 
     * @param int $id - ID экземпляра пользовательского достижения
     * 
     * @return object|string - Результат
     */
    public function get_formatted_data($id, $options = [])
    {
        // Получение достижения
        $instance = $this->get($id);
        if ( empty($instance) )
        {// Достижение не найдено
            return null;
        }
        $class = $this->dof->storage('achievements')->object($instance->achievementid, $options);
        $data = unserialize($instance->data);
        $data['achievementinsid'] = $instance->id;
        $data['userid'] = $this->dof->storage('persons')->get($instance->userid)->mdluser;
        
        return $class->get_formatted_user_data($data);
    }
    
    /**
     * Отфильтровать пользовательские достижения
     * 
     * @param array $filterfields - Массив критериев для фильтрации
     *      ['category'] - Массив ID разделов, по которым необходима фильтрация
     *      
     * @return object|string - Результат
     */
    public function get_filtered_data($filterfields = [])
    {
        $where = ['1=1'];
        $param = [];
        if ( isset($filterfields['category']) )
        {
            $categories = [];
            foreach($filterfields['category'] as $category)
            {
                //в условии должны фигурировать сами переданные категории
                $categories[] = $category;
                //и их потомки
                $children = $this->dof->storage('achievementcats')->get_categories($category);
                if( !empty($children))
                {
                    foreach($children as $child)
                    {
                        $categories[] = $child->id;
                    }
                }
            }
            if(!empty($categories))
            {
                $where[] = ' a.catid IN ('.implode(',', $categories).') ';
            }
        }
        if ( isset($filterfields['personids']) && ! empty($filterfields['personids']) )
        {
            $where[] = ' i.userid IN ('.implode(',', $filterfields['personids']).') ';
        }
        if ( isset($filterfields['statuses']) && ! empty($filterfields['statuses']) )
        {
            $string = '';
            $string .= 'i.status IN (\''. array_shift($filterfields['statuses']) .'\'';
            if ( ! empty($filterfields['statuses']) )
            {
                foreach ( $filterfields['statuses'] as $status )
                {
                    $string .= ',\'' . $status . '\'';
                }
            }
            $string .= ')';
            $where[] = $string;
        }
        if ( isset($filterfields['createdate_from']) )
        {
            $where[] = ' i.timecreated >= :createdate_from ';
            $param['createdate_from'] = $filterfields['createdate_from'];
        }
        if ( isset($filterfields['createdate_to']) )
        {
            $where[] = ' i.timecreated <= :createdate_to ';
            $param['createdate_to'] = $filterfields['createdate_to'];
        }
        
        $sql = 'SELECT 
                    i.id, a.catid, a.type, a.createdate, a.changedate, a.points, a.status,
                    i.userid, i.moderatorid, i.timecreated, i.timechecked, i.userpoints, i.status
                FROM
	                {block_dof_s_achievementins} as i LEFT JOIN {block_dof_s_achievements} as a 
                    ON a.id = i.achievementid
                WHERE '.implode(' AND ', $where).' 
               ';
        $result = $this->get_records_sql($sql, $param);

        return $result;
    }
    
    /**
     * Получить достижения, принадлежащие указанному шаблону
     * 
     * @param int $achievementid - ID шаблона, по которому требуется получить достижения
     * @param int $personid - Дополнительная фильтрация по персоне
     * @param array $options - Дополнительные опции
     *      string 'metastatus' - Дополнительная фильтрация по Метастатусу
     * 
     * @return array - Массив достижений
     */
    public function get_achievementins($achievementid = 0, $personid = 0, $options = [])
    {
        $achievementid = (int)$achievementid;
        $params = [];
        // Параметры фильтрации достижений
        if( $achievementid > 0 )
        {
            $params = [
                'achievementid' => $achievementid
            ];
        }
        
        if ( (int)$personid )
        {// Указана фильтрация по персоне
            $params['userid'] = (int)$personid;
        }
        
        if ( isset($options['metastatus']) )
        {// Указана дополнительная фильтрация по метастатусу
            
            // Получение списка статусов
            $statuses = (array)$this->dof->workflow('achievementins')->
                get_meta_list((string)$options['metastatus']);
            $statuses = array_keys($statuses);
            
            $params['status'] = $statuses;
        }
        
        if ( isset($options['personids']) && is_array($options['personids']) )
        {// Указана фильтрация по нескольким персонам
            $params['userid'] = $options['personids'];
        }
        
        // Получение пользовательских достижений
        return $this->dof->storage('achievementins')->get_records($params);
    }
    
    /**
     * Получить достижения, принадлежащие указанному шаблону
     * 
     * @param int $achievementid - ID шаблона, по которому требуется получить достижения
     * @param int $personid - Дополнительная фильтрация по персоне
     * @param array $options - Дополнительные опции
     *      string 'metastatus' - Дополнительная фильтрация по Метастатусу
     * 
     * @return array - Массив достижений
     */
    public function get_achievementins_by_type($achievementtype, $personid = 0, $options = [])
    {
        // Параметры фильтрации достижений
        $statuses = (array)$this->dof->workflow('achievements')->get_meta_list('real');
        $achievements = (array)$this->dof->storage('achievements')->get_records(
            [
                'type' => (string)$achievementtype,
                'status' => array_keys($statuses)
            ]
        );
        $params = [
            'achievementid' => array_keys($achievements)
        ];
        
        if ( (int)$userid )
        {// Указана фильтрация по персоне
            $params['userid'] = (int)$personid;
        }
        
        if ( isset($options['metastatus']) )
        {// Указана дополнительная фильтрация по метастатусу
            
            // Получение списка статусов
            $statuses = (array)$this->dof->workflow('achievementins')->
                get_meta_list((string)$options['metastatus']);
            $statuses = array_keys($statuses);
            
            $params['status'] = $statuses;
        }
        
        // Получение пользовательских достижений
        return $this->dof->storage('achievementins')->get_records($params);
    }
    
    /**
     * Поддержка пользовательским достижением ручного удаления
     * 
     * @param int|stdClass $achievementin
     * 
     * @return bool|null - true, Если достижение поддерживает ручное удаление,
     *                     false, Если достижение не поддреживает ручное удаление
     *                     и null в случае ошибки
     */
    public function can_manual_delete($achievementin)
    {
        // Нормализация
        if ( ! is_object($achievementin) )
        {
            // Получение достижения
            $achievementin = $this->get((int)$achievementin);
        }

        if ( ! empty($achievementin->achievementid) )
        {// Шаблон достижения определен
            
            // Получение класса шаблона
            $achievement = $this->dof->storage('achievements')->object($achievementin->achievementid);
            if ( ! empty($achievement) )
            {
                return $achievement->manual_delete();
            }
        }
        return true;
    }
    
    /**
     * Удаление пустых достижений
     * @param int $intvar идентификатор шаблона достижений
     * @param mixed $mixedvar дополнительные параметры
     */
    public function clean_empty_achievementins($intvar, $mixedvar = null)
    {
        $intvar = (int)$intvar;
        $achievementins = $this->get_achievementins($intvar);
        if( ! empty($achievementins) )
        {
            foreach($achievementins as $achievementin)
            {
                // Получение класса шаблона
                $achievement = $this->dof->storage('achievements')->object($achievementin->achievementid);
                if ( ! empty($achievement) && $achievement->is_empty_userdata($achievementin) )
                {
                    $this->dof->workflow('achievementins')->change($achievementin->id, 'deleted');
                }
            }
        }
    }
    
    /**
     * Одобрить/Отклонить цель
     *
     * @param int|object $achievementinorid - идентификатор цели (достижения) или уже готовый объект
     * @param bool $decision
     * 
     * @return bool
     *
     */
    public function approve_the_goal($achievementinorid, $decision = true)
    {
        $result = false;
        if( ! is_object($achievementinorid) )
        {
            $achievementin = $this->get(intval($achievementinorid));
        } else
        {
            $achievementin = $achievementinorid;
        }
        
        if( empty($achievementin) )
        {// достижение не определено, операцию выполнить не удастся
            // не найдена цель
            $this->dof->messages->add(
                $this->dof->get_string('goal_not_found', 'achievements', null, 'storage'),
                'error'
            );
            return $result;
        }
        
        if( ! $this->is_access('approve_goal', $achievementin->id) )
        {
            // нет полномочий одобрять цель
            $this->dof->messages->add(
                $this->dof->get_string('goal_approve_access_denied', 'achievements', null, 'storage'),
                'error'
            );
            return $result;
        }
        
        if ( $decision ) 
        {
            // Получение объекта класса шаблона
            $achievement = $this->dof->storage('achievements')->object($achievementin->achievementid);
            if ( empty($achievement) )
            {// не получен
                return $result;
            }
            
            
            // перевод статуса в ожидает подтверждение выполнения
            $result = $this->dof->workflow('achievementins')->change($achievementin->id, 'wait_completion');
            if ( $result )
            {
                // отправка уведомления владельцу цели
                $this->send_message_goal_approve(true, $achievementin);
            }
            
            
            return $result;
        } else 
        {
            $result = $this->dof->workflow('achievementins')->change($achievementin->id, 'fail_approve');
            if ( $result )
            {
                // отправка уведомления владельцу цели
                $this->send_message_goal_approve(false, $achievementin);
            }
            
            return $result;
        }
    }
    
    /**
     * Зарегистрировать достижение цели
     * 
     * @param int|object $achievementinorid - идентификатор цели (достижения) или уже готовый объект
     * @return boolean успешность исполнения операции
     * 
     */
    public function achieve_the_goal($achievementinorid)
    {
        $result = false;
        
        if( ! is_object($achievementinorid) )
        {
            $achievementin = $this->get(intval($achievementinorid));
        } else
        {
            $achievementin = $achievementinorid;
        }
        
        if( empty($achievementin) )
        {// достижение не определено, операцию выполнить не удастся
            $this->dof->messages->add(
                $this->dof->get_string('achievementin_not_found', 'achievementins', null, 'storage'),
                'error'
            );
            return $result;
        }
        $ach = $this->dof->storage('achievements')->get($achievementin->achievementid);
        if ( empty($ach) )
        {
            return false;
        }
        // получение объекта класса шаблона достижения
        $achobj = $this->dof->storage('achievements')->object($ach->id);
        if ( $achobj->is_autocompletion() )
        {
            // автоматическое подтверждение
            return false;
        }
        
        $cat = $this->dof->storage('achievementcats')->get($ach->catid);
        if ( empty($cat) )
        {
            return false;
        }
        
        if( ! $this->is_access('achieve_goal', $achievementin->id) )
        {
            // нет полномочий подтверждать выполнение цели
            $this->dof->messages->add(
                $this->dof->get_string('goal_achieve_access_denied', 'achievementins', null, 'storage'),
                'error'
            );
            return $result;
        }
        
        // подтвердим выполнение цели 
        $result = $this->update_achievementin_state($achievementin, false);
        
        // статус при предыдущем действии изменится, надо обновить данные о записи
        $achievementin = $this->get($achievementin->id);
        
        // проверим, может ли пользователь модерировать достижение
        $moderateaccess = array_key_exists($achievementin->status, $this->dof->workflow('achievementins')->get_meta_list('achievement_real')) &&
            ($this->dof->im('achievements')->is_access('achievementins/moderate', $achievementin->id) ||
                $this->dof->im('achievements')->is_access('achievementins/moderate_except_myself', $achievementin->id) ||
                $this->dof->im('achievements')->is_access('achievementins/moderate_category', $cat->id) ||
                    $this->dof->im('achievements')->is_access('moderation', $cat->departmentid));
        if ( $moderateaccess  )
        {
            // есть право на модерацию достижения, подтвердим все критерии
            $result = $result && $this->dof->storage('achievementins')->moderate_confirm($achievementin->id, ['confirmall' => true]);
        } else 
        {
            $this->send_message_new_achievementin($achievementin);
        }
        
        return $result;
    }
    
    /**
     * Обновить состояние достижения, его статус на основе данных
     *
     * @param int|object $achievementinorid - идентификатор цели (достижения) или уже готовый объект
     * 
     * @return boolean успешность исполнения операции
     *
     */
    public function update_achievementin_state($achievementinorid, $notify = true)
    {
        if( ! is_object($achievementinorid) )
        {
            $achievementin = $this->get(intval($achievementinorid));
        } else
        {
            $achievementin = $achievementinorid;
        }
        
        if( empty($achievementin) )
        {// достижение не определено, операцию выполнить не удастся
            $this->dof->messages->add(
                $this->dof->get_string('achievementin_not_found', 'achievements', null, 'storage'),
                'error'
            );
            return false;
        }
        
        // цель превратилась в достижение, удаляем все записи дедлайнов
        $this->process_goal_deadlines($this->dof->storage('achievements')->get($achievementin->achievementid), $achievementin);
        
        if ( $achievementin->status == 'suspend' )
        {
            $this->dof->workflow('achievementins')->init($achievementin->id);
        }
        
        // Проверка на полное подтверждение достижения
        $isconfirm = $this->is_completely_confirmed($achievementin->id);
        
        if ( $isconfirm === true )
        {// Полностью подтвержден
            $this->calculate_userpoints($achievementin->id);
            $this->before_completely_confirmed_process($achievementin->id);
            return $this->dof->workflow('achievementins')->change($achievementin->id, 'available');
        }
        
        if ( $isconfirm === false )
        {// Не подтверждено до конца
            $this->calculate_userpoints($achievementin->id);
            $res = $this->dof->workflow('achievementins')->change($achievementin->id, 'notavailable');
            if ( $res && $notify )
            {
                // отправка уведомления о новом неподтвержденном достижении/неодобренной цели
                $this->send_message_new_achievementin($this->get($achievementin->id));
            }
            return $res;
        }
        
        return false;
    }
    
    /**
     * Инициализация статуса цели
     * 
     * @param int|object $achievementinorid
     * 
     * @return boolean
     */
    public function init_goal($achievementinorid)
    {
        if( ! is_object($achievementinorid) )
        {
            $achievementin = $this->get(intval($achievementinorid));
        } else
        {
            $achievementin = $achievementinorid;
        }
        
        if( empty($achievementin) )
        {// достижение не определено, операцию выполнить не удастся
            $this->dof->messages->add(
                $this->dof->get_string('achievementin_not_found', 'achievements', null, 'storage'),
                'error'
            );
            return false;
        }
        
        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
        if( empty($achievement) )
        {
            // шаблон достижения не найден
            $this->dof->messages->add(
                $this->dof->get_string('achievement_not_found', 'achievements', null, 'storage'),
                'error'
            );
            return false;
        }
        
        if( $this->dof->storage('achievements')->is_approval_required($achievement->scenario) )
        {// согласно настроенному сценарию шаблона, начальный статус - ожидается одобрение цели
            $res =  $this->dof->workflow('achievementins')->change(
                $achievementin->id, 
                'wait_approval'
            );
            if ( $res )
            {
                // отправка уведомления о новом неподтвержденном достижении/неодобренной цели
                $this->send_message_new_achievementin($this->get($achievementin->id));
            }
            return $res;
        } else
        {// согласно настроенному сценарию шаблона, начальный статус - ожидается достижение цели
            return $this->dof->workflow('achievementins')->change(
                $achievementin->id, 
                'wait_completion'
            );
        }
    }
    
    /**
     * обработка дедлайна
     * 
     * @param string $code
     * @param int $achievementinid
     * 
     * @return void
     */
    public function storage_deadline_process($code, $achievementinid)
    {
        $info = explode('_', $code);
        if ( empty($code[0]) || empty($code[1]) )
        {
            return true;
        }
        $achievementin = $this->get($achievementinid);
        if ( empty($achievementin) )
        {
            return true;
        }
        
        return $this->process_message($code, $info[1], $achievementin);
    }
    
    /**
     * отправка модераторам уведомление о том, что добавлена новая цель, требующая одобрения/достижение, требущее модерации
     *
     * @param stdClass $achievementin
     *
     * @return bool
     */
    protected function send_message_new_achievementin(stdClass $achievementin)
    {
        if ( $achievementin->status == 'notavailable' )
        {
            $type = 'achievement';
        } elseif ( $achievementin->status == 'wait_approval' )
        {
            $type = 'goal';
        } else
        {
            return false;
        }
        
        // Массив подразделений, где будет осуществляться поиск персон для отправки уведомлений
        $departments = [];
        
        // получение шаблона
        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
        $data = unserialize($achievement->notificationdata);
        if ( empty($data['stat_promptly']) )
        {
            return true;
        }
        // получение категории
        $cat = $this->dof->storage('achievementcats')->get($achievement->catid);
        if ( empty($cat) )
        {
            return true;
        }
        $owner = $this->dof->storage('persons')->get($achievementin->userid);
        if ( empty($owner->mdluser) )
        {
            return true;
        }
        // Кладем подразделение персоны в список
        $departments[] = $owner->departmentid;
        // Получаем должностные назначения персоны
        $appointments = $this->dof->storage('appointments')->get_appointment_by_persons($owner->id);
        if( ! empty($appointments) )
        {
            foreach($appointments as $appointment)
            {
                if( ! in_array($appointment->departmentid, $departments) )
                {// Добавляем подразделения, где лежат должностные назначения в список
                    $departments[] = $appointment->departmentid;
                }
            }
        }
        
        // получение информации по сайту
        $sitename = $this->dof->modlib('ama')->course(false)->get_site()->fullname;
        
        // формирование баового сообщения
        $message = new \core\message\message();
        $message->subject = $this->dof->get_string("message_subject_stat_promptly_{$type}", 'achievementins', null, 'storage');
        $message->fullmessageformat = FORMAT_HTML;
        
        // получение конфига с текстом уведомления
        $configtext = $this->dof->storage('config')->get_config_value("notification_stat_promptly_{$type}", 'storage', 'achievementins', $cat->departmentid);
        
        // формирование строк
        $strings = [
            '{STUDENTFULLNAME}' => $this->dof->storage('persons')->get_fullname($owner->id),
            '{SITENAME}' => $sitename,
            '{ACHIEVEMENTNAME}' => $achievement->name,
            '{URL}' => $this->dof->url_im('achievements', '/my.php', ['personid' => $owner->id]),
        ];
        
        $recievers = [];
        foreach($departments as $departmentid)
        {// Ищем персон для отправки уведомлений во всех найденных подразделениях
            // конфиг, определяющий получателей
            $conf = $this->dof->storage('config')->get_config_value('notificate_onlypersondep_moderators', 'storage', 'achievementins', $departmentid);
            if ( $type == 'achievement' )
            {
                if ( ! empty($conf) )
                {
                    $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'achievementins/moderate', $departmentid),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'moderation', $departmentid),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'achievementins/moderate_category', $departmentid, $cat->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('im', 'achievements', 'achievementins/moderate_except_myself', $departmentid));
                } else
                {
                    $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievementins/moderate', $departmentid),
                        $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'moderation', $departmentid),
                        $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievementins/moderate_category', $departmentid, $cat->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code('im', 'achievements', 'achievementins/moderate_except_myself', $departmentid));
                }
            } else
            {
                if ( ! empty($conf) )
                {
                    $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('storage', 'achievementins', 'approve_goal_by_template', $departmentid, $achievementin->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('storage', 'achievementins', 'approve_goal_to_person', $departmentid, $owner->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('storage', 'achievementins', 'approve_goal_by_template/except_myself', $departmentid, $achievementin->id));
                } else
                {
                    $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code('storage', 'achievementins', 'approve_goal_by_template', $departmentid, $achievementin->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code('storage', 'achievementins', 'approve_goal_to_person', $departmentid, $owner->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code('storage', 'achievementins', 'approve_goal_by_template/except_myself', $departmentid, $achievementin->id));
                }
            }
        }
        
        // уникальные получатели
        $uniquerecievers = [];
        if ( ! empty($recievers) )
        {
            foreach ( $recievers as $reciever )
            {
                $uniquerecievers[$reciever->id] = $reciever;
            }
        }
        // удалим владельца достижения/цели
        unset($uniquerecievers[$achievementin->userid]);
        foreach ( $uniquerecievers as $reciever )
        {
            $message->smallmessage = str_replace(array_merge(array_keys($strings),['{USERFULLNAME}']), array_merge(array_values($strings), [$this->dof->storage('persons')->get_fullname($reciever->id)]), $configtext);
            $message->fullmessage = text_to_html($message->smallmessage, false, false, true);
            $message->fullmessagehtml = $message->fullmessage;
            
            // отправка уведомления
            $this->dof->modlib('messager')->message_send('storage', 'achievementins', 'achievementin_new', $reciever->id, $message);
        }
        
        return true;
    }
    
    /**
     * отправка пользователю уведомление о том, что цель одобрена/отклонена
     * 
     * @param stdClass $achievementin
     * 
     * @return bool
     */
    protected function send_message_goal_approve($decision, stdClass $achievementin)
    {
        if ( $decision )
        {
            // достижение одобрили
            $type = 'approve';
        } else 
        {
            // достижение отклонили
            $type = 'reject';
        }
        
        // получение шаблона
        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
        $data = unserialize($achievement->notificationdata);
        if ( empty($data["user_{$type}"]) )
        {
            return true;
        }
        // получение категории
        $cat = $this->dof->storage('achievementcats')->get($achievement->catid);
        if ( empty($cat) )
        {
            return true;
        }
        $owner = $this->dof->storage('persons')->get($achievementin->userid);
        if ( empty($owner->mdluser) )
        {
            return true;
        }
        
        // получение информации по сайту
        $sitename = $this->dof->modlib('ama')->course(false)->get_site()->fullname;
        
        // формирование баового сообщения
        $message = new \core\message\message();
        $message->subject = $this->dof->get_string("message_subject_goal_{$type}", 'achievementins', null, 'storage');
        $message->fullmessageformat = FORMAT_HTML;
        
        // получение конфига с текстом уведомления
        $configtext = $this->dof->storage('config')->get_config_value("notification_user_{$type}", 'storage', 'achievementins', $cat->departmentid);
        
        // формирование строк
        $strings = [
            '{STUDENTFULLNAME}' => $this->dof->storage('persons')->get_fullname($owner->id),
            '{SITENAME}' => $sitename,
            '{ACHIEVEMENTNAME}' => $achievement->name,
            '{URL}' => $this->dof->url_im('achievements', '/my.php', ['personid' => $achievementin->userid]),
        ];
        
        // отправка владельцу достижения
        $message->smallmessage = str_replace(array_keys($strings), array_values($strings), $configtext);
        $message->fullmessage = text_to_html($message->smallmessage, false, false, true);
        $message->fullmessagehtml = $message->fullmessage;
        
        // отправка уведомления
        $this->dof->modlib('messager')->message_send('storage', 'achievementins', "goal_{$type}", $owner->id, $message);
        
        return true;
    }
    
    /**
     * отправка уведомления по дедлайнам
     * 
     * @param string $code - полный код
     * @param string $torole - user/curator
     * @param stdClass $achievementin
     */
    protected function process_message($code, $torole, stdClass $achievementin)
    {
        // получение шаблона
        $achievement = $this->dof->storage('achievements')->get($achievementin->achievementid);
        if ( empty($achievement) )
        {
            return true;
        }
        // получение категории
        $cat = $this->dof->storage('achievementcats')->get($achievement->catid);
        if ( empty($cat) )
        {
            return true;
        }
        $owner = $this->dof->storage('persons')->get($achievementin->userid);
        if ( empty($owner->mdluser) )
        {
            return true;
        }
        
        // получение информации по сайту
        $sitename = $this->dof->modlib('ama')->course(false)->get_site()->fullname;
        
        // формирование баового сообщения
        $message = new \core\message\message();
        $message->subject = $this->dof->get_string("message_subject_{$code}", 'achievementins', null, 'storage');
        $message->fullmessageformat = FORMAT_HTML;
        
        // получение конфига с текстом уведомления
        $configtext = $this->dof->storage('config')->get_config_value("notification_{$code}", 'storage', 'achievementins', $cat->departmentid);
        
        // формирование строк
        $strings = [
            '{STUDENTFULLNAME}' => $this->dof->storage('persons')->get_fullname($owner->id),
            '{SITENAME}' => $sitename,
            '{ACHIEVEMENTNAME}' => $achievement->name,
            '{URL}' => $this->dof->url_im('achievements', '/my.php', ['personid' => $achievementin->userid]),
            '{DAYS}' => abs(round((time() - $achievementin->goaldeadline)/86400))
        ];
        
        if ( $torole == 'curator' )
        {
            // конфиг, определяющий получателей
            $conf = $this->dof->storage('config')->get_config_value('notificate_onlypersondep_moderators', 'storage', 'achievementins', $owner->departmentid);
            if ( ! empty($conf) )
            {
                $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('storage', 'achievementins', 'approve_goal_by_template', $owner->departmentid, $achievementin->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code_without_hierarchy('storage', 'achievementins', 'approve_goal_to_person', $owner->departmentid, $owner->id));
            } else
            {
                $recievers = array_replace($this->dof->storage('acl')->get_persons_acl_by_code('storage', 'achievementins', 'approve_goal_by_template', $owner->departmentid, $achievementin->id),
                        $this->dof->storage('acl')->get_persons_acl_by_code('storage', 'achievementins', 'approve_goal_to_person', $owner->departmentid, $owner->id));
            }
            
            // уникальные получатели
            $uniquerecievers = [];
            if ( ! empty($recievers) )
            {
                foreach ( $recievers as $reciever )
                {
                    $uniquerecievers[$reciever->id] = $reciever->id;
                }
            }
            
            // куратор - это тот, кто прописан в договоре студента
            $contracts = $this->dof->storage('contracts')->get_records(['status' => 'work', 'studentid' => $owner->id]);
            foreach ( $contracts as $contract )
            {
                if ( ! empty($contract->curatorid) && ! array_key_exists($contract->curatorid, $uniquerecievers) )
                {
                    $uniquerecievers[$contract->curatorid] = $contract->curatorid;
                }
            }
            foreach ( $uniquerecievers as $curatorid )
            {
                $message->smallmessage = str_replace(array_merge(array_keys($strings),['{USERFULLNAME}']), array_merge(array_values($strings), [$this->dof->storage('persons')->get_fullname($curatorid)]), $configtext);
                $message->fullmessage = text_to_html($message->smallmessage, false, false, true);
                $message->fullmessagehtml = $message->fullmessage;
                
                // отправка уведомления
                $this->dof->modlib('messager')->message_send('storage', 'achievementins', 'goal_achieve_failed', $curatorid, $message);
            }
        } else 
        {
            // отправка владельцу достижения
            $message->smallmessage = str_replace(array_keys($strings), array_values($strings), $configtext);
            $message->fullmessage = text_to_html($message->smallmessage, false, false, true);
            $message->fullmessagehtml = $message->fullmessage;
            
            // отправка уведомления
            $this->dof->modlib('messager')->message_send('storage', 'achievementins', 'goal_achieve_failed', $owner->id, $message);
        }
        
        return true;
    }
    
    /**
     * обновление дедлайнов
     * 
     * @param stdClass $achievement
     * 
     * @return void
     */
    public function update_deadlines(stdClass $achievement)
    {
        $achievementins = $this->get_records(
                [
                    'status' => array_keys($this->dof->workflow('achievementins')->get_meta_list('goal_real')),
                    'achievementid' => $achievement->id
                ]);
        foreach ( $achievementins as $achievementin )
        {
            $this->process_goal_deadlines($achievement, $achievementin);
        }
    }
    
    /**
     * добавление дедлайнов цели
     * 
     * @param stdClass $achievement - шаблон достижения
     * @param stdClass $achievementin - достижение
     * 
     * @return true
     */
    public function process_goal_deadlines(stdClass $achievement, stdClass $achievementin)
    {
        // удаление существующих дедлайнов для текущего достижения
        $this->dof->storage('deadline')->delete_records(['plugintype' => 'storage', 'plugincode' => 'achievementins', 'objid' => $achievementin->id]);
        if ( $achievementin->status == 'deleted' )
        {
            // достижение удалено
            return true;
        }
        $templatedata = unserialize($achievement->notificationdata);
        if ( empty($templatedata) )
        {
            // не настроены уведомления у шаблона
            return true;
        }
        
        $goalrealstatuses = $this->dof->workflow('achievementins')->get_meta_list('goal_real');
        if ( ! array_key_exists($achievementin->status, $goalrealstatuses) )
        {
            // невалидный статус
            return true;
        }
        
        // фиксируем текущее время
        $curtime = time();
        
        // базовые данные для записи дедлайна
        $record = new stdClass();
        $record->plugintype = 'storage';
        $record->plugincode = 'achievementins';
        $record->objid = $achievementin->id;
        
        if ( ! empty($templatedata['before_user']) )
        {
            // добавление дедлайна отправки уведомления пользователю за N дней до дедлайна
            $record->code = 'before_user';
            $record->date = $achievementin->goaldeadline - $templatedata['before_user'];
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        if ( ! empty($templatedata['before_curator']) )
        {
            // добавление дедлайна отправки уведомления пользователю за N дней до дедлайна
            $record->code = 'before_curator';
            $record->date = $achievementin->goaldeadline - $templatedata['before_curator'];
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        
        if ( ! empty($templatedata['after_user']) )
        {
            // добавление дедлайна отправки уведомления пользователю через N дней после дедлайна
            $record->code = 'after_user';
            $record->date = $achievementin->goaldeadline + $templatedata['after_user'];
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        if ( ! empty($templatedata['after_curator']) )
        {
            // добавление дедлайна отправки уведомления куратору через N дней после дедлайна
            $record->code = 'after_curator';
            $record->date = $achievementin->goaldeadline + $templatedata['after_curator'];
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        
        if ( ! empty($templatedata['inday_user']) )
        {
            // добавление дедлайна отправки уведомления пользователю в день дедлайна
            $record->code = 'inday_user';
            $record->date = $achievementin->goaldeadline;
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        if ( ! empty($templatedata['inday_curator']) )
        {
            // добавление дедлайна отправки уведомления куратору в день дедлайна
            $record->code = 'inday_curator';
            $record->date = $achievementin->goaldeadline;
            $record->periodic = 0;
            
            $this->dof->storage('deadline')->insert($record);
        }
        
        return true;
    }

    /**
     * Проверка прав обработки целей
     * 
     * @param string $do - код комплексного права
     * @param mixed $goaltemplateorid - объект шаблона цели или его идентификатор
     * @param mixed $goalownerorid - объект владельца цели или его идентификатор
     * @param int $userid - идентификатор пользователя Moodle, совершившего действие, требующее полномочий
     * @param int $depid - идентификатор подразделения, в котором должны быть проверены полномочия
     * 
     * @return boolean
     */
    public function is_access_goal($do, $goaltemplateorid=null, $goalownerorid=null, $userid=null, $depid=null, $blockfailed=false)
    {
        // Определение id персоны, которая совершает действие
        $doerid = (int)$this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Определение будущего владельца объекта
        if( empty($goalownerorid) )
        {// владельца цели не передали, считаем владельцем текущего пользователя
            $goalowner = $this->dof->storage('persons')->get_bu();
            if( ! empty($goalowner->id) )
            {// получен объект с идентификатором, извлечем идентификатор
                $goalownerid = intval($goalowner->id);
            } else
            {// не удалось получить идентификатор
                return false;
            }
        } else
        {
            if( is_object($goalownerorid) )
            {// передали сразу объект
                if( ! empty($goalownerorid->id) )
                {// извлечем идентификатор
                    $goalownerid = intval($goalownerorid->id);
                } else
                {// не удалось получить идентификатор
                    return false;
                }
            } else
            {// передали идентификатор
                $goalownerid = intval($goalownerorid);
            }
        }
        
        // Определение id шаблона цели
        if( is_object($goaltemplateorid) )
        {// передали сразу объект
            if( ! empty($goaltemplateorid->id) )
            {// извлечем идентификатор
                $goaltemplateid = intval($goaltemplateorid->id);
            } else
            {// не удалось получить идентификатор
                return false;
            }
        } else
        {// передали идентификатор
            $goaltemplateid = intval($goaltemplateorid);
        }
        
        // Проверка права использования любой из доступных пользователю разделов
        if ( ! $this->dof->storage('achievementcats')->is_access_use_any($userid, $depid) )
        {// Пользователь не имеет права использовать ни один из разделов достижений
            return false;
        }
        
        if ( $goalownerid == $doerid )
        {// Попытка создать цель для себя
            // Проверка на право создания своей цели
            if ( $this->is_access("{$do}_goal/owner", null, $userid, $depid) )
            {
                return true;
            }
        }
            
        // Проверка права по цели по указанному шаблону 
        if ( $this->is_access("{$do}_goal_by_template", $goaltemplateid, $userid, $depid) )
        {
            return true;
        }
        
        // Проверка права по цели по указанному шаблону для всех, кроме самого себя
        if ( ($goalownerid != $doerid) && $this->is_access("{$do}_goal_by_template/except_myself", $goaltemplateid, $userid, $depid) )
        {
            return true;
        }
        
        // Проверка права по цели для указанного пользователя
        if ( $this->is_access("{$do}_goal_to_person", $goalownerid, $userid, $depid) )
        {
            return true;
        }
        
        if( $blockfailed )
        {
            $do = "{$do}_goal";
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($goaltemplateid){$notice.=" goaltemplateid={$goaltemplateid}";}
            if ($goalownerid){$notice.=" goalownerid={$goalownerid}";}
            $this->dof->print_error('nopermissions','',$notice);
        } else
        {
            return false;
        }
    }
    
    /**
     * Удаление пользовательского достижения
     *
     * @param int $id - ID достижения
     * @param array $options - массив параметров удаления
     *
     * @return array $errors - Массив ошибок, полученных во время удаления достижения
     */
    public function delete_achievementin($id, $options = [] )
    {
        // Результирующий массив ошибок
        $errors = [];
        
        // Проверка доступа
        $access = $this->dof->storage('achievementins')->is_access('delete', $id);
        if ( empty($access) )
        {// Нет доступа к удалению шаблона
            $errors[] = $this->dof->get_string('error_achievementins_deleting_access_error', 'achievements').': ID '.$id;
            return $errors;
        }
        
        // Смена статуса шаблона
        $result = $this->dof->workflow('achievementins')->change($id, 'deleted');
        if ( empty($result) )
        {// Ошибка
            $errors[] = $this->dof->get_string('error_achievementins_deleting_error', 'achievements').': ID '.$id;
        }
        
        return $errors;
    }
 
    /**
     * Перевод достижения в статус ожидания достижения
     * 
     * @param stdClass $achievementin
     * 
     * @return bool
     */
    public function achievementin_return_to_goal(stdClass $achievementins)
    {
        // флаг о том, что достижение было в одном из статусов цели
        if ( ! $this->dof->storage('statushistory')->has_status('storage', 'achievementins', $achievementins->id, array_keys($this->dof->workflow('achievementins')->get_meta_list('goal_real'))) )
        {
            return false;
        }
        
        $ach = $this->dof->storage('achievements')->get($achievementins->achievementid, '*', MUST_EXIST);
        $cat = $this->dof->storage('achievementcats')->get($ach->catid, '*', MUST_EXIST);
        // получение объекта класса шаблона
        $achievementobj = $this->dof->storage('achievements')->object($ach->id);
        
        // получение доступных статусов для достижения
        if ( ($achievementins->status != 'notavailable') || $achievementobj->is_autocompletion() )
        {// статус не поддерживается
            return false;
        }
        
        // проверка прав на модерацию достижения
        $access = ($this->dof->im('achievements')->is_access('achievementins/moderate', $achievementins->id) ||
                $this->dof->im('achievements')->is_access('achievementins/moderate_category', $cat->id) ||
                $this->dof->im('achievements')->is_access('achievementins/moderate_except_myself', $achievementins->id));
        if ( empty($access) )
        {// Доступа нет
            return false;
        }
        
        return $this->dof->workflow('achievementins')->change($achievementins->id, 'wait_completion');
    }
}   
?>