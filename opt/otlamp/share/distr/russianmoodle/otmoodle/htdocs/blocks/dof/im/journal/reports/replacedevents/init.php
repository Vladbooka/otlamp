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

/** Отчет о замененных уроках
 *
 */
class dof_im_journal_report_replacedevents extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'journal';
    protected $templatertemplatename = 'replacedevents';
    protected $departmentid;
    
    /**
     * Типы замен: замена временем (date), замена преподавателем (teacher), замена потоком (cstream)
     * @var array
     */
    protected $replacedtypes = ['date', 'teacher', 'cstream'];
 
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $id - ID текущего отчета
     */
    public function __construct($dof, $id = null)
    {
        parent::__construct($dof, $id);
        // Инициализируем генератор HTML
        $dof->modlib('widgets')->html_writer();
    }
    
	/* Код плагина, объявившего тип приказа
     * 
     */
    public function code()
    {
        return 'replacedevents';
    }
    
    /* Название отчета
     * 
     */ 
    public function name()
    {
        return $this->dof->get_string('replacedevents', 'journal');
    }
    
    /*
     * Тип плагина
     */
    public function plugintype()
    {
        return 'im';
    }
    
    /*
     * Код плагина
     */
    public function plugincode()
    {
        return 'journal';
    }    
    
    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = dof_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate   = dof_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('replacedevents_time', 'journal', $a);
        return $report;
    }     
    
 

    /** Метод записывает в отчет все данные по замененным урокам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который формируем
     * @return object $report - сформированный отчет
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// не того типа передали даные
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        // высчитываем время с учетом часового пояса пользователя
        $beginday   = dof_usergetdate($report->begindate);   
        $endday     = dof_usergetdate($report->enddate); 
        $begindate  = mktime(0,0,0,$beginday['mon'],$beginday['mday'],$beginday['year']);
        $enddate    = mktime(24,0,0,$endday['mon'],$endday['mday'],$endday['year']);
        $ageids     = (array)$report->data->ageids;
        $this->departmentid = $report->departmentid;
        // Получаем все проведенные уроки, являющиеся заменами
        $finalevents = $this->get_final_events($begindate, $enddate, $ageids);
        $mkfinalevents = $data = [];
        
        if ( $finalevents )
        {
            $events = $mkevents = [];
            // для того чтобы отобразить сколько уроков осталось обработать - посчитаем их количество
            $totalcount   = count($finalevents);
            $currentcount = 0;
            
            foreach ( $finalevents as $event )
            {
                // Выводим сообщение о том какой что проверяется сейчас, и сколько осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing eventid: '.$event->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->log_string($mtracestring, true);
                $this->log_string("\n\n");
                // Получаем все данные для отображения в шаблоне
                if ( $eventdata = $this->get_string_event($event) )
                {
                    $events[] = $eventdata;
                }
                
                if ( ! isset($mkfinalevents[$event->teacherid]) )
                {
                    $mkfinalevents[$event->teacherid] = [];
                }
                
                if ( ! isset($mkfinalevents[$event->teacherid][$event->appointmentid]) )
                {
                    $mkfinalevents[$event->teacherid][$event->appointmentid] = [];
                }
                
                if ( ! isset($mkfinalevents[$event->teacherid][$event->appointmentid][$event->cstreamid]) )
                {
                    $mkfinalevents[$event->teacherid][$event->appointmentid][$event->cstreamid] = [];
                }
                
                if ( ! isset($mkfinalevents[$event->teacherid][$event->appointmentid][$event->cstreamid][$event->id]) )
                {
                    $mkfinalevents[$event->teacherid][$event->appointmentid][$event->cstreamid][$event->id] = [];
                }
                
                $mkfinalevents[$event->teacherid][$event->appointmentid][$event->cstreamid][$event->id] = 
                [
                    'id' => $event->id,
                    'date' => $event->date,
                    'ahours' => $event->ahours,
                    'salfactor' => $event->salfactor,
                    'rhours' => $event->rhours    
                ];
                
            }
            
            foreach( $mkfinalevents as $personid => $persondata )
            {
                $mkfinalevent[$personid] = $persondata;
                if( $eventdata = $this->get_mk_string_event($mkfinalevent) )
                {
                    $mkevents[] = $eventdata;
                }
                unset($mkfinalevent);
            }
            
            // допол информация
            $report->data->info   = $this->dof->get_string('info','journal');
            $report->data->depart = $this->dof->get_string('department','journal');
            if ( $report->departmentid )
            {// отчет по подразделению
                $report->data->depart_name = $this->dof->im('departments')->get_html_link((int)$report->departmentid, true);  
            }else 
            {// все отчеты
                $report->data->depart_name = $this->dof->get_string('all_departs','journal');
            }
            // Данные о времени сбора отчета  
            $report->data->data_complete            = $this->dof->get_string('data_complete','journal');
            $report->data->data_begin_name          = $this->dof->get_string('data_begin','journal');
            $report->data->data_begin               = dof_userdate($report->crondate,'%d.%m.%Y %H:%M');
            $report->data->request_name             = $this->dof->get_string('request_name','journal');
            $report->data->requestdate              = dof_userdate($report->requestdate,'%d.%m.%Y %H:%M');
            // задаем название столбцов таблицы 
            $report->data->column_date              = $this->dof->modlib('ig')->igs('date');
            $report->data->column_oldteacher        = $this->dof->get_string('old_event_teacher','journal');
            $report->data->column_oldpitem          = $this->dof->get_string('old_event_pitem','journal');
            $report->data->column_eventrhours       = $this->dof->get_string('event_rhours','journal');
            $report->data->column_student           = $this->dof->get_string('student_or_group','journal');
            $report->data->column_newteacher        = $this->dof->get_string('new_event_teacher','journal');
            $report->data->column_newpitem          = $this->dof->get_string('new_event_pitem','journal');
            // список замененных уроков
            $report->data->column_events            = $events;
            $report->data->column_mkevents          = $mkevents;
            // вкладки
            $report->data->tab1                     = $this->dof->get_string('tab1','journal');
            $report->data->tab2                     = $this->dof->get_string('tab2','journal');
            // Ссылки на отчет в excel
            $report->data->excelexport2             = $this->get_export_link($report->id, 'makegood');
            $report->data->excelexport1             = $this->get_export_link($report->id, 'standart');
            // id отчета
            $report->data->reportid                 = $report->id;
        }
        
        return $report;
    }
    
    /** Строка для вывода одного замененного события
     * @param object $finalevent - событие из таблицы schevents. Проведенные уроки, являющиеся заменами
     * 
     * @return object $templater - объект с данныим для строчки события
     */
    public function get_string_event($finalevent)
    {
        $templater = new stdClass();
        // Получаем событие с которого все началось
        $templater->id = $finalevent->id;
        // ФИО заменяющего учителя
        $templater->newteacher = $this->get_event_teacher($finalevent);
        // Проведенный предмет
        $templater->newpitem = '';
        $templater->newdate = dof_userdate($finalevent->date,'%d.%m.%Y %H:%M');
        if ( $cstream = $this->dof->storage('cstreams')->get($finalevent->cstreamid) )
        {
            $templater->newpitem = $this->dof->im('programmitems')->get_html_link($cstream->programmitemid, 
                                   false, array('departmentid'=>$this->departmentid));
        }
        
        //зарплатные часы
        $templater->eventrhours = $finalevent->rhours;
            
        if ( ! $event = $this->dof->storage('schevents')->get($finalevent->replaceid) )
        {// не нашли заменяемый урок
            return false;
        }
        // Запланированная дата проведения урока
        $templater->date = dof_userdate($event->date,'%d.%m.%Y %H:%M');
        
        // ФИО пропустившего учителя 
        $templater->oldteacher = $this->get_event_teacher($event);
        
        // Запланированный предмет
        $templater->oldpitem = '';
        // Класс или ученик
        $templater->student  = '';
        if ( $cstream = $this->dof->storage('cstreams')->get($event->cstreamid) )
        {
            $templater->oldpitem = $this->dof->im('programmitems')->get_html_link($cstream->programmitemid, 
                                   false, array('departmentid'=>$this->departmentid));
            $templater->student  = html_writer::div( 
                $this->dof->im('cstreams')->get_html_link($cstream->id,
                    $this->dof->get_string('cstream_members','journal').":", 
                    [
                        'departmentid' => $this->departmentid
                    ]));
            
            // Определяем, кто должен был учавствовать в событии: класс или ученик
            $agroups  = array();
            $students = array();
            if ( $cslinks = $this->dof->storage('cstreamlinks')->get_records(array('cstreamid'=>$cstream->id)) )
            {// это группа
                foreach ( $cslinks as $cslink )
                {
                    $agroups[] = $this->dof->im('agroups')->get_html_link($cslink->agroupid, 
                                 false, array('departmentid'=>$this->departmentid));
                }
                $templater->student .= implode(',<br>', $agroups);
            }elseif ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cstream->id)) )
            {// Это отдельные ученики
                foreach ( $cpassed as $cpobj )
                {
                    $students[] = $this->dof->im('persons')->get_fullname($cpobj->studentid,true,null,$this->departmentid); 
                }
                $templater->student .= implode(',<br>', $students);
            }
        }
        
        return $templater;
    }
    
    /**
     * Строка для вывода данных по персоне
     * @param array $persondata массив следующей структуры:
     *     [personid][appointmentid][cstreamid]['date', 'ahour', 'salfactor', 'rhour']
     */
    public function get_mk_string_event($persondata)
    {
        // Данные для нового вида отчета "make_good"
        if( empty($persondata) )
        {// нечего формировать
            return false;
        }
        // Вытаскиваем id персоны
        $personid = key($persondata);
        
        $templater = new stdClass();
        // ФИО учителя
        $templater->newteacherfullname = $this->get_event_teacher_fullname($personid);
        // ID учителя (персоны деканата)
        $templater->personid = $personid;
        // email учителя
        $templater->email = 'email: ' . $this->dof->storage('persons')->get($personid)->email;
        
        $key = $hourssumm = $rhourssumm = $captionhourssumm = $captionrhourssumm = 0;
        foreach($persondata[$personid] as $appointmentid => $cstreams)
        {
            // Инициализация плейсхолдеров
            $templater->data[$key] = new stdClass();
            $templater->data[$key]->extended[$key] = new stdClass();
            
            // Заголовки основной таблицы
            $templater->column_agreement  = $this->dof->get_string('eagreement','journal');
            $templater->column_enumber    = $this->dof->get_string('appointment','journal');
            $templater->column_hourssumm  = $this->dof->get_string('hourssumm','journal');
            $templater->column_rhourssumm = $this->dof->get_string('rhourssumm','journal');
            $templater->column_actions    = $this->dof->get_string('actions','journal');
            
            // Заголовки таблицы-спойлера
            $templater->data[$key]->extended[$key]->column_dateofreplace = $this->dof->modlib('ig')->igs('date');
            $templater->data[$key]->extended[$key]->column_cstream       = $this->dof->get_string('cstream','journal');
            $templater->data[$key]->extended[$key]->column_hours         = $this->dof->get_string('hours','journal');
            $templater->data[$key]->extended[$key]->column_salfactor     = $this->dof->get_string('salfactor','journal');
            $templater->data[$key]->extended[$key]->column_rhour         = $this->dof->get_string('rhour','journal');
            
            // Ссылка на договор
            $templater->data[$key]->agreement = $this->get_event_teacher_agreement($appointmentid);
            // Табельный номер
            $templater->data[$key]->enumber = $this->get_event_enumber($appointmentid);
            // Действия
            $templater->data[$key]->actions = $this->get_event_actions();
            
            foreach($cstreams as $cstreamid => $event)
            {
                foreach($event as $eventid => $eventdata)
                {
                     // Инициализация плейсхолдеров
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid] = new stdClass();
                    // Дата замены
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->dateofreplace = dof_userdate($eventdata['date'],'%d.%m.%Y %H:%M');;
                    // Ссылка на учебный процесс
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->cstream = $this->get_event_cstream($cstreamid);
                    // Фактические часы
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->ahour = (int)$eventdata['ahours'];
                    // Суммируем фактические часы
                    $hourssumm += (int)$eventdata['ahours'];
                    // Коэффициент для рассчета зарплатного часа
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->salfactor = $eventdata['salfactor'];
                    // Зарплатные часы
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->rhour = (float)$eventdata['rhours'];
                    // Суммируем зарпатные часы
                    $rhourssumm += (float)$eventdata['rhours'];
                    $templater->data[$key]->extended[$key]->extendeddata[$eventid]->id = $eventid;
                }
            }
            // Сумма фактических часов по табелю
            $templater->data[$key]->hourssumm = $hourssumm;
            // Сумма зарплатных часов по табелю
            $templater->data[$key]->rhourssumm = $rhourssumm;
            // Просуммируем все фактические часы
            $captionhourssumm += $hourssumm;
            // Просуммируем все зарплатные часы
            $captionrhourssumm += $rhourssumm;
            // Обнулим суммы фактических и зарплатных часов по табелю
            $hourssumm = $rhourssumm = 0;
            // Передвинем основной ключ на единичку
            $key++;
        }
        
        // Сумма фактических часов по персоне
        $templater->captionhourssumm =  $this->dof->get_string('hourssumm','journal');
        $templater->datahourssumm = $captionhourssumm;
        // Сумма зарплатных часов по персоне
        $templater->captionrhourssumm =  $this->dof->get_string('rhourssumm','journal');
        $templater->datarhourssumm = $captionrhourssumm;
        
        return $templater;
    }
    
    /** Получить все проведенные уроки за указанный период (в одном подразделении или во всех)
     *  которые являлись конечными заменами (последним звеном в цепочке замен)
     * @param int $begindate - Дата начала периода, за который собираются замененные уроки (unixtime)
     * @param int $endate - окончание периода, когда собираются замененные уроки (unixtime)
     * @param int $departmentid[optional] - id подразделения для которого собираются уроки
     * @param string $sort[optional] - порядок сортировки уроков 
     *                                    (по умолчанию - по запланированному времени, по возрастанию)
     * 
     * @return array - массив записей из таблицы schevents
     */
    protected function get_final_events($begindate, $enddate, $ageids = [], $departmentid = null, $sort = 'date ASC')
    {
        // составляем условия выборки
        $params = [];
        $params['begindate']    = $begindate;
        $params['enddate']      = $enddate;
        $select = 'date >= ? AND (date+duration) <= ? AND
                    status IN ("plan", "completed", "postponed")  AND replaceid IS NOT NULL';
        // Нормализуем id-шники периодов
        $ageids = (array)$ageids;
        if( ! empty($ageids) )
        {
            // Если выбран конкретный период
            $cstreams = $this->dof->storage('cstreams')->get_records(['ageid' => $ageids]);
            if( ! empty($cstreams) )
            {// Получим учебные процессы этого периода
                $selectcstreams = array_keys($cstreams);
            }
            if( ! empty($selectcstreams) )
            {// Если получили учебные процессы - добавим их в условие выборки
                $select .= ' AND cstreamid IN (' . implode(',', $selectcstreams) . ')';
            
            } else
            {// Если учебных процессов нет - вернем пустой массив
                return [];
            }
        }

        return $this->dof->storage('schevents')->get_records_select($select, $params, $sort,
            'id, planid, cstreamid, date, duration, place, replaceid, form, appointmentid, rhours, teacherid, ahours, salfactor');
    }
    
    /** Получить ссылку на сотрудника на которого запланировано событие (вместе с договором)
     * @param object $event - событие из таблицы schevents
     * 
     * @return string - html-ссылка на сотрудника и его тоговор или пустая строка
     */
    protected function get_event_teacher($event)
    {
        if ( $person = $this->dof->storage('appointments')->get_person_by_appointment($event->appointmentid) )
        {
            $appointment = $this->dof->storage('appointments')->get($event->appointmentid);
            $eagreementlink = '';
            if ( $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
            {// @todo сейчас при переходе будет сбиваться подразделение - а у нас нет
                // стандартных способов отслеживания глобального состояния
                // когда их придумаете - исправьте ссылку
                $eagreementlink = dof_html_writer::link($this->dof->url_im('employees', 
                    '/view_eagreement.php?id='.$eagreement->id.'&dapartmentid='.$this->departmentid), '[' . $eagreement->num . ']');
            }
            // Показываем ссылку на учителя и на договор
            return $this->dof->im('persons')->get_fullname($person->id,true,null,$this->departmentid).' '.$eagreementlink;
        }
        
        return '';
    }
    
    /** Получить ссылку на сотрудника на которого запланировано событие (без договора)
     * @param int $personid - id персоны
     *
     * @return string - html-ссылка на сотрудника или пустая строка
     */
    protected function get_event_teacher_fullname($personid)
    {
        $fullname = $this->dof->storage('persons')->get_fullname($personid);
        $depid = optional_param('departmentid', 0, PARAM_INT);
        return dof_html_writer::link($this->dof->url_im('persons', '/view.php',
            ['id'=>$personid, 'departmentid'=>$depid]), $fullname . '[' . $personid . ']');
    }
    
    /** Получить ссылку на договор сотрудника на которого запланировано событие
     * @param int $appointmentid - id должностного назначения
     *
     * @return string - html-ссылка на договор сотрудника или пустая строка
     */
    protected function get_event_teacher_agreement($appointmentid)
    {
        if ( $appointment = $this->dof->storage('appointments')->get($appointmentid) )
        {
            $eagreementlink = '';
            if ( $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
            {// @todo сейчас при переходе будет сбиваться подразделение - а у нас нет
                // стандартных способов отслеживания глобального состояния
                // когда их придумаете - исправьте ссылку
                $eagreementlink = dof_html_writer::link($this->dof->url_im('employees',
                    '/view_eagreement.php?id='.$eagreement->id.'&dapartmentid='.$this->departmentid), '[' . $eagreement->num . ']');
            }
            // Показываем ссылку на учителя и на договор
            return $eagreementlink;
        }
    
        return '';
    }
    
    /**
     * Получить табельный номер должностного назначения
     * @param int $appointmentid - id должностного назначения
     * @return string табельный номер должностного назначения или пусто
     */
    protected function get_event_enumber($appointmentid)
    {
        if ( $appointment = $this->dof->storage('appointments')->get($appointmentid) )
        {
            return $appointment->enumber;
        }
        return '';
    }
    
    /**
     * Получить html-код для отображения доступных действий
     */
    protected function get_event_actions()
    {
        return dof_html_writer::div('', 'toggle');
    }
    
    /**
     * Получить ссылку на cstream вида "Варка кислых щей (Иванов Иван Иванович)"
     * @param int $cstreamid id учебного процесса
     * @return string
     */
    protected function get_event_cstream($cstreamid)
    {
        $teacher = $this->get_teacher_by_cstream($cstreamid);
        $pitem = $this->get_pitem_by_cstream($cstreamid);
        $teacherfullname = $this->dof->storage('persons')->get_fullname($teacher->id);
        return html_writer::div(
            $this->dof->im('cstreams')->get_html_link($cstreamid,
                $pitem->name . ' (' . $teacherfullname . ')',
                [
                    'departmentid' => $this->departmentid
                ]));
    }
    
    /**
     * Получить объект учителя, ведущего учебный процесс
     * @param int $cstream
     * @return object|stdClass|false|boolean объект персоны или false
     */
    protected function get_teacher_by_cstream($cstreamid)
    {
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        return $this->dof->storage('persons')->get($cstream->teacherid);
    }
    
    /**
     * Получить объект дисциплины учебный процесс
     * @param int $cstream
     * @return object|stdClass|false|boolean объект дисциплины или false
     */
    protected function get_pitem_by_cstream($cstreamid)
    {
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        return $this->dof->storage('programmitems')->get($cstream->programmitemid);
    }
    
    /**
     * Получить ссылку на экспорт отчета
     * @return string
     */
    protected function get_export_link($reportid, $reporttype = '', $options = [])
    {
        $options = (array)$options;
        $plugintype = optional_param('plugintype', 'im', PARAM_TEXT);
        $plugincode = optional_param('plugincode', 'journal', PARAM_TEXT);
        $code = optional_param('code', 'replacedevents', PARAM_TEXT);
        $export = optional_param('export', 'xls', PARAM_TEXT);
        
        $addvars = [
            'plugintype' => $plugintype,
            'plugincode' => $plugincode,
            'code' => $code,
            'export' => $export,
            'id' => $reportid,
            'rtype' => $reporttype
        ];
        
        if( ! empty($options) )
        {
            foreach($options as $key => $val)
            {
                if( ! in_array($key, $addvars) )
                {// Нельзя переопределять имеющиеся ключи
                    $addvars[$key] = $val;
                }
            }
        }
        
        $url = new moodle_url('/blocks/dof/im/reports/export.php', $addvars);
        
        return dof_html_writer::link($url, $this->dof->get_string('excelexport','journal'), ['class' => 'btn']);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see dof_storage_reports_basereport::load_data()
     */
    protected function load_data($report)
    {
        // Массив идентификаторов замен, которые нужно отобразить после фильтрации
        $eventids = $formdata = [];
        // Тип отчета переданный в get-параметре
        $reporttype = optional_param('rtype', 'standart', PARAM_TEXT);
        // Типы замен из фильтра
        $replacedtypes = optional_param('replacedtype', null, PARAM_TEXT);
        if( $replacedtypes )
        {
            $replacedtypes = explode(',', (string)$replacedtypes);
        }
        
        // Смотрим POST или GET-параметры
        if( ! empty($_POST) )
        {
            $formdata = $_POST;
        } elseif( ! empty($replacedtypes) )
        {
            foreach($replacedtypes as $replacedtype)
            {
                $formdata[$replacedtype] = $replacedtype;
            }
        } else 
        {
            foreach($this->replacedtypes as $replacedtype)
            {// Не оставляем $formdata пустой, чтобы в отчете типа standart вывести тип замены
                $formdata[$replacedtype] = $replacedtype;
            }
        }
        
        if( ! empty($formdata) )
        {// Если выбран фильтр - фильтруем данные
            $report->column_type = $this->dof->get_string('type_replace_event','journal');
            if ( isset($report->column_events) )
            {// если уроки есть
            // переформируем массив отображаемых уроков заново
            $events = array();
            foreach ( $report->column_events as $num=>$event )
            {
                $type = [];
                if ( isset($event->newdate) AND $event->date != $event->newdate )
                {// замена другим временем
                    if ( empty($formdata) OR !empty($formdata['date']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                        $eventids[$event->id] = $event->id;
                    }
                    $type[] = $this->dof->get_string('replace_event_date','journal');
                }
                if ( $event->oldteacher != $event->newteacher )
                {// замена другим учителем
                    if ( empty($formdata) OR !empty($formdata['teacher']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                        $eventids[$event->id] = $event->id;
                    }
                    $type[] = $this->dof->get_string('replace_event_teacher','journal');
                }
                if ( $event->oldpitem != $event->newpitem )
                {// замена другим потоком
                    if ( empty($formdata) OR !empty($formdata['cstream']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                        $eventids[$event->id] = $event->id;
                    }
                    $type[] = $this->dof->get_string('replace_event_cstreams','journal');
                }
                // объединяем все типы в одну строчку
                if ( isset($events[$num]) )
                {// урок попал в переформированный массив
                    $events[$num]->type = implode('<br>',$type);
                }
            }
            // сохраняем переформированный массив в данные отчета
            $report->column_events = $events;
            }
            
            if( ! empty($eventids) )
            {// Если есть что отображать
                if ( ! empty($report->column_mkevents) )
                {// Если есть данные для отчета типа "make_good"
                    foreach($report->column_mkevents as $key => $userdata)
                    {// Разберем данные по персонам
                        foreach($userdata->data as $datakey => $enumberdata)
                        {// Разберем данные персоны по табелям
                            foreach($enumberdata->extended as $extendedkey => $cstreamdata)
                            {// Разберем табеля по учебным процессам
                                foreach($cstreamdata->extendeddata as $eventid => $eventdata)
                                {// Размеберм учебные процессы по заменам
                                    if( ! in_array($eventid, $eventids) )
                                    {// Если замену отображать по фильтру не нужно
                                        // Вычтем из суммы часов по табелю часы по замене, которую не нужно отображать
                                        $report->column_mkevents[$key]->data[$datakey]->hourssumm -= $report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata[$eventid]->ahour;
                                        $report->column_mkevents[$key]->data[$datakey]->rhourssumm -= $report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata[$eventid]->rhour;
                                        // Вычтем из суммы часов по персоне часы по замене, которую не нужно отображать
                                        $report->column_mkevents[$key]->datahourssumm -= $report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata[$eventid]->ahour;
                                        $report->column_mkevents[$key]->datarhourssumm -= $report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata[$eventid]->rhour;
                                        // Убираем данные по замене из отображения
                                        unset($report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata[$eventid]);
                                    }
                                }
                                if( empty($report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]->extendeddata) )
                                {// Если по учебному процессу убрали все данные по заменам
                                // Уберем учебный процесс из табеля
                                unset($report->column_mkevents[$key]->data[$datakey]->extended[$extendedkey]);
                                }
                            }
                            if( empty($report->column_mkevents[$key]->data[$datakey]->extended) )
                            {
                                unset($report->column_mkevents[$key]->data[$datakey]);
                            }
                        }
                        if( empty($report->column_mkevents[$key]->data) )
                        {// Если по табелю убраны все учебные процессы
                        // Уберем табель из отображения
                        unset($report->column_mkevents[$key]);
                        }
                    }
                }
            } else
            {
                $report->column_mkevents = [];
            }
        }
        
        // Подготовка данных для выгрузки в csv
        switch($reporttype)
        {
            case 'standart':
                $c1 = $this->dof->modlib('ig')->igs('date');;
                $c2 = $this->dof->get_string('old_event_teacher','journal');
                $c3 = $this->dof->get_string('old_event_pitem','journal');
                $c4 = $this->dof->get_string('student_or_group','journal');
                $c5 = $this->dof->get_string('new_event_teacher','journal');
                $c6 = $this->dof->get_string('new_event_pitem','journal');
                $c7 = $this->dof->get_string('event_rhours','journal');
                $data = [];
                $header = new stdClass();
                $header->date = $c1;
                $header->oldteacher = $c2;
                $header->oldpitem = $c3;
                $header->student = $c4;
                $header->newteacher = $c5;
                $header->newpitem = $c6;
                $header->eventrhours = $c7;
                $data[] = $header;
                if( ! empty($report->column_events) )
                {
                    foreach($report->column_events as $key => $val)
                    {
                        $cell = new stdClass();
                        $cell->date = trim(strip_tags($val->date));
                        $cell->oldteacher = trim(strip_tags($val->oldteacher));
                        $cell->oldpitem = trim(strip_tags($val->oldpitem));
                        $cell->student = trim(strip_tags($val->student));
                        $cell->newteacher = trim(strip_tags($val->newteacher));
                        $cell->newpitem = trim(strip_tags($val->newpitem));
                        $cell->eventrhours = trim(strip_tags($val->eventrhours));
                        $data[] = $cell;
                    }
                }
                $report->exportcsv = $data;
                return $report;
                break;
            case 'makegood':
                $c1 = $this->dof->get_string('new_event_teacher','journal');;
                $c2 = $this->dof->get_string('rhourssumm','journal');
                $data = [];
                $header = new stdClass();
                $header->newteacherfullname = $c1;
                $header->datarhourssumm = $c2;
                $data[] = $header;
                if( ! empty($report->column_mkevents) )
                {
                    foreach($report->column_mkevents as $key => $val)
                    {
                        $cell = new stdClass();
                        $cell->newteacherfullname = trim(strip_tags($val->newteacherfullname));
                        $cell->datarhourssumm = trim(strip_tags($val->datarhourssumm));
                        $data[] = $cell;
                    }
                }
                $report->exportcsv = $data;
                return $report;
                break;
            default:
                return $report;
        }
    }
    
    /**
     * Поддержка отчетом заголовков в csv-формате
     * 
     * @return bool
     */
    public function templater_csv_show_header()
    {
        return false;
    }
    
    /** Отобразить отчет в формате HTML
     * 
     */
    public function show_report_html($addvars=null)
    {
        // подключаем js для обработки внешнего вида отчетов
        $this->dof->modlib('nvg')->add_js('im', 'journal', '/reports/replacedevents/script.js', false);
        $error = '';
        $table = '';
        if ( ! $this->is_generate($this->load()) )
        {//  отчет еще не сгенерирован
            $error = $this->dof->get_string('report_no_generate','journal');
        }else
        {// загружаем шаблон
            // достаем данные из файла
            $template = $this->load_file();
            // подгружаем методы работы с шаблоном
            if ( isset($template->column_events) )
            {
                if ( ! $templater = $this->template() )
                {//не смогли
                    $error = $this->dof->get_string('report_no_get_template','journal');
                }elseif ( ! $table = $templater->get_file('html') )
                {// не смогли загрузить html-таблицу
                    $error = $this->dof->get_string('report_no_get_table','journal');
                }
            }else 
            {
                $error = $this->dof->get_string('no_data','journal','<br>');
            }
        }
        
        // вывод ошибок
        print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';
        echo $table;
    }
    
    /**
     * Метод, предусмотренный для расширения логики отображения данных отчета
     */
    protected function template_data($template)
    {
        // Массив идентификаторов замен, которые нужно отобразить после фильтрации
        $eventids = $replacedtypes = [];
        // ловим данный из формы template
        $formdata = $_POST;
        // все фильтры пока активны
        $optiondate = 'checked';
        $optionteacher = 'checked';
        $optioncstream = 'checked';
        if ( !empty($formdata) AND empty($formdata['date']) )
        {// отменили фильтр замены по дате - снимем галку
            $optiondate = '';
        }
        if ( !empty($formdata) AND empty($formdata['teacher']) )
        {// отменили фильтр замены по учителю - снимем галку
            $optionteacher = '';
        }
        
        // Формируем ссылки на выгрузку отчетов в csv
        foreach($this->replacedtypes as $replacedtype)
        {
            if( ! empty($formdata[$replacedtype]) )
            {// по фильтру выбираем нужные get-параметры
                $replacedtypes[] = $replacedtype;
            }
        }
        if( ! empty($replacedtypes) )
        {// формируем ссылки
            $replacedtypes = implode(',', $replacedtypes);
            $template->excelexport2 = $this->get_export_link($template->reportid, 'makegood', ['replacedtype' => $replacedtypes]);
            $template->excelexport1 = $this->get_export_link($template->reportid, 'standart', ['replacedtype' => $replacedtypes]);
        }
        
        // формируем отображение типа замены
        $form = '<form id="form_select_type" method="post" action="">'.
                '<input type="checkbox" name="date" '.$optiondate.'/>'.
                $this->dof->get_string('replace_event_date','journal').'<br>'.
                '<input type="checkbox" name="teacher" '.$optionteacher.'/>'.
                $this->dof->get_string('replace_event_teacher','journal').'<br>'.
                '<input name="remove" id="remove" type="submit" value="'.
                $this->dof->modlib('ig')->igs('show').'" title="'.
                $this->dof->modlib('ig')->igs('show').'" />';
        $template->form_title = $this->dof->get_string('filter_type','journal');
        $template->form = $form;
        
        return $template;
    }  
    
    /**
     * Добавление CSS для стилизации HTML-формата отчета
     * 
     * @return void
     */
    public function templater_html_additional_css()
    {
        $this->dof->modlib('nvg')->add_css('im', 'journal', '/reports/replacedevents/styles.css');
        $this->dof->modlib('nvg')->add_css('im', 'journal', '/reports/replacedevents/jquery-ui.css');
    }
    
    /**
     * Метод дополнения формы заказа нового отчета
     *
     * @param dof_im_journal_report_form - Ссылка на форму заказа нового отчета
     *
     * @return void
     */
    public function reportcreate_form_definition($form)
    {
        // Создание ссылки на HTML_QuickForm
        $mform = $form->get_mform();
        
        // Получение доступных учебных периодов
        $agestatuses = (array)$this->dof->workflow('ages')->get_meta_list('active');
        $ages = (array)$this->dof->storage('ages')->get_records([
            'status' => array_keys($agestatuses)
        ]);
        
        // Фильтрация учебных периодов
        foreach ( $ages as $ageid => &$age )
        {
            $access = $this->dof->storage('ages')->
                is_access('use', $ageid, null, $form->addvars['departmentid']);
            if ( ! $access )
            {// Доступ на использование периода закрыт
                unset($ages[$ageid]);
            } else 
            {// Формирование списка 
                $age = $age->name;
            }
        }
        
        // Дополнение выпадающего списка
        if ( empty($ages) )
        {// Доступных учебных периодов не найдено
            $form->freeze_form();
        }
        
        // Добавление списка учебных периодов
        $mform->addElement(
            'dof_multiselect', 
            'ageids',
            $this->dof->get_string('age', 'journal'), 
            $ages
        );
    }
    
    /**
     * Метод дополнительной валидации формы заказа нового отчета
     *
     * @param dof_im_journal_report_form - Объект формы заказа нового отчета
     * @param array $data - Данные формы
     * @param array $files - Данные файлов формы
     * @param array $errors - Ссылка на массив ошибок валидации
     *
     * @return void
     */
    public function reportcreate_form_validation($form, $data, $files, &$errors)
    {
        // Валидация учебных периодов
        if ( ! empty($data['ageids']) )
        {
            // Проверка доступа к учебным периодам
            foreach ( (array)$data['ageids'] as $ageid )
            {
                $access = $this->dof->storage('ages')->
                    is_access('use', $ageid, null, $form->addvars['departmentid']);
                if ( ! $access )
                {// Доступ на использование периода закрыт
                    $errors['ageids'] = $this->dof->get_string('reportcreate_form_error_ageid_access_denied', 'journal');
                }
            }
        } else 
        {// Учебные периоды не указаны
            $errors['ageids'] = $this->dof->get_string('reportcreate_form_error_empty_ageids', 'journal');
        }
    }
    
    /**
     * Метод дополнительной обработки формы заказа нового отчета
     *
     * @param dof_im_journal_report_form - Объект формы заказа нового отчета
     * @param stdClass $data - Данные формы
     * @param stdClass $reportdata - Данные отчета
     *
     * @return void
     */
    public function reportcreate_form_process($form, $data, &$reportdata)
    {
        // Добавление информации об учебных периодах
        $reportdata->data->ageids = $data->ageids;
    }
}