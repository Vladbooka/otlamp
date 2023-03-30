<?php

////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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
 * Класс для работы с синхронизацией оценок of <-> moodle
 *
 * @package    block_dof
 * @subpackage sync_grades
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_grades implements dof_sync
{
    /**
     * @var dof_control $dof - содержит методы ядра деканата
     */
    protected $dof;
    
    /**
     * @var $cfg - массив настроек плагина
     */
    protected $cfg;
    
    /**
     * Конструктор
     * @param dof_control $dof - это $DOF - методы ядра деканата
     */
    public function __construct($dof)
    {
        GLOBAL $CFG;
        $this->dof = $dof;
        require_once($this->dof->plugin_path('modlib', 'journal', '/classes/managers/lessonprocess/lesson.php'));
        require_once ($CFG->libdir . '/gradelib.php');
        require_once($CFG->libdir . '/grade/grade_grade.php');
    }
    
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
        return 2018090300;
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
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'grades';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return [
        ];
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return [
            // отлавливаем событие выставление оценок за КТ
            [
                'plugintype' => 'modlib',
                'plugincode' => 'journal',
                'eventcode' => 'plan_grades_saved'
            ],
        ];
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
     * Получить настройки для плагина
     *
     * @param string $code
     *
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        return [];
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
    
    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     *
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->has_right(
            $acldata->plugintype, 
            $acldata->plugincode, 
            $acldata->code,
            $acldata->userid, 
            $acldata->departmentid, 
            $acldata->objectid
        );
    }
    
    /**
     * Сформировать права доступа для интерфейса
     *
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
        
        return $a;
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
     * Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $id - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
        switch( $gentype.'__'.$gencode.'__'.$eventcode )
        {
            case 'modlib__journal__plan_grades_saved':
                $this->sync_plan_grades($this->dof->storage('plans')->get_record(['id' => $id]));
                
            default: 
                break;
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
        if ( $loan == 2 )
        {
            // синхронизация оценок занятий
            $this->sync_all_plan_grades();
        }
        
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
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }
    
    // **********************************************
    // Собственные методы и переменные
    // **********************************************
    
    /**
     * Лимит записей на один SQL запрос
     * 
     * @var integer
     */
    CONST LIMIT = 50000;
    
    /**
     * Синхронизация оценок занятий с прилинкованными оцениваемыми элементами Moodle
     * 
     * @return void
     */
    public function sync_all_plan_grades()
    {
        $cstreamsstatuses = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
        
        // каждым SQL запросом достаем оп 10к записей
        // чтобы не сломать буфер, если вдруг записей окажется слишком много
        $offsetcstreams = 0;
        while ( $cstreams = $this->dof->storage('cstreams')->get_records(['status' => $cstreamsstatuses], '', '*', $offsetcstreams, self::LIMIT)  )
        {
            foreach ($cstreams as $cstream)
            {
                $this->sync_cstream_plan_grades($cstream);
            }
            $offsetcstreams += self::LIMIT;
        }
    }
    
    /**
     * Синхронизация оценок занятий учебного процесса
     * 
     * @param stdClass $cstream
     * 
     * @return void
     */
    public function sync_cstream_plan_grades($cstream)
    {
        // второй тип оценивания КТ - автоматическая синхронизация с оцениваемым элементом Moodle
        static $gradessynctype = 2;
        static $plansstatuses = null;
        if ( is_null($plansstatuses) )
        {
            $plansstatuses = array_keys($this->dof->workflow('plans')->get_meta_list('active'));
        }
        
        $offsetplans = 0;
        $params = ['status' => $plansstatuses, 'linktype' => 'cstreams', 'linkid' => $cstream->id, 'gradessynctype' => $gradessynctype];
        while ( $plans = $this->dof->storage('plans')->get_records($params, '', '*', $offsetplans, self::LIMIT) )
        {
            foreach ($plans as $plan)
            {
                $this->sync_plan_grades($plan);
            }
            $offsetplans += self::LIMIT;
        }
    }
    
    /**
     * Синхронизация оценок по КТ
     * 
     * @param stdClass $plan
     * 
     * @return void
     */
    public function sync_plan_grades(stdClass $plan)
    {
        static $plansbuffer = [];
        if ( array_key_exists($plan->id, $plansbuffer) )
        {
            return;
        }
        $plansbuffer[$plan->id] = true;
        static $personsbuffer = [];
        
        if ( $plan->linktype != 'cstreams' || $plan->gradessynctype < 1 )
        {
            return;
        }
        if ( empty($plan->mdlgradeitemid) ||
                ! $this->dof->modlib('ama')->grade_item(false)->is_exists($plan->mdlgradeitemid) )
        {
            return;
        }
        $mdlcourseid = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_cstream_mdlcourse($plan->linkid);
        if ( empty($mdlcourseid) )
        {
            return;
        }
        static $cpassedsstatuses = null;
        if ( is_null($cpassedsstatuses) )
        {
            $cpassedsstatuses = array_keys($this->dof->workflow('cpassed')->get_meta_list('active'));
        }
        $cpasseds = $this->dof->storage('cpassed')->get_records(['status' => $cpassedsstatuses, 'cstreamid' => $plan->linkid]);
        if ( empty($cpasseds) )
        {
            return;
        }
        
        static $syncconnections = [];
        if ( ! array_key_exists($plan->id, $syncconnections) )
        {
            // создаем объект синхронизации
            $syncconnections[$plan->id] = $this->dof->storage('sync')->createConnect(
                    'sync',
                    'grades',
                    $plan->id,
                    'modlib',
                    'ama',
                    'ama_grade'
                    );
        }
        
        // результирующий массив оценок за занятие
        $grades = [];
        $planscale = $this->dof->modlib('journal')->get_manager('scale')->get_plan_scale($plan);
        $plangradesconversation = $this->dof->modlib('journal')->get_manager('scale')->get_plan_grades_conversation_options($plan);
        $lesson = new dof_lesson($this->dof, $plan);
        foreach ($cpasseds as $cpassed)
        {
            if ( ! array_key_exists($cpassed->studentid, $personsbuffer) )
            {
                $person = $this->dof->storage('persons')->get_record(['id' => $cpassed->studentid]);
                $personsbuffer[$person->id] = null;
                if ( ! empty($person->mdluser) )
                {
                    $personsbuffer[$person->id] = $person->mdluser;
                }
            }
            if ( is_null($personsbuffer[$cpassed->studentid]) ||
                    $cpassed->begindate > $plan->datetheme + $plan->reldate )
            {
                continue;
            }
            
            // грейд итем
            $amagradeitem = $this->dof->modlib('ama')->grade_item($plan->mdlgradeitemid);
            $gradeitem = $amagradeitem->get();
            
            // грейд пользователя
            $amagrade = $amagradeitem->fetch_user_grade($personsbuffer[$cpassed->studentid]);
            
            // получение записи синхронизации
            $syncrecord = $syncconnections[$plan->id]->getSync(['downid' => $cpassed->id]);
            $action = 'update';
            if ( empty($syncrecord) )
            {
                $action = 'create';
            }
            
            // пока у нас можно выставлять только одну оценку
            // поэтому берем последнюю
            $currentgrade = end($lesson->get_listener_gradedata($cpassed->id)->grades);
            if ( empty($currentgrade) ||
                    ( ! empty($currentgrade) && (($currentgrade->item->estimatedin == 'moodle') || ! strlen($currentgrade->item->grade)) ) ||
                    $plan->gradespriority == 'moodle' )
            {
                if ( ! empty($currentgrade) && 
                        ! strlen($currentgrade->item->grade) && 
                        ! empty($syncrecord->direct) && 
                        $syncrecord->direct == 'up' )
                {
                    // ранее электронный деканат переопределял оценку
                    // сбросим переопределение
                    if ( $amagrade->is_overridden() )
                    {
                        $amagrade->set_overridden(false, true);
                    } 
                    if ( ! $gradeitem->is_overridable_item() )
                    {
                        // если элемент не может физически быть переопределен
                        // то сбросим оценку
                        $amagrade->rawgrade = null;
                        $amagrade->finalgrade = null;
                        $amagrade->update('block_dof');
                    }
                }
                
                // получение процента за оцениваемый элемент
                $percent = $this->dof->modlib('ama')
                    ->course($mdlcourseid)
                    ->grade()
                    ->get_total_grade_percentage(null, $plan->mdlgradeitemid, $personsbuffer[$cpassed->studentid], 2, false);
                if ( $percent === false )
                {
                    if ( ! empty($currentgrade) && 
                            strlen($currentgrade->item->grade) &&
                            $currentgrade->item->estimatedin == 'moodle' )
                    {
                        // если оценка в Moodle отсутствует но ранее через Moodle мы выставляли оценку, то сбросим ее на null
                        $grades[] = [
                            'cpassedid' => $cpassed->id,
                            'grade' => null,
                            'estimatedin' => 'dof',
                            'workingoff' => null
                        ];
                    }
                } else
                {
                    // итоговая оценка за занятие в Moodle
                    $processedgrade = $this->dof->modlib('journal')->get_manager('scale')->bring_grade_to_scale($percent, $planscale, $plangradesconversation);
                    if ( $currentgrade->item->grade == $processedgrade )
                    {
                        // оценка не изменилась
                        continue;
                    }
                    
                    // выставление оценки в журнал при срабатывании одного из условий
                    // 1 - еще нет оценки за занятие
                    // 2 - приоритет оценок у занятия указан Moodle
                    // 3 - последняя выставленная оценка за занятие имеет тип Moodle
                    $gradeinfo = [
                        'cpassedid' => $cpassed->id,
                        'grade' => $processedgrade,
                        'estimatedin' => 'moodle'
                    ];
                    if ( $this->dof->modlib('journal')->get_manager('lessonprocess')->should_set_workingoff($plan, $cpassed) )
                    {
                        // проверка, нужно ли выставлять флаг "отработки"
                        $gradeinfo['workingoff'] = true;
                    }
                    
                    $grades[] = $gradeinfo;
                }
                
                // сохраним информацию о том, что только что произвели
                $syncconnections[$plan->id]->updateDown($amagrade->id, $action, '', $cpassed->id);
            } elseif ( $plan->gradespriority == 'dof' &&
                    $plan->gradesoverride == 1 ) 
            {
                if ( is_null($currentgrade->item->grade) )
                {
                    continue;
                }
                // приоритет у оценок ЭД и при этом включена перезапись оценок в Moodle
                // перезапишем оценку в журнале оценок Moodle
                $newmdlgrade = $this->dof->modlib('journal')
                                            ->get_manager('scale')
                                            ->convert_grade_from_one_scale_to_another(
                                                    $currentgrade->item->grade, 
                                                    $planscale, 
                                                    $gradeitem->grademin, 
                                                    $gradeitem->grademax, 
                                                    $plangradesconversation);
                
                $amagrade = $amagradeitem->fetch_user_grade($personsbuffer[$cpassed->studentid]);
                if ( is_null($newmdlgrade) )
                {
                    // если оценка переопределена, сбросим переопределение
                    if ( $amagrade->is_overridden() )
                    {
                        $amagrade->set_overridden(false, true);
                    }
                    continue;
                }
                
                // сохраняем оценку в Moodle
                $gradeitem->update_final_grade($personsbuffer[$cpassed->studentid], $newmdlgrade, 'block_dof');
                
                // сохраним информацию о том, что только что произвели
                $syncconnections[$plan->id]->updateUp($cpassed->id, $action, '', $amagrade->id);
            }
        }
        
        if ( ! empty($grades) )
        {
            // сохранение оценок, если они есть
            $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->save_students_grades($plan->linkid, $plan, null, $grades);
        }
    }
}

