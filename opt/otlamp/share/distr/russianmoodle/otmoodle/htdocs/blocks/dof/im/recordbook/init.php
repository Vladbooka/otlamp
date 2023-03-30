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
 * Интерфейс зачетной книжки студента. Класс плагина.
 *
 * @package    im
 * @subpackage recordbook
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_recordbook implements dof_plugin_im
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
        // Обновим права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
        return 2017011211;
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
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'im';
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
        return 'recordbook';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                                'ama'          => 2009042900,
                                'templater'    => 2009031600,
                                'nvg'          => 2008060300,
                                'widgets'      => 2009050800
                ],
                'storage' => [
                                'persons'      => 2009060400,
                                'plans'        => 2009060900,
                                'cpgrades'     => 2009060900,
                                'schpresences' => 2009060800,
                                'schevents'    => 2009060800,
                                'cstreams'     => 2009060800,
                                'cpassed'      => 2009060800,
                                'programms'    => 2009040800,
                                'contracts'    => 2009052900,
                                'programmsbcs' => 2009052900,
                                'ages'         => 2009050600,
                                'programmitems'=> 2009060800,
                                'config'       => 2011080900,
                                'departments'  => 2015110500,
                                'acl'          => 2011040504
                ],
                'workflow' => [
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
     *              TRUE - если плагин можно устанавливать
     *              FALSE - если плагин устанавливать нельзя
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
        return [
                'modlib' => [
                                'ama'          => 2009042900,
                                'templater'    => 2009031600,
                                'nvg'          => 2008060300,
                                'widgets'      => 2009050800
                ],
                'storage' => [
                                'persons'      => 2009060400,
                                'plans'        => 2009060900,
                                'cpgrades'     => 2009060900,
                                'schpresences' => 2009060800,
                                'schevents'    => 2009060800,
                                'cstreams'     => 2009060800,
                                'cpassed'      => 2009060800,
                                'programms'    => 2009040800,
                                'contracts'    => 2009052900,
                                'programmsbcs' => 2009052900,
                                'ages'         => 2009050600,
                                'programmitems'=> 2009060800,
                                'config'       => 2011080900,
                                'departments'  => 2015110500,
                                'acl'          => 2011040504
                ],
                'workflow' => [
                ]
        ];
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [
            [
                'plugintype' => 'im',
                'plugincode' => 'my',
                'eventcode' => 'shortinfo'
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
       // Не запускать
       return FALSE;
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
     *              TRUE - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              FALSE - доступ запрещен
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return TRUE;
        } 
              
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            case 'view_recordbook':
                return $this->can_view_recordbook($objid, $personid);
            break;
            // запрошено неизвестное полномочие
            default: $acldata->code = $do;
        }
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return TRUE;
        } 
        return FALSE;
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
     *              TRUE - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              FALSE - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "recordbook/{$do} (block/dof/im/recordbook: {$do})";
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
     * @return bool - TRUE в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {

        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'shortinfo' )
        {
            $sections = [
                [
                    'im'=>$this->code(),
                    'name'=>'list_learning_data',
                    'id'=>$intvar
                ]
            ];
            return $sections;
        }
        return TRUE;
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
     * @return bool - TRUE в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        return TRUE;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - TRUE в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return TRUE;
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
     * Функция получения настроек для плагина
     */
    public function config_default($code = NULL)
    {
        // Плагин включен и используется
        $config = [];
    
        return $config;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /** 
     * Возвращает текст для отображения в блоке на странице dof
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string - html-код содержимого блока
     */
    public function get_block($name, $id = 1)
    {
        $result = '';

        // Инициализируем генератор HTML
        if ( !class_exists('dof_html_writer') )
        {
            $this->dof->modlib('widgets')->html_writer();
        }

        $addvars = [
            'departmentid' => $this->dof->storage('departments')->get_user_default_department()
        ];
        
        switch ($name)
        {
            case 'link':
                $result = dof_html_writer::link(
                    $this->dof->url_im($this->code(),'/index.php'),
                    $this->dof->get_string('page_main_name')
                );
                break;
            default:  
                break;  
        }
        return $result;
    }
    
    
    /** 
     * Возвращает html-код, который отображается внутри секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 0)
    {
        $result = '';
        
        // Инициализируем генератор HTML
        if ( !class_exists('dof_html_writer') )
        {
            $this->dof->modlib('widgets')->html_writer();
        }
        
        switch ($name)
        {
            case 'list_learning_data':
                $result = $this->get_learning_data([
                    'id' => $id,
                    'template' => 'listlearningdata'
                ]);
                break;
            case 'index':  
                $result .= $this->get_learning_data([
                    'id' => $id,
                    'template' => 'studentslist'
                ]);
                break;  
            default:  
                break;  
        }
        return $result;
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
     * Задаем права доступа для объектов
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        /* Базовые права */
        
        return $a;
    }
    
    /**
     * Проверка прав на просмотр дневника
     * 
     * @param int $programmsbcid - id подписки на программу
     * @param int $personid - id проверяемого пользователя
     * 
     * @return bool - Доступ к зачетной книжке
     */
    private function can_view_recordbook($programmsbcid, $personid = NULL)
    {
        // Получение подписки на программу
        $programmsbc = $this->dof->storage('programmsbcs')->get((int)$programmsbcid);
        if ( ! $programmsbc )
        {// Подписка не найдена
            return FALSE;
        }
        // Получение договора на обучение по подписке
        $contract = $this->dof->storage('contracts')->get((int)$programmsbc->contractid);
        if ( ! $contract )
        {// Договор не найден
            return FALSE;
        }
        if ( $personid == $contract->studentid || $personid == $contract->clientid )
        {// Владелец зачетной книжки
            return TRUE;
        }
        return FALSE;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    

    /**
     * Отображение договоров и подписок клиента
     * 
     * @param array $options - id = id студента, template = название шаблона для отображения
     * @return string
     */
    private function get_learning_data( $options = [] )
    {
        require_once ($this->dof->plugin_path('im', 'recordbook', '/classes/studentslist.php'));
        $result = '';
        
        if ( ! isset($options['template']) )
        {//шаблон по умолчанию, если не передали в параметрах
            $options['template'] = 'listlearningdata';
        }
        if( $options['id'] == 0 )
        {
            $targetperson = $this->dof->storage('persons')->get_bu();
            if( ! empty($targetperson) )
            {
                $options['id'] = (int)$targetperson->id;
            }
        }
    
        if ( $this->dof->is_access('view') && isset($options['id']) && (int) $options['id'] != 0 &&
            $this->dof->storage('persons')->is_exists((int) $options['id']) )
        {//в параметрах передан подходящий пользователь
            $clientid = (int) $options['id'];
        } else
        {
            return '';
        }
    
        //создаем объект для сбора и подготовки всех необходимых данных
        $c = new dof_im_recordbook_studentslist($this->dof);
        $c->set_data($clientid);
        $c->add_data();
        // получаем данные для последующего отображения в шаблоне
        $listdata = $c->get_output($clientid);
    
        
        if ( ! is_object($listdata) or empty($listdata->students) )
        { // нет данных об ученике
            $result .= '<p align="center">(<i>' . $this->dof->get_string('no_data', 'recordbook') .
            '</i>)</p>';
        } else
        { // обращаемся к шаблонизатору для вывода таблицы
            $template = $this->dof->modlib('templater')->template('im', 'recordbook',
                $listdata, $options['template']);
            $result .= $template->get_file('html');
        }
        return $result;
    }
    
    /**
     * Получение данных для вывода личного рейтинга по учебной программе
     *
     * @param integer $cstream_id
     *
     * @return stdClass | bool
     */
    public function get_my_grades($sbc_id = null, $user_id = null)
    {
        if ( empty($sbc_id) )
        {
            return false;
        }
        // Получение объекта
        $sbc = $this->dof->storage('programmsbcs')->get_record(['id' => $sbc_id]);
        if ( empty($sbc) )
        {
            return false;
        }
        
        $available_disciplines = $this->dof->storage('programmitems')->get_records(['programmid' => $sbc->programmid, 'agenum' => $sbc->agenum], '', 'id, name');
        if ( empty($available_disciplines) )
        {
            return false;
        }
        
        $cpasseds = $this->dof->storage('cpassed')->get_cpassed_on_programmsbcid_new($sbc_id, array_keys($this->dof->workflow('cpassed')->get_meta_list('real')));
        if ( empty($cpasseds) )
        {
            return false;
        }
        // Объект результата
        $result_final = new stdClass();
        $result_final->users = [];
        $result_final->cstreams = [];
        $result_final->sum_grades = 0;
        $result_final->users[$user_id] = new stdClass();
        $result_final->users[$user_id]->grades = [];
        $result_final->users[$user_id]->percents = [];
        $result_final->users[$user_id]->final_grade = 0;
        $result_final->users[$user_id]->final_grade_percent = 0;
        $result_final->users[$user_id]->user = $this->dof->storage('persons')->get_record(['id' => $user_id]);
        
        $cstreams = [];
        foreach ( $cpasseds as $cpassed )
        {
            if ( ! in_array($cpassed->cstreamid, $cstreams) )
            {
                $cstreams[] = $cpassed->cstreamid;
            }
        }
        
        $pre_result = [];
        
        foreach ( $cstreams as $obj )
        {
            $cstream = $this->dof->storage('cstreams')->get_record(['id' => $obj]);
            if ( ! empty($cstream) && ! empty($available_disciplines[$cstream->programmitemid]) )
            {
                // Формирование информации по учебному процессу
                $cstream_info = new stdClass();
                $cstream_info->programmitem = $available_disciplines[$cstream->programmitemid];
                $cstream_info->grades = $this->dof->im('cstreams')->get_cstream_grades($cstream->id);
                $cstream_info->user_grade = 0;
                $cstream_info->user_percent = 0;
                
                // Имя учителя
                $teacher_name = $this->dof->get_string('rtreport_my_teacher_name_empty', 'recordbook');
                $teacher = $this->dof->storage('appointments')->get_person_by_appointment($cstream->appointmentid);
                if ( ! empty($teacher) )
                {
                    $teacher_name = $this->dof->storage('persons')->get_fullname($teacher->id);
                }
                $cstream->teacher_name = $teacher_name;
                $cstream_info->cstream = $cstream;
                
                foreach ( $cstream_info->grades->users as $grade )
                {
                    if ( $grade->userid == $user_id )
                    {
                        if ( ! array_key_exists($cstream->programmitemid, $pre_result) || ( $grade->grade > $pre_result[$cstream->programmitemid]->user_grade ) )
                        {
                            $cstream_info->user_grade = $grade->grade;
                            $cstream_info->user_percent = $grade->percent;
                            
                            $pre_result[$cstream->programmitemid] = $cstream_info;
                        } 
                    }
                }
            }
        }
        
        if ( ! empty($pre_result) )
        {
            foreach ( $pre_result as $discid => $cstream_info )
            {
                $result_final->cstreams[] = $cstream_info;
                $result_final->sum_grades = number_format($result_final->sum_grades + $cstream_info->grades->max_grade_average, 1);
                $result_final->users[$user_id]->final_grade = number_format($result_final->users[$user_id]->final_grade + $cstream_info->user_grade, 1);
                
                // Сбор пользователей и принадлежащие им оценки
                $result_final->users[$user_id]->grades[$cstream_info->cstream->id] = $cstream_info->user_grade;
                
                // Сбор пользователей и принадлежащие им проценты балла
                $result_final->users[$user_id]->percents[$cstream_info->cstream->id] = $cstream_info->user_percent;
            }
        }
        
        // Посчитаем общий средний балл студента
        if ( ! empty( $result_final->sum_grades) )
        {
            foreach ( $result_final->users as &$user )
            {
                if ( $result_final->sum_grades > 0 )
                {
                    $user->final_grade_percent = number_format($user->final_grade / $result_final->sum_grades * 100, 2);
                } else
                {
                    $user->final_grade_percent = 0;
                }
            }
        }
        
        return $result_final;
    }
}

