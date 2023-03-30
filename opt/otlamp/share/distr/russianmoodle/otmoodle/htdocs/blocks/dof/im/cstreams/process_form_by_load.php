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
 * обработчик формы страницы для отображения подробной информации о потоках
 */

class dof_im_cstreams_process_form_by_load
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $eadepid;
    private $apdepid;
    private $cstreamdepid;
    private $personid;
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof, $eadepid, $apdepid, $cstreamdepid, $personid)
    {
        $this->dof = $dof;
        $this->eadepid = $eadepid;
        $this->apdepid = $apdepid;
        $this->cstreamdepid = $cstreamdepid;
        $this->personid = $personid;
    }
    
    /** Возвращает таблицы с нагрузками учитьелей
     * @return string html-код таблиц
     */
    public function get_teachers_load()
    {
        // найдем табельные номера отсортированные по персонам
        if ( ! is_null($this->personid) )
        {// если персона не пустая - отобразим всю информацию
            $persons = $this->get_array_appointments();
            if ( empty($persons) )
            {// табельные номера не найдены - таблиц нет
                return '';
            }   
        }else
        {// персона пустая - сообщим, что надо что-то выбрать
            return '<br><p align="center"><b>'.$this->dof->get_string('select_person','cstreams').'</b></p>';
        }
        $rez = '';
        foreach ( $persons as $id=>$person )
        {// для каждого табеля формируем строчку
            $rez .= $this->get_table_person($person, $id).'<br>';
        }
        return '<br>'.$rez;
        
    }
    
    /** Возвращает список табельных номеров отсортированных по персонам
     * @return array массив персон с табельными номерами
     */
    private function get_array_appointments()
    {
        $conds = new stdClass();
        $conds->eagreementdepartmentid = $this->eadepid;
        $conds->departmentid = $this->apdepid;
        if ( $this->personid )
        {// есть персона - добавим ее к поиску
            $conds->personid = $this->personid;
        }
        $conds->status = array('plan','active');
        $mas = array();
        if ( $appointments = $this->dof->storage('appointments')->get_teacher_list($conds) )
        {
            foreach ( $appointments as $appointment )
            {// для каждого табеля
                $mas[$appointment->personid][$appointment->id] = $appointment;
            }
        }
        return $mas;
        
        
    }
    /** Возвращает строку для отображения данных о потоке
     * @param object $cstream - объект записи из таблицы cstreams БД
     * @return array - массив для вставки в таблицу
     */
    private function get_string_info_cstream($cstream)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $cstreamname = '<a href="'.$this->dof->url_im('cstreams', '/view.php?cstreamid='.$cstream->id,$addvars).'">'.
               $this->dof->storage('cstreams')->change_name_cstream($cstream).'</a>';
        // имя программы
        $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'programmid');
        $programname = '<a href="'.$this->dof->url_im('programms', '/view.php?programmid='.
                    $programmid,$addvars).'">'.
                    $this->dof->storage('programms')->get_field($programmid,'name').' <br>['.
                    $this->dof->storage('programms')->get_field($programmid,'code').']';
        // имя предмета
        $itemname = '<a href="'.$this->dof->url_im('programmitems', '/view.php?pitemid='.
                    $cstream->programmitemid,$addvars).'">'.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'code').']';
        
        // ссылки
        $link = '';
        if ( $this->dof->storage('cstreams')->is_access('edit', $cstream->id) OR 
             $this->dof->storage('cstreams')->is_access('edit/plan', $cstream->id) )
        {
            $link .= '<a href="'.$this->dof->url_im('cstreams', '/edit.php?cstreamid='.$cstream->id,$addvars).
                     '"><img src="'.$this->dof->url_im('cstreams', '/icons/edit.png').'"</a>' ;
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $link .= ' <a href='.$this->dof->url_im('schedule','/view_week.php?ageid='.
                    $cstream->ageid.'&cstreamid='.$cstream->id.'&departmentid='.$depid).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').'">'.'</a>';
        }
        $calculatedsalfactor = $this->dof->storage('cstreams')->calculation_salfactor($cstream);
        $salaryhours = $cstream->hoursweek*$calculatedsalfactor;
        if ( $cstream->status == 'active' )
        {// если статус активный - выведем обычную надпись
            return array($programname, $itemname, $cstreamname, $cstream->hoursweek, 
                    (int) $cstream->hoursweekinternally, (int) $cstream->hoursweekdistance, 
                    $cstream->salfactor.'/'.$cstream->substsalfactor, $calculatedsalfactor,
                    $salaryhours, $link);
        }
        //выводим все серым цвеиом
        return array('<span class=gray_link>'.$programname.'</span>','<span class=gray_link>'.$itemname.'</span>',
                     '<span class=gray_link>'.$cstreamname.'</span>','<span class=gray>'.$cstream->hoursweek.'</span>',
                     '<span class=gray>'.(int) $cstream->hoursweekinternally.'</span>',
                     '<span class=gray>'.(int) $cstream->hoursweekdistance.'</span>',
                     '<span class=gray>'.$cstream->salfactor.'/'.$cstream->substsalfactor.'</span>', 
                     '<span class=gray>'.$calculatedsalfactor.'</span>',
                     '<span class=gray>'.$salaryhours.'</span>',
                     $link);
        
    }
    
    /** Возвращает таблицу с потоками
     * @param object $appointid - id табельного номера
     * @param int $fixhours - назначенная нагрузка по табельному номеру (должностному назначению)
     * @param int $salaryhours - зарплатные часы по табельному номеру (должностному назначению)
     * @param int $fullfixhours - назначенная нагрузка по табельному номеру (должностному назначению) с учетом неактивных процессов
     * @param int $fullsalaryhours - назначенные часы по табельному номеру (должностному назначению) с учетом неактивных процессов
     * @return string html-код таблиц
     */
    public function get_table_cstream($appointid,&$fixhours,&$salaryhours,&$fullfixhours,&$fullsalaryhours)
    {
        // ищем все потоки
        if ( $this->cstreamdepid )
        {// указано подразделение - выведем только для него
            $cstreams = $this->dof->storage('cstreams')->get_records(array('departmentid'=>$this->cstreamdepid,
                            'appointmentid'=>$appointid,'status'=>array('plan','active','suspend')), 'status ASC, name ASC');
        }else
        {// для всех подразделений
            $cstreams = $this->dof->storage('cstreams')->get_records(array('appointmentid'=>$appointid,
                            'status'=>array('plan','active','suspend')), 'status ASC, name ASC');
        }
        if ( ! $cstreams )
        {// потоков нет - возвращаем пустую строчку
            return '';
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "left";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
		//$table->size = array('200px','200px',null,'100px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head[] = $this->dof->get_string('programm', 'cstreams');
        $table->head[] = $this->dof->get_string('programmitem', 'cstreams');
        $table->head[] = $this->dof->get_string('name_cstream', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweek', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweekinternally', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweekdistance', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('salcalcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('calcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('salaryhours', 'cstreams','<br>');
        $table->head[] = $this->dof->modlib('ig')->igs('actions');
        // заносим данные в таблицу     
        foreach ( $cstreams as $cstream )
        {// для каждого предмета формируем строчку и запоминаем кол-во часов
            $table->data[] = $this->get_string_info_cstream($cstream);
            
            $calculatedsalfactor = $this->dof->storage('cstreams')->calculation_salfactor($cstream);
            if ( $cstream->status == 'active' )
            {// если статус активный, считаем нагрузку
                $fixhours += $cstream->hoursweek;
                $salaryhours += $cstream->hoursweek * $calculatedsalfactor;
            }
            $fullfixhours += $cstream->hoursweek;
            $fullsalaryhours += $cstream->hoursweek * $calculatedsalfactor;
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
        
    }
    
    /** Возвращает таблицы табельных номеров для персоны
     * @param array $person - массив персоны с табельными номерами
     * @param int $id - id персоны
     * @return string html-код таблиц
     */
    public function get_table_person($person,$id)
    {        
        // заносим данные в таблицу  
        $result = '';
        
        // Табельная нагрузка, суммарно по учителю
        $teacherworktime = 0;
        // Назначенная нагрузка, суммарно по учителю
        $teacherfixhours = 0;
        // Назначенная нагрузка с учетом неактивных процессов, суммарно по учителю
        $teacherfullfixhours = 0;
        // Зарплатные часы, суммарно по учителю
        $teachersalaryhours = 0;
        // Зарплатные часы с учетом неактивных процессов, суммарно по учителю
        $teacherfullsalaryhours = 0;
        
        foreach ( $person as $appoint )
        {// для каждого табеля формируем отчет
            
            // Назначенная нагрузка по табельному номеру (должностному назначению)
            $fixhours = 0;
            // Назначенная нагрузка по табельному номеру (должностному назначению) с учетом неактивных процессов
            $fullfixhours = 0;
            // Зарплатные часы по табельному номеру (должностному назначению)
            $salaryhours = 0;
            // Зарплатные часы по табельному номеру (должностному назначению) с учетом неактивных процессов
            $fullsalaryhours = 0;
            
            $cstreams_table = $this->get_table_cstream($appoint->id, $fixhours, $salaryhours, $fullfixhours, $fullsalaryhours);
            
            // Формирование заголовка перед выводом данных по должностному назначению (табелю)
            $eagreementlabel = dof_html_writer::span(
                $this->dof->get_string('eagreement', 'cstreams'),
                'appointment_load_label eagreement_label'
            );
            $eagreementvalue = dof_html_writer::span(
                $this->dof->storage('eagreements')->get_field($appoint->eagreementid, 'num'),
                'appointment_load_value eagreement_value'
            );
            $appointmentlabel = dof_html_writer::span(
                $this->dof->get_string('appointment', 'cstreams'),
                'appointment_load_label appointment_label'
            );
            $appointmentvalue = dof_html_writer::span(
                $appoint->enumber,
                'appointment_load_value appointment_value'
            );            
            $worktimelabel = dof_html_writer::span(
                $this->dof->get_string('tabel', 'cstreams'),
                'appointment_load_label worktime_label'
            );
            $worktimevalue = dof_html_writer::span(
                round($appoint->worktime, 2),
                'appointment_load_value worktime_value'
            );
            $fixhourslabel = dof_html_writer::span(
                $this->dof->get_string('fix', 'cstreams'),
                'appointment_load_label fixhours_label'
            );
            $fixhoursvalue = dof_html_writer::span(
                $fixhours.' ('.$fullfixhours.')',
                'appointment_load_value fixhours_value'
            );
            $salaryhourslabel = dof_html_writer::span(
                $this->dof->get_string('salaryhours', 'cstreams'),
                'appointment_load_label salaryhours_label'
            );
            $salaryhoursvalue = dof_html_writer::span(
                $salaryhours.' ('.$fullsalaryhours.')',
                'appointment_load_value salaryhours_value'
            );
            
            // заголовок
            $result .= dof_html_writer::div(
                //dof_html_writer::div($this->dof->get_string('load', 'cstreams'), 'loadtype') .
                dof_html_writer::div(
                    dof_html_writer::div($eagreementlabel . $eagreementvalue, 'eagreement') .
                    dof_html_writer::div($appointmentlabel . $appointmentvalue, 'appointment'),
                    'load_identifier'
                ) .
                dof_html_writer::div($worktimelabel . $worktimevalue, 'worktime') .
                dof_html_writer::div($fixhourslabel . $fixhoursvalue, 'fixhours') .
                dof_html_writer::div($salaryhourslabel . $salaryhoursvalue, 'salaryhors'),
                'appointment_load_header'
            );
            
            if ( $cstreams_table )
            {// если потоки на тебельные номер есть - выведем таблицу
                $result .= $cstreams_table; 
            }else
            {// выведем сообщение что их нет
                $result .= dof_html_writer::div($this->dof->get_string('no_cstream_for_appointment', 'cstreams'), 'nodata');
            }
            
            $teacherworktime += $appoint->worktime;
            $teacherfixhours += $fixhours;
            $teacherfullfixhours += $fullfixhours;
            $teachersalaryhours += $salaryhours;
            $teacherfullsalaryhours += $fullsalaryhours;
        }
        
        
        
        // Формирование заголовка перед выводом суммарныз данных по преподавателю
        $teacher = dof_html_writer::span(
            $this->dof->storage('persons')->get_fullname($id), 
            'teacher'
        );
        $teacherworktimelabel = dof_html_writer::span(
            $this->dof->get_string('tabel', 'cstreams'),
            'teacher_load_label teacher_worktime_label'
        );
        $teacherworktimevalue = dof_html_writer::span(
            round($teacherworktime, 2),
            'teacher_load_value teacher_worktime_value'
        );
        $teacherfixhourslabel = dof_html_writer::span(
            $this->dof->get_string('fix', 'cstreams'),
            'teacher_load_label teacher_fixhours_label'
        );
        $teacherfixhoursvalue = dof_html_writer::span(
            $teacherfixhours.' ('.$teacherfullfixhours.')',
            'teacher_load_value teacher_fixhours_value'
        );
        $teachersalaryhourslabel = dof_html_writer::span(
            $this->dof->get_string('salaryhours', 'cstreams'),
            'teacher_load_label teacher_salaryhours_label'
        );
        $teachersalaryhoursvalue = dof_html_writer::span(
            $teachersalaryhours.' ('.$teacherfullsalaryhours.')',
            'teacher_load_value teacher_salaryhours_value'
        );
        
        $result = dof_html_writer::div(
            //dof_html_writer::div($this->dof->get_string('total_load', 'cstreams'), 'loadtype') .
            dof_html_writer::div($teacher, 'load_identifier') .
            dof_html_writer::div($teacherworktimelabel . $teacherworktimevalue, 'worktime') .
            dof_html_writer::div($teacherfixhourslabel . $teacherfixhoursvalue, 'fixhours') .
            dof_html_writer::div($teachersalaryhourslabel . $teachersalaryhoursvalue, 'salaryhors'),
            'teacher_load_header'
            ) . dof_html_writer::div($result, 'appointment_load');
        
        return $result;  
    }
    
    
    /** Возвращает количество шаблонов потока
     * @param $cstreamid - id потока
     * @return bool false|int
     */
    private function get_count_templates($cstreamid)
    {
        if ( ! $this->dof->plugin_exists('im', 'otech') )
        {// нет плагина, смысла искать нет
            return false;
        }
        $csobj = new otech_doffice_templesson_cstreams();
        // выводим количество записей в шаблоне
        if ( ! $templates = $csobj->get_filter_lessons_cstream(null,null,null,null,$cstreamid,null,'on') )
        {// если записей не нашли, то возвращаем 0
            return 0;
        }
        return count($templates);
    }

    /** Возвращает html-код справки
     * @return string 
     */
    public function get_help()
    {
        return '<b>'.$this->dof->get_string('help', 'cstreams').':</b><br>
               - '.$this->dof->get_string('help_choise', 'cstreams').'.<br>';
    }
}

?>