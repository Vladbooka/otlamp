<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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
/*
 * Формы для журнала
 */
require_once('lib.php');
// содключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * кнопка отмены урока
 *
 */
class dof_im_journal_form_cancel_lesson extends dof_modlib_widgets_form
{
    protected $dof;
    function definition()
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header', 'cancelname', $this->dof->get_string('lesson_cancel_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);
        $mform->addElement('checkbox', 'yes_cancel',null, $this->dof->get_string('сonfirmation_cancel_lesson','journal'));
        $mform->setDefault('yes_cancel', 0);
        // Кнопка "отменить"
        $mform->addElement('submit', 'lesson_cancel', $this->dof->get_string('lesson_cancel','journal'));
        $mform->disabledIf('lesson_cancel', 'yes_cancel');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}

/** Перенос уроков
 *
 */
class dof_im_journal_form_replace_event extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    function definition()
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header','transfer_lesson',
                $this->dof->get_string('lesson_transfer_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid',$this->_customdata->eventid);
        $mform->setType('eventid', PARAM_INT);
        $mform->addElement('hidden', 'event', 0);
        $mform->addElement('hidden', 'joinid', 0);
        $mform->setType('event', PARAM_INT);
        $mform->setType('joinid', PARAM_INT);
        // настройки для элемента datetimeselector
        $options = array();
        $options['startyear'] = dof_userdate(time()-5*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
        $options['optional']  = true;
        $options['enabled']  = true;
        $options['defaulttime']  = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'date');
        $event_types = $this->dof->modlib('refbook')->get_event_types();
        $mform->addElement('radio', 'type', null, $event_types['normal'],'normal');
        $mform->addElement('radio', 'type', null, $event_types['combination'],'combination');
        $mform->addElement('radio', 'type', null, $event_types['free'],'free');
        $mform->setDefault('type','normal');
        //покажем меню выбора даты
        if  ( $this->dof->im('journal')->is_access('replace_schevent:date_dis',$this->_customdata->eventid) OR
                $this->dof->im('journal')->is_access('replace_schevent:date_dis/own',$this->_customdata->eventid) OR
                $this->dof->im('journal')->is_access('replace_schevent:date_int',$this->_customdata->eventid))
        {
            $mform->addElement('date_time_selector', 'date', $this->dof->get_string('new_lesson_date','journal').':',$options);
        }
        // замена учителя
        if ( $this->dof->im('journal')->is_access('replace_schevent:teacher',$this->_customdata->eventid) )
        {
            $cstreamid = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'cstreamid');
            $teachers = $this->get_list_teachers($this->dof->storage('cstreams')->get_field($cstreamid, 'programmitemid'));
            $mform->addElement('select', 'teacher', $this->dof->get_string('new_teacher','journal'),$teachers);
            $appointmentid = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'appointmentid');
            if ( ! $appointmentid )
            {// у события нет учителя - поставим учителья потока
                $appointmentid = $this->dof->storage('cstreams')->get_field($cstreamid, 'appointmentid');
            }
            $mform->setDefault('teacher', $appointmentid);

        }
        // Кнопка "применить"
        $mform->addElement('submit', 'replace_lesson', $this->dof->modlib('ig')->igs('next').
                '/'.$this->dof->modlib('ig')->igs('replace'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Добавляет необходимые поля
     * @return void
     */
    function definition_after_data()
    {
        $mform =& $this->_form;
    }

    function choice_event($formdata)
    {
        $mform =& $this->_form;
        if ( $mform->elementExists('replace_lesson') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('replace_lesson');
        }
        if ( $mform->elementExists('joinid') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('joinid');
        }
        $mform->setConstant('event',1);
        if ( $mform->elementExists('type') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('type');
            $mform->removeElement('type');
            $mform->removeElement('type');
        }
        $mform->addElement('hidden', 'type', $formdata->type);
        $event_types = $this->dof->modlib('refbook')->get_event_types();
        $mform->addElement('static', 'htype', null, $event_types[$formdata->type]);
        if ( $mform->elementExists('date') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('date');
        }
        $mform->addElement('hidden', 'date', $formdata->date);
        $date = dof_userdate($formdata->date,'%d-%m-%Y %H:%M');
        if ( $formdata->date == 0 )
        {
            $date = $this->dof->modlib('ig')->igs('no_specify_jr');
        }
        $mform->addElement('static', 'hdate', $this->dof->get_string('new_lesson_date','journal'), $date);
        if ( $mform->elementExists('teacher') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('teacher');
        }
        $mform->addElement('hidden', 'teacher', $formdata->teacher);
        $person = $this->dof->storage('appointments')->get_person_by_appointment($formdata->teacher);
        $fio = $this->dof->storage('persons')->get_fullname($person);
        if ( $formdata->teacher == 0 )
        {
            $fio = $this->dof->modlib('ig')->igs('no_specify_mr');
        }
        $mform->addElement('static', 'hteacher', $this->dof->get_string('new_teacher','journal'), $fio);
        //$mform->addElement('hidden', 'date', $formdata->date);
        //$mform->addElement('hidden', 'teacher', $formdata->teacher);
        //$mform->addElement('hidden', 'type', $formdata->type);
        $events = $this->get_list_events($formdata->date,$formdata->teacher);
        $mform->addElement('select', 'joinid', $this->dof->get_string('new_event','journal'),$events);
        // Кнопка "применить"
        $mform->addElement('submit', 'replace_lesson', $this->dof->modlib('ig')->igs('replace'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /** Проверка данных на стороне сервера
     * @return
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        if ( isset($data['postpone_lesson']) )
        {// если переносим урок
            //return $errors;
        }
        $access = $this->dof->im('journal')->is_access_replace($data['eventid']);
        // проверим по времени
        $cstreamid = $this->dof->storage('schevents')->get_field($data['eventid'], 'cstreamid');
        $ageid = $this->dof->storage('cstreams')->get_field($cstreamid, 'ageid');
        $age = $this->dof->storage('ages')->get($ageid);
        if ( ($data['date'] < $age->begindate OR $data['date'] > $age->enddate)
                AND ! $this->dof->is_access('datamanage') )
        {// даты начала и окончания события не должны вылезать за границы периода
            //$errors['date'] = $this->dof->get_string('err_date','journal',
            //    date('Y/m/d', time()).'-'.date('Y/m/d', $age->enddate));
        }
        if ( ! $access->ignorolddate )
        {// игнорировать новую дату урока нельзя
            if ( $data['date'] < time() )
            {// переносить можно только на еще не наступившее время
                //$errors['date'] = $this->dof->get_string('err_date_postfactum','journal');
            }
            // @todo если границы бутут определятся в конфиге сделаем потом через него

            // @todo сделать проверку, если у ученика или учителя уже есть на это время уроки
        }
        // если ошибки есть - то пользователь вернется на страницу редактирования и увидит их
        return $errors;
    }

    /** Возвращает массив персон
     *
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        if ( is_int_string($pitemid) )
        {// если передан id предмета, выведем только учителей предмета
            $teachers = $this->dof->storage('teachers')->get_records(array
                    ('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( $teachers AND isset($teachers) )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            foreach ( $persons as $id=>$person )
            {// составляем название комплекта: категория + код
                $positionid = $this->dof->storage('schpositions')->
                get_field($this->dof->storage('appointments')->
                        get_field($id,'schpositionid'), 'positionid');
                $departmentid = $this->dof->storage('appointments')->
                get_field($id,'departmentid');
                $fullname = $this->dof->storage('persons')->get_fullname($person);
                $position = $this->dof->storage('positions')->get_field($positionid,'name');
                $depcode  = $this->dof->storage('departments')->get_field($departmentid,'code');
                $rez[$id] = "$fullname [$position / $person->enumber / $depcode]";
            }
            asort($rez);
        }

        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        return $rez;
    }

    /** Возвращает массив персон
     *
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_events($date = 0,$appointid=null)
    {
        $rez = array();
        // получаем список всех кто может преподавать
        $params = new stdClass;
        $params->status = array('plan');
        if ( !empty($date) )
        {// если передан id учителя - ищем по нему
            $params->date = $date;
            $params->status[] = 'completed';
        }else
        {
            $params->date_from = time()-7*24*60*60;
            $params->date_to = time()+365*24*60*60;
        }
        if ( !empty($appointid) )
        {// если передан id учителя - ищем по нему
            $params->appointmentid = $appointid;
            $params->status[] = 'completed';
        }
        $select = $this->dof->storage('schevents')->get_select_listing($params);
        $events = $this->dof->storage('schevents')->get_records_select($select,null,'date');
        if ( empty($events) )
        {
            return $this->dof_get_select_values();
        }
        foreach ( $events as $event )
        {
            if ( $event->id == $this->_customdata->eventid )
            {// себя самого исключаем
                continue;
            }
            $pitemid = $this->dof->storage('cstreams')->get_field($event->cstreamid,'programmitemid');
            $pitem = $this->dof->storage('programmitems')->get($pitemid);
            $person = $this->dof->storage('appointments')->get_person_by_appointment($event->appointmentid);
            $fullname = $this->dof->storage('persons')->get_fullname($person);
            $eventdate = dof_userdate($event->date,'%d-%m-%Y %H:%M');
            $rez[$event->id] = "$pitem->name [$pitem->code] $fullname $eventdate";
        }
        return $rez;
    }

    /** Возвращает массив персон
     *
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    public function process()
    {
        $addvars = '';
        //обработчик формы
        $formdata = $this->get_data();
        if ( isset($formdata->joinid) AND $formdata->type != 'normal' AND
                $formdata->joinid == 0 AND $formdata->event == 1 )
        {
            return $this->dof->get_string('err_joinid','journal');
        }
        if ( $this->is_submitted() AND confirm_sesskey() )
        {//даные переданы в текущей сессии - получаем
            $addvars['departmentid'] = $formdata->departmentid;
            if ( $formdata->type != 'normal' AND $formdata->event == 0 )
            {
                $this->choice_event($formdata);
            }else
            {
                $replace = new stdClass;
                $replace->date = $formdata->date;
                $replace->appointmentid = $formdata->teacher;
                $replace->type = $formdata->type;
                $replace->joinid = $formdata->joinid;
                if ( $formdata->joinid AND empty($formdata->date) )
                {// время не передано - вставим из совместного урока
                    $replace->date = $this->dof->storage('schevents')->get_field($formdata->joinid,'date');
                }
                if ( $formdata->joinid AND empty($formdata->teacher) )
                {// учитель не задан - вставим из совместного урока
                    $replace->appointmentid = $this->dof->storage('schevents')->get_field($formdata->joinid,'appointmentid');
                }
                $expression = $this->dof->storage('schevents')->replace_events($formdata->eventid, $replace);

                if ( empty($formdata->date) )
                {
                    $replace->date = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'date');
                }
                $addvars['date_from'] = $replace->date;
                $addvars['date_to'] = $replace->date;
                $person = $this->dof->storage('appointments')->get_person_by_appointment($replace->appointmentid);
                $addvars['personid'] = $person->id;
                $path = $this->dof->url_im('journal','/show_events/show_events.php',$addvars);
                redirect($path,'',0);
            }
        }
    }

}

/**
 * Форма установки посещаемости занятия
 */
class dof_im_journal_students_presence extends dof_modlib_widgets_form
{
    /**
     * Код логгера
     *
     * @var string
     */
    protected $loggercode = 'dof_im_journal_students_presence';

    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof;

    /**
     * GET параметры для ссылок
     *
     * @var array
     */
    protected $addvars = [];

    /**
     * URL для возврата
     *
     * @var string
     */
    protected $returnurl = null;

    /**
     * Занятие
     *
     * @var dof_lesson
     */
    protected $lesson = null;

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->lesson = $this->_customdata->lesson;
        $this->addvars = $this->_customdata->addvars;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }

        // Сохранение якоря
        $mform->addElement('hidden', 'anchor');
        $mform->setType('anchor', PARAM_TEXT);

        // Идентификатор события
        $eventid = $this->lesson->get_event()->id;

        // Установка посещаемости для каждого слушателя
        $listeners = $this->lesson->get_listeners_data();
        foreach ( $listeners as $cpassedid => $listener )
        {
            if ( ($this->lesson->get_event()->date < $listener['cpassed']->begindate) ||
                    ($this->lesson->get_event()->date > $listener['cpassed']->enddate) )
            {
                continue;
            }

            $presence = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_present_status($listener['person']->id, $this->lesson->get_event()->id);
            if ( $presence === false )
            {
                // Студент не обучался на этом занятии
                continue;
            }

            $prefix = '__' . $cpassedid . '__' . $this->lesson->get_event()->id;
            // Заголовок
            $mform->addElement(
                'header',
                'header'.$prefix,
                $this->dof->storage('persons')->get_fullname($listener['person'])
            );
            $mform->setExpanded('header'.$prefix, true, true);

            $group = [];
            // Флаг отсутствия
            $group[] = $mform->createElement(
                'advcheckbox',
                'absenteeism'.$prefix,
                '',
                $this->dof->get_string('form_presence_reason_label','journal')
            );

            // AJAX параметры для выпадающего списка причин отсутствия
            $ajaxparams = $this->get_autocomplete_params('reasons_for_absence');
            // Установка присутствия
            $presences = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_student_presences($eventid, $listener['person']->id);
            $presence = array_pop($presences);
            if ( $presence )
            {// Найдена посещаемость
                if ( is_null($presence->present) || ! empty($presence->present) )
                {
                    $mform->setDefault('absenteeism'.$prefix, 0);
                } else
                {
                    $mform->setDefault('absenteeism'.$prefix, 1);
                }

                if ( ! empty($presence->reasonid) )
                {
                    $reason_record = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_reason($presence->reasonid);

                    // Отображение причины
                    $ajaxparams['default'] = [$reason_record->id => $reason_record->name];
                }
            } else
            {
                $mform->setDefault('absenteeism'.$prefix, 0);
            }

            $group[] = $mform->createElement(
                'dof_autocomplete',
                'absenteeismreason'.$prefix,
                '',
                [],
                $ajaxparams
            );
            $mform->addGroup(
                $group,
                'group'.$prefix,
                $this->dof->get_string('form_presence_absence_label', 'journal'),
                '',
                false
            );

            $mform->disabledIf('absenteeismreason'.$prefix, 'absenteeism'.$prefix);

            // Комментарий
            $comments = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_student_comments($eventid, $listener['person']->id);
            $commenttext = '';
            if ( ! empty($comments) )
            {
                $comment = array_pop($comments);
                $commenttext = '';
                if ( $comment )
                {
                    $commenttext = $comment->text;
                }
            }
            $mform->addElement(
                'textarea',
                'comment'.$prefix,
                $this->dof->get_string('form_presence_field_comment', 'journal')
            );
            $mform->setDefault('comment'.$prefix, $commenttext);
        }

        if ( $listeners )
        {
            // Кнопка сохранения данных
            $group = [];
            $group[] = $mform->createElement(
                'submit',
                'submit_'.$eventid,
                $this->dof->get_string('page_form_save_submit_label', 'journal')
            );
            $mform->addGroup(
                $group,
                'actions',
                ' ',
                ' ',
                false
            );
        }
    }

    /**
     * Получение идентиифкатора формы
     *
     * @return string
     */
    protected function get_form_identifier()
    {
        $identifier = parent::get_form_identifier();
        $event = $this->_customdata->lesson->get_event();
        if ( $event )
        {
            return $identifier.$event->id;
        }
        return $identifier;
    }

    /**
     * Отображение секции результата
     *
     * @param bool $result
     * @param string $result_string
     *
     * @return void
     */
    protected function show_result($result = false)
    {
        if ( $result )
        {
            $this->dof->messages->add($this->dof->get_string('edit_success','journal'), DOF_MESSAGE_SUCCESS, $this->loggercode);
        } else
        {
            $this->dof->messages->add($this->dof->get_string('edit_fail','journal'), DOF_MESSAGE_ERROR, $this->loggercode);
        }

        if ( (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_ERROR)) &&
                (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_WARNING)) )
        {
            $this->dof->modlib('nvg')->add_js('im', 'journal', '/group_journal/js/closemodal.js', false);
        }
    }

    /**
     * Параметры для AJAX
     *
     * @return array
     */
    protected function get_autocomplete_params($type)
    {
        // Дефолтный параметр
        $default = 'reasons_for_absence';

        // Массив опций
        $options = [];
        $options['plugintype'] = "storage";
        $options['plugincode'] = "schabsenteeism";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';

        // Добавляем возможность создания новой причины
        $options['extoptions'] = new stdClass;

        // Создать уважительную
        $options['extoptions']->create_explained = new stdClass();
        $options['extoptions']->create_explained->string = $this->dof->get_string('create_explained','journal');

        // Создать неуважительную
        $options['extoptions']->create_unexplained = new stdClass();
        $options['extoptions']->create_unexplained->string = $this->dof->get_string('create_unexplained','journal');

        // Тип данных для автопоиска
        switch ( $type )
        {
            case 'reasons_for_absence':
                $options['querytype'] = "reasons_for_absence";
                break;

            default:
                $options['querytype'] = $default;
                break;
        }

        // Вернем опции
        return $options;
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = parent::validation($data, $files);

        // Валидация данных
        foreach ( $data as $field => $value )
        {
            $fielddata = explode('__', $field);
            if ( count($fielddata) == 3 )
            {
                $fieldname = $fielddata[0];
                $cpassedid = $fielddata[1];

                if ( $fieldname == 'absenteeism' )
                {// Валидация данных подписки на дисциплину
                    $listeners = $this->lesson->get_listeners_data();
                    if ( ! isset($listeners[$cpassedid]) )
                    {
                        $errors[$field] = $this->dof->get_string('form_presence_error_cpassed_invalid', 'journal');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            // Массив результатов
            $students_result = [];
            $student_comments = [];
            $listeners = $this->lesson->get_listeners_data();
            // Валидация данных
            foreach ( $formdata as $field => $value )
            {
                $fielddata = explode('__', $field);
                if ( count($fielddata) == 3 )
                {
                    $fieldname = $fielddata[0];
                    $cpassedid = $fielddata[1];
                    $eventid = $fielddata[2];

                    if ( $fieldname == 'absenteeism' )
                    {// Обработка данных подписки на дисцилину

                        $listener = $listeners[$cpassedid];

                        // Стандартные данные по посещаемости
                        $person = new stdClass();
                        $person->eventid = $this->lesson->get_event()->id;
                        $person->personid = $listener['person']->id;
                        if ( ! empty($value) )
                        {
                            $person->present = 0;
                        } else
                        {
                            $person->present = 1;
                        }
                        $person->reasonid = 0;

                        if ( $person->present == 0 )
                        {// Пользователь отсутствовал

                            if ( ! empty($formdata->{'absenteeismreason__' . $cpassedid . '__' . $eventid}) )
                            {// Поле причины
                                $reason = $formdata->{'absenteeismreason__' . $cpassedid . '__' . $eventid};

                                if ( trim($reason['absenteeismreason__' . $cpassedid . '__' . $eventid]) )
                                {// Указана причина отсутствия
                                    // Сохранение причины
                                    $person->reasonid = $this->dof->storage('schabsenteeism')->handle_reason(
                                        'absenteeismreason__' . $cpassedid . '__' . $eventid,
                                        $reason
                                    );
                                }
                            }
                        }
                        $students_result[$listener['person']->id] = $person;

                        // Комментарии по пользователю
                        $comments = $this->dof->modlib('journal')->get_manager('lessonprocess')
                            ->get_student_comments($this->lesson->get_event()->id, $listener['person']->id);
                        $comment = array_pop($comments);

                        // Посещаемость пользователя
                        $presences = $this->dof->modlib('journal')
                            ->get_manager('lessonprocess')
                            ->get_student_presences($this->lesson->get_event()->id, $listener['person']->id);
                        $presence = array_pop($presences);

                        // Заполняем объект комментария для сохранения
                        if( ! empty($formdata->{'comment__' . $cpassedid . '__' . $eventid}) )
                        {
                            $comment_obj = new stdClass();
                            $comment_obj->userid = $listener['person']->id;
                            $comment_obj->comment = $formdata->{'comment__' . $cpassedid . '__' . $eventid};
                            if ( isset($comment->id) )
                            {
                                $comment_obj->commentid = $comment->id;
                            }
                            if ( isset($presence->id) )
                            {
                                $comment_obj->presenceid = $presence->id;
                            }

                            $student_comments[$listener['person']->id] = $comment_obj;
                        }
                    }
                }
            }

            // Сохранение посещения
            $result_presence = $this->dof->modlib('journal')->get_manager('lessonprocess')
                ->save_students_presence($this->lesson->get_event()->id, $students_result, $this->addvars['departmentid']);

            // Сохранение посещения
            if ( ! empty($student_comments) )
            {
                $result_comments = $this->dof->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->save_students_comments($student_comments);
            }

            // Смена статуса
            $result_changestatus_schevent = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->schevent_complete($this->lesson->get_event()->id, $this->addvars['departmentid']);
            if ( ! $result_changestatus_schevent )
            {
                $this->dof->messages->add($this->dof->get_string('warning_cannot_change_status_schevent', 'journal'), DOF_MESSAGE_WARNING, $this->loggercode);
            }

            // Отображение результата
            $this->show_result($result_presence);
        }
    }


    /**
     * Отображение посещаемости студентов
     *
     * @return bool
     */
    protected function show_students_presence()
    {
        // Получим ивент
        if ( ! empty($this->eventid) )
        {
            $event = $this->dof->storage('schevents')->get($this->eventid);
            $event = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_schevent($this->eventid);

            if ( ! empty($event) )
            {// Событие найдено, начинаем процесс отображения студентов
                // Заполним подписки на учебный процесс
                $this->generate_cpasseds();

                if ( ! empty($this->cpasseds) )
                {// Есть подписанные студенты на учебный процесс
                    foreach ( $this->cpasseds as $student_cpassed )
                    {
                        $this->show_student_presence($student_cpassed);
                    }
                } else
                {
                    return false;
                }
            } else
            {
                return false;
            }
        }

        // Все прошло успешно и без ошибок
        return true;
    }
}

/**
 * Форма установки посещаемости занятия
 */
class dof_im_journal_student_progress extends dof_modlib_widgets_form
{
    /**
     * Код логгера
     *
     * @var string
     */
    protected $loggercode = 'dof_im_journal_student_progress';

    /**
     * Контроллер Деканата
     *
     * @var dof_control
     */
    protected $dof;

    /**
     * GET параметры для ссылок
     *
     * @var array
     */
    protected $addvars = [];

    /**
     * URL для возврата
     *
     * @var string
     */
    protected $returnurl = null;

    /**
     * Занятие
     *
     * @var dof_lesson
     */
    protected $lesson = null;

    /**
     * Данные о пользователе
     *
     * @var array
     */
    protected $user_info = [];

    /**
     * Идентификатор учебного процесса
     *
     * @var int
     */
    protected $cstreamid = null;

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->lesson = $this->_customdata->lesson;
        $this->addvars = $this->_customdata->addvars;
        $this->cstreamid = $this->_customdata->cstreamid;

        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }

        // Сохранение якоря
        $mform->addElement('hidden', 'anchor');
        $mform->setType('anchor', PARAM_TEXT);

        // Установка посещаемости для каждого слушателя
        $listeners = $this->lesson->get_listeners_data();

        // Подписка, по которой будет
        $listener = null;
        foreach ( $listeners as $listener_info )
        {
            if ( ! empty($listener_info['cpassed']->id) && ( $this->_customdata->cpassed == $listener_info['cpassed']->id ) )
            {
                $this->user_info = $listener = $listener_info;
            }
        }

        // Право на сохранение формы
        $cansave = true;
        if ( ! empty($listener) )
        {
            // Заголовок
            $mform->addElement(
                    'html',
                    dof_html_writer::nonempty_tag('h2', $this->dof->storage('persons')->get_fullname($listener['person']))
                    );

            // Н/О - слушатель не обучался
            if ( $this->lesson->event_exists() )
            {
                $access = $this->dof->modlib('journal')->get_manager('lessonprocess')->can_change_not_studied($this->lesson->get_event()->id);

                $mform->addElement('advcheckbox', 'notstudied', $this->dof->get_string('form_presence_notstudied_label', 'journal'));

                $presence = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_present_status($listener['person']->id, $this->lesson->get_event()->id);
                if ( $presence === false )
                {
                    if ( ! $access )
                    {
                        // Стоит галка о том, что студент не обучался и нет прав
                        $cansave = false;
                    }

                    // Студент не обучался на этом занятии
                    $mform->setDefault('notstudied', 1);
                } else
                {
                    $mform->setDefault('notstudied', 0);
                }

                if ( ! $access )
                {
                    $mform->freeze('notstudied');
                }
            }

            // Оценка
            if ( $this->lesson->plan_exists() &&
                    $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_grades($this->lesson->get_plan()->id, $this->cstreamid, $this->addvars['departmentid']) )
            {
                // Получение данных о работе на занятии
                $gradedata = $this->lesson->get_listener_gradedata($listener['cpassed']->id);

                // Получение шкалы оценок
                $scale = $this->dof->modlib('journal')
                    ->get_manager('scale')
                    ->get_plan_scale($this->lesson->get_plan());

                if ( ! empty($scale) &&
                        ! empty($gradedata) &&
                        ( $gradedata->overenroltime === false ) )
                {
                    $selected = '';
                    $workingoff = 0;
                    if ( ! empty($gradedata->grades) )
                    {
                        $grade = array_shift($gradedata->grades);
                        if ( ! empty($grade) )
                        {
                            $selected = $grade->item->grade;
                            $workingoff = $grade->item->workingoff;
                        }
                    }
                    $scale = [ '' => '-' ] + $scale;

                    // оценка
                    $mform->addelement('select', 'grade', $this->dof->get_string('choose_grade', 'journal'), $scale);
                    $mform->setDefault('grade', $selected);

                    // флаг отработки
                    $mform->addelement('checkbox', 'workingoff', $this->dof->get_string('workingoff', 'journal'));
                    $mform->setDefault('workingoff', $workingoff);
                    $mform->disabledIf('workingoff', 'grade', 'eq', '');
                    $this->add_help('workingoff', 'workingoff', 'journal');

                    if ( $mform->elementExists('notstudied') )
                    {
                        $mform->disabledIf('grade', 'notstudied', 'checked');
                        $mform->disabledIf('workingoff', 'notstudied', 'checked');
                    }
                    if ( ! $this->lesson->can_set_grade($listener['cpassed']) )
                    {
                        // нельзя выставлять оценку
                        $mform->freeze(['grade']);
                    }
                }
            }

            // Посещаемость
            if ( $this->lesson->event_exists() && $this->dof->modlib('journal')->get_manager('lessonprocess')->can_save_presence($this->lesson->get_event()->id, $this->addvars['departmentid']) )
            {
                // Идентификатор события
                $eventid = $this->lesson->get_event()->id;

                $group = [];
                // Флаг отсутствия
                $group[] = $mform->createElement(
                        'advcheckbox',
                        'absenteeism',
                        '',
                        $this->dof->get_string('form_presence_reason_label','journal')
                        );

                // AJAX параметры для выпадающего списка причин отсутствия
                $ajaxparams = $this->get_autocomplete_params('reasons_for_absence');

                // Установка присутствия
                $presences = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_student_presences($eventid, $listener['person']->id);
                $presence = array_pop($presences);
                if ( $presence )
                {// Найдена посещаемость
                    if ( is_null($presence->present) || ! empty($presence->present) )
                    {
                        $mform->setDefault('absenteeism', 0);
                    } else
                    {
                        $mform->setDefault('absenteeism', 1);
                    }

                    if ( ! empty($presence->reasonid) )
                    {
                        $reason_record = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_reason($presence->reasonid);

                        // Отображение причины
                        $ajaxparams['default'] = [$reason_record->id => $reason_record->name];
                    }
                } else
                {
                    $mform->setDefault('absenteeism', 0);
                }

                $group[] = $mform->createElement(
                        'dof_autocomplete',
                        'absenteeismreason',
                        'test',
                        [],
                        $ajaxparams
                        );
                $mform->addGroup(
                        $group,
                        'group',
                        $this->dof->get_string('form_presence_absence_label', 'journal'),
                        '',
                        false
                        );

                $mform->disabledIf('absenteeismreason', 'absenteeism');

                // Комментарий
                $comments = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_student_comments($eventid, $listener['person']->id);
                $comment = array_pop($comments);
                $commenttext = '';
                if ( $comment )
                {
                    $commenttext = $comment->text;
                }
                $mform->addElement(
                        'textarea',
                        'comment',
                        $this->dof->get_string('form_presence_field_comment', 'journal')
                        );
                $mform->setDefault('comment', $commenttext);

                $mform->disabledIf('comment', 'notstudied', 'checked');
                $mform->disabledIf('absenteeism', 'notstudied', 'checked');
                $mform->disabledIf('absenteeismreason', 'notstudied', 'checked');
            }
        }

        if ( $listener && $cansave )
        {
            // Кнопка сохранения данных
            $group = [];
            $group[] = $mform->createElement(
                    'submit',
                    'submit',
                    $this->dof->get_string('page_form_save_submit_label', 'journal')
                    );
            $mform->addGroup(
                    $group,
                    'actions',
                    ' ',
                    ' ',
                    false
                    );
        }
    }

    /**
     * Получение идентиифкатора формы
     *
     * @return string
     */
    protected function get_form_identifier()
    {
        $identifier = parent::get_form_identifier();
        if ( ! empty($this->user_info) )
        {
            $identifier .= $this->user_info['cpassed']->id;
        }

        return $identifier;
    }

    /**
     * Отображение секции результата
     *
     * @param bool $result
     * @param string $result_string
     *
     * @return void
     */
    protected function show_result($result = false)
    {
        if ( $result )
        {
            $this->dof->messages->add($this->dof->get_string('edit_success','journal'), DOF_MESSAGE_SUCCESS, $this->loggercode);
        } else
        {
            $this->dof->messages->add($this->dof->get_string('edit_fail','journal'), DOF_MESSAGE_ERROR, $this->loggercode);
        }

        if ( (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_ERROR)) &&
                (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_WARNING)) )
        {
            $this->dof->modlib('nvg')->add_js('im', 'journal', '/group_journal/js/closemodal.js', false);
        }
    }

    /**
     * Параметры для AJAX
     *
     * @return array
     */
    protected function get_autocomplete_params($type)
    {
        // Дефолтный параметр
        $default = 'reasons_for_absence';

        // Массив опций
        $options = [];
        $options['plugintype'] = "storage";
        $options['plugincode'] = "schabsenteeism";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';

        // Добавляем возможность создания новой причины
        $options['extoptions'] = new stdClass;

        // Создать уважительную
        $options['extoptions']->create_explained = new stdClass();
        $options['extoptions']->create_explained->string = $this->dof->get_string('create_explained','journal');

        // Создать неуважительную
        $options['extoptions']->create_unexplained = new stdClass();
        $options['extoptions']->create_unexplained->string = $this->dof->get_string('create_unexplained','journal');

        // Тип данных для автопоиска
        switch ( $type )
        {
            case 'reasons_for_absence':
                $options['querytype'] = "reasons_for_absence";
                break;

            default:
                $options['querytype'] = $default;
                break;
        }

        // Вернем опции
        return $options;
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
                $this->is_validated() && $formdata = $this->get_data()
                )
        {// Обработка данных формы
            global $DOF;

            // Результат сохранения
            $result = true;
            if ( ! empty($this->user_info) )
            {
                $listener = $this->user_info;

                // Проверим, что была выставлена галка Н/О
                if ( property_exists($formdata, 'notstudied') )
                {
                    if ( ! empty($formdata->notstudied) )
                    {
                        $planid = 0;
                        $eventid = 0;
                        if ( $this->lesson->event_exists() )
                        {
                            $eventid = $this->lesson->get_event()->id;
                        }
                        if ( $this->lesson->plan_exists() )
                        {
                            $planid = $this->lesson->get_plan()->id;
                        }

                        // Ученик не обучался, создадим приказ и очистим все данные (оценка/посещаемость)
                        $result = $this->dof->modlib('journal')
                            ->get_manager('lessonprocess')
                            ->remove_student_lesson_data($this->cstreamid, $planid, $eventid, $listener, $this->addvars['departmentid']);

                        $this->show_result($result);
                        return;
                    }
                }

                // Сохранение данных о посещаемости
                if ( $this->lesson->event_exists() )
                {
                    // Массив результатов
                    $students_result = [];
                    $student_comments = [];

                    // Стандартные данные по посещаемости
                    $person = new stdClass();
                    $person->eventid = $this->lesson->get_event()->id;
                    $person->personid = $listener['person']->id;
                    if ( ! empty($formdata->absenteeism) )
                    {
                        $person->present = 0;
                    } else
                    {
                        $person->present = 1;
                    }
                    $person->reasonid = 0;

                    if ( $person->present == 0 )
                    {// Пользователь отсутствовал

                        if ( ! empty($formdata->absenteeismreason) )
                        {// Поле причины
                            $reason = $formdata->absenteeismreason;

                            if ( trim($reason['absenteeismreason']) )
                            {// Указана причина отсутствия

                                // Сохранение причины
                                $person->reasonid = $this->dof->storage('schabsenteeism')->handle_reason(
                                        'absenteeismreason',
                                        $reason
                                        );
                            }
                        }
                    }
                    $students_result[$listener['person']->id] = $person;

                    // Комментарии по пользователю
                    $comments = $this->dof->modlib('journal')->get_manager('lessonprocess')
                        ->get_student_comments($this->lesson->get_event()->id, $listener['person']->id);
                    $comment = array_pop($comments);

                    // Посещаемость пользователя
                    $presences = $this->dof->modlib('journal')
                        ->get_manager('lessonprocess')
                        ->get_student_presences($this->lesson->get_event()->id, $listener['person']->id);
                    $presence = array_pop($presences);

                    // Заполняем объект комментария для сохранения
                    if( ! empty($formdata->{'comment'}) )
                    {
                        $comment_obj = new stdClass();
                        $comment_obj->userid = $listener['person']->id;
                        $comment_obj->comment = $formdata->comment;
                        if ( isset($comment->id) )
                        {
                            $comment_obj->commentid = $comment->id;
                        }
                        if ( isset($presence->id) )
                        {
                            $comment_obj->presenceid = $presence->id;
                        }

                        $student_comments[$listener['person']->id] = $comment_obj;
                    }

                    // Сохранение посещения
                    $result = $this->dof->modlib('journal')
                        ->get_manager('lessonprocess')
                        ->save_students_presence($this->lesson->get_event()->id, $students_result, $this->addvars['departmentid']) &&
                            $result;

                    // Смена статуса
                    $changestatus = $this->dof->modlib('journal')
                        ->get_manager('lessonprocess')
                        ->schevent_complete($this->lesson->get_event()->id, $this->addvars['departmentid']);
                    if ( ! $changestatus )
                    {
                        $this->dof->messages->add($this->dof->get_string('warning_cannot_change_status_schevent', 'journal'), DOF_MESSAGE_WARNING, $this->loggercode);
                    }

                    // Сохранение посещения
                    if ( ! empty($student_comments) )
                    {
                        $result = $this->dof->modlib('journal')
                            ->get_manager('lessonprocess')
                            ->save_students_comments($student_comments) &&
                                $result;
                    }
                }

                // Сохранение данных об оценке
                if ( $this->lesson->plan_exists() )
                {
                    if ( ! empty($formdata->grade) )
                    {
                        $grade = $formdata->grade;
                        $workingoff = ! empty($formdata->workingoff) ? 1 : 0;
                    } else
                    {
                        $grade = '';
                        $workingoff = 0;
                    }

                    $grades = [[
                        'cpassedid' => $listener['cpassed']->id,
                        'grade' => $grade,
                        'workingoff' => $workingoff
                    ]];
                    $result = $this->dof->modlib('journal')
                            ->get_manager('lessonprocess')
                            ->save_students_grades($this->addvars['csid'], $this->lesson->get_plan(), null, $grades) && $result;
                }
            }

            $this->show_result($result);
        }
    }


    /**
     * Отображение посещаемости студентов
     *
     * @return bool
     */
    protected function show_students_presence()
    {
        // Получим ивент
        if ( ! empty($this->eventid) )
        {
            $event = $this->dof->storage('schevents')->get($this->eventid);
            $event = $this->dof->modlib('journal')
            ->get_manager('lessonprocess')
            ->get_schevent($this->eventid);

            if ( ! empty($event) )
            {// Событие найдено, начинаем процесс отображения студентов
                // Заполним подписки на учебный процесс
                $this->generate_cpasseds();

                if ( ! empty($this->cpasseds) )
                {// Есть подписанные студенты на учебный процесс
                    foreach ( $this->cpasseds as $student_cpassed )
                    {
                        $this->show_student_presence($student_cpassed);
                    }
                } else
                {
                    return false;
                }
            } else
            {
                return false;
            }
        }

        // Все прошло успешно и без ошибок
        return true;
    }
}

class dof_im_journal_lesson_edit extends dof_modlib_widgets_form
{
    /**
     * Код логгера
     *
     * @var string
     */
    protected $loggercode = 'dof_im_journal_lesson_edit';

    protected $plan;

    protected $event;
    protected $cstream;
    protected $cstreams;

    /**
     * Контроллер деканата
     *
     * @var dof_control
     */
    protected $dof;
    protected $linktype;
    protected $linkid;
    protected $departmentid;

    /**
     * Объект занятия
     *
     * @var dof_lesson
     */
    protected $lesson;
    protected $usertimezone = 99;

    /**
     * Прользователь имеет право на редактирование/создание события
     *
     * @var string
     */
    protected $scheventaccess = false;

    /**
     * Прользователь имеет право на редактирование/создание КТ
     *
     * @var string
     */
    protected $planaccess = false;

    /**
     * Флаг о том, что изначально форма вызывалась для создания занятия
     * @var bool
     */
    protected $create = false;

    /**
     * Доступные курсы Moodle
     *
     * @var array
     */
    protected $courses = [];

    /**
     * Код формы
     * @desc
     * 0 - простой,занятие
     * 1 - простой,событие
     * 2 - простой,кт
     * 3 - сложный,занятие
     * 4 - сложный,событие
     * 5 - сложный,кт
     * @var integer
     */
    protected $formtypecode = 0;

    /**
     * Код хранилища
     *
     * @return string
     */
    protected function storage_code()
    {
        return 'plans';
    }

    /**
     * Код интерфейса
     *
     * @return string
     */
    protected function im_code()
    {
        return 'journal';
    }

    /**
     * Определить, можно ли создавать событие через журнал
     *
     * @return bool
     */
    protected function can_create_event()
    {
        return $this->dof->storage('schevents')->is_access('create');
    }

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    protected function definition()
    {
        if ( isset($this->_customdata->dof) )
        {
            $this->dof = $this->_customdata->dof;
        } else
        {
            GLOBAL $DOF;
            $this->dof = $DOF;
        }
        if ( isset($this->_customdata->cstreams) )
        {
            $this->cstreams = $this->_customdata->cstreams;
        }
        if ( isset($this->_customdata->departmentid) )
        {
            $this->departmentid = $this->_customdata->departmentid;
        } else
        {
            $this->departmentid = $addvars['departmentid'];
        }

        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();

        // Установка часовой зоны текущего пользователя
        $this->usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

        // Установка данных
        $this->setup_local_variables(
            $this->_customdata->planid,
            $this->_customdata->cstreamid,
            $this->_customdata->eventid
        );

        // Установка скрытых полей
        $this->setup_hidden_fields();

        //если передан массив потоков
        if ( ! empty($this->cstreams) )
        {//Отображаем раздел формы, связанный с выбором потоков
            $this->show_cstreams();
        }

        // Право на манипуляции с событием
        $this->scheventaccess = $this->lesson->can_manipulate_schevent($this->cstream->id, $this->departmentid);

        // Право на манипуляции с КТ
        $this->planaccess = $this->lesson->can_manipulate_plan($this->cstream->id, $this->departmentid);

        $content = '';
        $lessontype = '';
        $eventexists = ! empty($this->event->id);
        $planexists = ! empty($this->plan->id);

        $this->create = !$eventexists && !$planexists;

        if ( $eventexists && $planexists )
        {
            $lessontype = 'lesson';
        } elseif ( $eventexists )
        {
            $lessontype = 'event';
        } elseif ( $planexists )
        {
            $lessontype = 'plan';
        }

        $this->formtypecode = optional_param('formtypecode', -1, PARAM_INT);
        if ( $this->formtypecode == -1 )
        {
            $configformtypecode = $this->formtypecode = $this->dof->storage('config')->get_config_value(
                    'initialformstate',
                    'im',
                    'journal',
                    ! empty($this->cstream->departmentid) ? $this->cstream->departmentid : $this->departmentid);

            $code = -1;
            if ( $eventexists && $planexists )
            {
                $code = 0;
            } elseif ( $eventexists )
            {
                $code = 1;
            } elseif ( $planexists )
            {
                $code = 2;
            }
            if ( $code == -1 )
            {// создание
                $code = $configformtypecode;
            } elseif ( !in_array($configformtypecode, [0,1,2]) )
            {// сложный режим
                $code += 3;
            }
            $this->formtypecode = $code;
        }
        $mform->addElement('hidden','formtypecode', $this->formtypecode);
        $mform->setType('formtypecode', PARAM_INT);

        $leftbuttonclass = '';
        $rightbuttonclass = '';
        if ( $this->formtypecode > 2 )
        {
            $rightbuttonclass .= 'switch-simplecomplex-wrapper__button_active';
        } else
        {
            $leftbuttonclass .= 'switch-simplecomplex-wrapper__button_active';
        }

        $switch = dof_html_writer::checkbox('lesson-switch', '', $this->formtypecode > 2 ? true : false, '', ['id' => 'lesson-switch']);
        $switch .= dof_html_writer::div(
                $this->dof->get_string('switch_type_form__simple', 'journal'),
                'switch-simplecomplex-wrapper__button switch-simplecomplex-wrapper__button-simple ' . $leftbuttonclass);
        $switch .= dof_html_writer::label('', 'lesson-switch');
        $switch .= dof_html_writer::div(
                $this->dof->get_string('switch_type_form__complex', 'journal'),
                'switch-simplecomplex-wrapper__button switch-simplecomplex-wrapper__button-complex ' . $rightbuttonclass);
        $switchwrapper = dof_html_writer::div($switch, 'switch-simplecomplex-wrapper');

        $lessonclass = $this->formtypecode == 0 || $this->formtypecode == 3 ? ' switch-lessontype-wrapper__button_active' : '';
        $eventclass = $this->formtypecode == 1 || $this->formtypecode == 4 ? ' switch-lessontype-wrapper__button_active' : '';
        $planclass = $this->formtypecode == 2 || $this->formtypecode == 5 ? ' switch-lessontype-wrapper__button_active' : '';

        // враппер заголовков
        $switchlessontype = '';
        if ( $lessontype == 'lesson' ||
                ($this->scheventaccess && $this->planaccess) ||
                (! empty($this->plan->id && $this->scheventaccess)) ||
                (! empty($this->event->id && $this->planaccess)) )
        {
            $switchlessontype .= dof_html_writer::div(
                    $this->dof->get_string('switch_type_lesson__lesson', 'journal'),
                    'switch-lessontype-wrapper__button' . $lessonclass,
                    ['data-mode' => 'lesson']);
        }
        if ( $lessontype == 'event' ||
                ($this->scheventaccess && (empty($this->plan->id) || (!empty($this->plan->id) && $this->lesson->editform_allowed('cancel', null, $this->departmentid) ))) )
        {
            $switchlessontype .= dof_html_writer::div(
                    $this->dof->get_string('switch_type_lesson__event', 'journal'),
                    'switch-lessontype-wrapper__button' . $eventclass,
                    ['data-mode' => 'event']);
        }
        if ( $lessontype == 'plan' ||
                ($this->planaccess && (empty($this->event->id) || (!empty($this->event->id) && $this->lesson->editform_allowed('cancel', null, $this->departmentid) ))) )
        {
            $switchlessontype .= dof_html_writer::div(
                    $this->dof->get_string('switch_type_lesson__plan', 'journal'),
                    'switch-lessontype-wrapper__button' . $planclass,
                    ['data-mode' => 'plan']);
        }
        $switchlessontypewrapper = dof_html_writer::div($switchlessontype, 'switch-lessontype-wrapper');

        $content .= $switchwrapper;
        $content .= $switchlessontypewrapper;
        $mform->addElement('html', dof_html_writer::div($content, 'switch-head-wrapper'));

        if ( ! empty($this->event->id) )
        {
            // Получение объекта события
            $eventobj = $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->get_schevent($this->event->id);
            if ( ! empty($eventobj->place) )
            {
                // Проверка доступности кабинета в указанное время
                $intersectionevents = $eventobj = $this->dof->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->get_events_intersection_place($this->event->id, $eventobj->date, $eventobj->date + $eventobj->duration, $eventobj->place);
                if ( ! empty($intersectionevents) )
                {
                    // Есть пересечения
                    $this->dof->messages->add(
                            dof_html_writer::div($this->dof->get_string('warning_event_place_intersection_first', 'journal'),
                                    'dof-lesson-intersection-warning') .
                                     $this->dof->get_string('warning_event_place_intersection_second', 'journal'),
                                    DOF_MESSAGE_WARNING, $this->loggercode);
                }
            }
        }

        // Отображаем раздел формы, связанных с событием
        $this->show_event();

        // Отображаем раздел формы, связанный с контрольной точкой
        $this->show_plan();

        // Отображение раздела переноса занятия
        $this->show_replace();

        // Отображение раздела подтверждения занятия
        $this->show_completion();

        // Отображение раздела отмены занятия
        $this->show_cancel();

        if ( $this->planaccess || $this->scheventaccess )
        {
            $mform->addElement('header','general_submit', $this->dof->get_string('form_lesson_edit_general_submit_title','journal'));
            $mform->addElement('submit', 'save_lesson', $this->dof->get_string('form_lesson_edit_general_submit_save','journal'));

            if ( $this->scheventaccess && (empty($this->plan->id) || $this->planaccess) )
            {
                if ( ! empty($this->plan->id) )
                {
                    $options = [
                        'modalbuttonname' => $this->dof->get_string('form_lesson_edit_general_submit_save', 'journal'),
                        'modaltitle' => $this->dof->get_string('warning_change_lessontype__title', 'journal'),
                        'modalcontent' => dof_html_writer::div($this->dof->get_string('warning_change_lessontype_to_event__content', 'journal'), '', ['style' => 'color:red;font-weight:bold;margin: 0 0 20px 0;']),
                        'submitbuttonname' => $this->dof->get_string('warning_change_lessontype__submitbuttonname','journal'),
                        'cancelbuttonname' => $this->dof->get_string('warning_change_lessontype__cancelbuttonname', 'journal')
                    ];
                    $mform->addElement(
                            'dof_confirm_submit',
                            'save_event',
                            'save_event',
                            $options
                            );
                } else
                {
                    $mform->addElement('submit', 'save_event', $this->dof->get_string('form_lesson_edit_general_submit_save','journal'));
                }
            }
            if ( $this->planaccess && (empty($this->event->id) || $this->scheventaccess) )
            {
                if ( ! empty($this->event->id) )
                {
                    $options = [
                        'modalbuttonname' => $this->dof->get_string('form_lesson_edit_general_submit_save', 'journal'),
                        'modaltitle' => $this->dof->get_string('warning_change_lessontype__title', 'journal'),
                        'modalcontent' => dof_html_writer::div($this->dof->get_string('warning_change_lessontype_to_plan__content', 'journal'), '', ['style' => 'color:red;font-weight:bold;margin: 0 0 20px 0;']),
                        'submitbuttonname' => $this->dof->get_string('warning_change_lessontype__submitbuttonname','journal'),
                        'cancelbuttonname' => $this->dof->get_string('warning_change_lessontype__cancelbuttonname', 'journal')
                    ];
                    $mform->addElement(
                            'dof_confirm_submit',
                            'save_plan',
                            'save_plan',
                            $options
                            );
                } else
                {
                    $mform->addElement('submit', 'save_plan', $this->dof->get_string('form_lesson_edit_general_submit_save','journal'));
                }
                $mform->addElement('cancel', 'cancel', $this->dof->get_string('form_lesson_edit_general_cancel','journal'));
            }

            // По умолчанию секция открыта
            $mform->setExpanded('general_submit', true);
        }

        // Стилизация элементов
        $this->style();

        // Применение фильтра ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Стилизация элементов
     * @return void
     */
    protected function style()
    {
        $fieldsdata = [
            'event_form' => ['class' => 'dof-journal-is_event'],
            'event_place' => ['class' => 'dof-journal-is_event'],
            'event_date' => ['class' => 'dof-journal-is_event'],
            'event_ahours' => ['class' => 'dof-journal-is_event'],
            'event_type' => ['class' => 'dof-journal-is_event'],
            'event_url' => ['class' => 'dof-journal-is_event'],
            'event_duration' => ['class' => 'dof-journal-is_event'],
            'plan_creation_type' => ['class' => 'dof-journal-is_plan'],
            'existing_point' => ['class' => 'dof-journal-is_plan'],
            'plansectionsid' => ['class' => 'dof-journal-is_plan'],
            'parentid1' => ['class' => 'dof-journal-is_plan'],
            'parentid2' => ['class' => 'dof-journal-is_plan'],
            'parentid3' => ['class' => 'dof-journal-is_plan'],
            'estimated' => ['class' => 'dof-journal-is_plan'],
            'gradescompulsion' => ['class' => 'dof-journal-is_plan'],
            'scale_flag' => ['class' => 'dof-journal-is_plan'],
            'scale' => ['class' => 'dof-journal-is_plan'],
            'mingrade' => ['class' => 'dof-journal-is_plan'],
            'modulegradesconversation' => ['class' => 'dof-journal-is_plan'],
            'name' => ['class' => 'dof-journal-is_plan'],
            'type' => ['class' => 'dof-journal-is_plan'],
            'homework' => ['class' => 'dof-journal-is_plan'],
            'homeworkhoursgroup' => ['class' => 'dof-journal-is_plan'],
            'note' => ['class' => 'dof-journal-is_plan'],
            'pinpoint_date' => ['class' => 'dof-journal-is_plan dof-journal-ingore_lesson'],
            'mdlsyncgrades' => ['class' => 'dof-journal-is_plan'],
            'mdlcategoryid' => ['class' => 'dof-journal-is_plan'],
            'mdlcourseid' => ['class' => 'dof-journal-is_plan'],
            'mdlgradeitemid' => ['class' => 'dof-journal-is_plan'],
            'gradessynctype' => ['class' => 'dof-journal-is_plan'],
            'gradespriority' => ['class' => 'dof-journal-is_plan'],
            'gradesoverride' => ['class' => 'dof-journal-is_plan'],
            'workingoffautomaticgradechanges' => ['class' => 'dof-journal-is_plan'],
            'workingoffautomaticlessonover' => ['class' => 'dof-journal-is_plan'],
            'save_lesson' => ['class' => 'dof-journal-is_simple dof-journal-is_lesson'],
            'save_event' => ['class' => 'dof-journal-is_simple dof-journal-is_event dof-journal-ingore_lesson'],
            'save_plan' => ['class' => 'dof-journal-is_simple dof-journal-is_plan dof-journal-ingore_lesson'],
            'cancel' => ['class' => 'dof-journal-is_simple dof-journal-is_lesson dof-journal-is_plan dof-journal-is_event'],
        ];

        // получение списка полей, отображаемых в упрощенной форме
        $simplefields = explode(',', $this->dof->storage('config')->get_config_value('simplefields', 'im', 'journal', ! empty($this->cstream->departmentid) ? $this->cstream->departmentid : $this->departmentid));

        $elements = $this->_form->_elements;
        foreach ( $elements as $element )
        {
            $elementname = $element->getName();
            if ( empty($elementname) || ! array_key_exists($elementname, $fieldsdata) )
            {
                continue;
            }
            $attrs = $element->getAttributes();
            $class = ! empty($attrs['class']) ? $attrs['class'] : '';
            if ( in_array($elementname, $simplefields) )
            {
                $class .= ' ' . 'dof-journal-is_simple';
            }
            $attrs['class'] = $class . ' ' . $fieldsdata[$elementname]['class'];
            $element->setAttributes($attrs);
        }
    }

    /**
     * Секция переноса занятия
     *
     * @return void
     */
    protected function show_replace()
    {
        $mform =& $this->_form;

        $eventid = $this->event->id;
        if ( ! empty($eventid) )
        {
            if ( $this->lesson->editform_allowed('replace', null, $this->departmentid) )
            {
                // Заголовок
                $mform->addElement('header','transfer_lesson', $this->dof->get_string('lesson_transfer_title','journal'));

                // Скрытые поля
                $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
                $mform->setType('departmentid', PARAM_INT);

                // Опции для элемента datetimeselector
                $options = [];
                $options['startyear'] = dof_userdate(time() - 5*365*24*3600, '%Y');
                $options['stopyear']  = dof_userdate(time() + 5*365*24*3600, '%Y');
                $options['optional']  = true;

                // Меню выбора даты
                if  ( $this->lesson->editform_allowed('replacetime', null, $this->departmentid) )
                {
                    $mform->addElement('date_time_selector', 'date', $this->dof->get_string('new_lesson_date','journal').':',$options);
                }

                // Учитель
                if ( $this->lesson->editform_allowed('replaceteacher', null, $this->departmentid) )
                {
                    // Получение учителей
                    $teachers = $this->get_list_teachers($this->dof->storage('cstreams')->get_field($this->cstream->id, 'programmitemid'));

                    // Добавление селекта учителей
                    $mform->addElement('select', 'teacher', $this->dof->get_string('new_teacher','journal'), $teachers);
                    $appointmentid = $this->dof->storage('schevents')->get_field($eventid, 'appointmentid');
                    if ( ! $appointmentid )
                    {// У события нет учителя - добавление учителя учебного процесса
                        $appointmentid = $this->cstream->appointmentid;
                    }
                    $mform->setDefault('teacher', $appointmentid);

                }

                // Сохранение
                $mform->addElement('submit', 'replace_lesson', $this->dof->get_string('postpone','journal'));

                // Извлечение статуса
                $status = $this->event->status;
                if ( $status == 'plan' )
                {// добавим кнопку отложения урока на неопределенный срок
                    $mform->addElement('submit', 'postpone_lesson', $this->dof->get_string('postpone_indefinitely','journal'));
                }

                $mform->setExpanded('transfer_lesson', false);
            }
        }
    }

    /**
     * Секция отмены занятия
     *
     * @return void
     */
    protected function show_cancel()
    {
        $mform = $this->_form;

        if ( $this->lesson->editform_allowed('cancel', null, $this->departmentid) )
        {
            // Заголовок
            $mform->addElement('header', 'cancelname', $this->dof->get_string('lesson_cancel_title','journal'));

            // Добавление полей
            $mform->addElement('checkbox', 'yes_cancel',null, $this->dof->get_string('сonfirmation_cancel_lesson','journal'));
            $mform->setDefault('yes_cancel', 0);

            // Кнопка "отменить"
            $mform->addElement('submit', 'lesson_cancel', $this->dof->get_string('lesson_cancel','journal'));
            $mform->disabledIf('lesson_cancel', 'yes_cancel');
        }
    }

    /**
     * Отметка о том, что занятие состоялось
     *
     * @return void
     */
    protected function show_completion()
    {
        $mform = $this->_form;

        $eventid = $this->event->id;
        if ( $this->lesson->editform_allowed('completion', null, $this->departmentid) )
        {
            // Заголовок
            $mform->addElement('header', 'completename', $this->dof->get_string('lesson_complete_title','journal'));

            // Добавление полей
            $mform->addElement('checkbox', 'yes_complete',null, $this->dof->get_string('сonfirmation_complete_lesson','journal'));
            $mform->setDefault('yes_complete', 0);

            // Кнопка "отменить"
            $mform->addElement('submit', 'lesson_complete', $this->dof->get_string('lesson_complete','journal'));
            $mform->disabledIf('lesson_complete', 'yes_complete');
        }
    }

    /**
     * Возвращает массив персон
     *
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        if ( is_int_string($pitemid) )
        {// если передан id предмета, выведем только учителей предмета
            $teachers = $this->dof->storage('teachers')->get_records(array
                    ('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( $teachers AND isset($teachers) )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($persons, true, 'appointmentid', array('sortname','enumber'));
            asort($rez);
        }

        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        return $rez;
    }

    /**
     * Извлечь из всех таблиц все необходимые объекты для построения запроса
     * @todo разобраться с сообщениями об ошибках - внести их в языковой файл и протестировать вызовы
     *
     * @return null
     * @param int $planid - id контрольной точки в таблице plans (или 0 если такой точки нет)
     * @param int $csid - id учебного потока в таблице cstreams
     * @param int $eventid - id учебного события в таблице schevents
     */
    protected function setup_local_variables($planid, $csid, $eventid)
    {
        if ( ! $this->cstream = $this->dof->storage('cstreams')->get($csid) )
        {// поток обязательно должен быть существующим
            $this->dof->print_error('cstream_not_found');
        }
        if ( $planid )
        {// если контрольная точка редактируется - то возьмем привязку из нее
            if ( ! $this->plan = $this->dof->storage('plans')->get($planid) )
            {// мы пытаемся редактировать элемент планирования, которого нет в базе - это ошибка
                $this->dof->print_error('plan_not_found');
            }
            //  свзязь и тип связи контрольной точки мы возьмем из базы в этом случае
            $this->linkid   = $this->plan->linkid;
            $this->linktype = $this->plan->linktype;
        }else
        {// если контрольная точка создается - то возьмем информацию о привязке из переданных параметров
            $this->linkid = $csid;
            // в форме создания урока через журнал - мы можем создавать
            // или редактировать только события учебного потока (cstream)
            $this->linktype = 'cstreams';
            // если событие не создано - создадим объект-заглушку для избежания notice-сообщений
            $this->plan = new stdClass();
            $this->plan->id             = 0;
            $this->plan->homeworkhours  = 0;
            $this->plan->plansectionsid = 0;
            $this->plan->name           = '';
            $this->plan->type           = 'facetime';
            $this->plan->homework       = '';
            $this->plan->note           = '';
        }

        if ( $eventid )
        {// мы редактируем существующее событие
            if ( ! $this->event = $this->dof->storage('schevents')->get($eventid) )
            {// переданное событие не существует
                $this->dof->print_error('event_not_found');
            }
        }else
        {// мы создаем новое событие - поставим заглушку внутрь переменной чтобы не было notice
            $event = new stdClass();
            $event->id = 0;
            $this->event = $event;
        }
        $this->lesson = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lesson(
            $this->cstream->id,
            $this->event->id,
            $this->plan->id
        );
        // получаем дату начала периода или потока (для которого редактируется журнал)
        $this->begindate = $this->cstream->begindate;
    }

    /** Установить все служебные hidden-параметры. Вынесено в отдельную функцию
     * для более удобного чтения кода
     *
     * @return null
     */
    protected function setup_hidden_fields()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // id контрольной точки
        $mform->addElement('hidden','planid', $this->plan->id);
        $mform->setType('planid', PARAM_INT);
        // id события
        $mform->addElement('hidden','eventid', $this->event->id);
        $mform->setType('eventid', PARAM_INT);
        // id потока
        $mform->addElement('hidden','csid', $this->cstream->id);
        $mform->setType('csid', PARAM_INT);

        //  ключ сессии
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // количество часов на домашнюю работу (для будущего пересчета)
        $mform->addElement('hidden','homeworkhours', $this->plan->homeworkhours);
        $mform->setType('homeworkhours', PARAM_INT);

        // объект привязки - 2 hidden-поля
        // тип связи
        $mform->addElement('hidden','linktype', $this->linktype);
        $mform->setType('linktype', PARAM_ALPHANUM);
        // id связи
        $mform->addElement('hidden','linkid', $this->linkid);
        $mform->setType('linkid', PARAM_INT);
        // дата начала периода или потока (если есть)
        $mform->addElement('hidden','begindate', $this->begindate);
        $mform->setType('begindate', PARAM_INT);

        // созданные из журнала уроки всегда отображаются по факту, поэтому directmap всегда будет в положении 1
        $mform->addElement('hidden', 'directmap', 1);
        $mform->setType('directmap', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
    }

    /** Отобразить форму создания тематического планирования или форму редактирования
     * тематического планирования
     * @todo если контрольную точку нельзя редактировать - то вывести сообщение о том, почему это нельзя
     * сделать и ссылка на редактирование контрольной точки в тематическом планировании
     * если у пользователя есть соответствующие права
     *
     * @return
     */
    protected function show_plan()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        if ( $this->plan->id )
        {// Контрольная точка редактируется - покажем форму редактирования
            // создаем заголовок формы
            $mform->addElement('header','formtitle_plan', $this->get_form_title());
            $mform->setExpanded('formtitle_plan', true);
            if ( $this->linktype != 'cstreams' )
            {// можем редактировать только контрольные точки относящиеся к потоку
                // поэтому мы отключаем форму редактирования для контрольных точек
                // не относящихся к потоку (cstream)

                // хак с display:none использован для того, чтобы выключить
                // форму редактирования контрольной точки.
                // По непонятным причинам в quickform правило disabledif
                // нельзя использовать для hidden-элементов
                $mform->addElement('radio', 'plan_disabled', '', '', 'true',
                    array('disabled' => 'disabled', 'style' => 'display:none;'));
                $this->disable_standart_plan_form('plan_disabled', 'true');
            }
            $this->show_plan_edit();

            if ( ! $this->planaccess )
            {
                // Зафризим форму редактирования КТ
                $mform->freeze([
                    'mdlcategoryid',
                    'mdlcourseid',
                    'mdlgradeitemid',
                    'gradessynctype',
                    'gradespriority',
                    'gradesoverride',
                    'gradescompulsion',
                    'workingoffautomaticgradechanges',
                    'workingoffautomaticlessonover',
                    'plansectionsid',
                    'parentid1',
                    'parentid2',
                    'parentid3',
                    'estimated',
                    'scale_flag',
                    'scale',
                    'mingrade',
                    'modulegradesconversation',
                    'name',
                    'type',
                    'homework',
                    'homeworkhoursgroup',
                    'note'
                ]);
            }
        }else
        {// Контрольную точку надо создать - покажем форму создания
            if ( $this->planaccess )
            {
                // создаем заголовок формы
                $mform->addElement('header','formtitle_plan', $this->get_form_title());
                $mform->setExpanded('formtitle_plan', true);
                $this->show_plan_create();
            }
        }
    }

    /** Показать фрагмент формы, который отвечает за редактирование контрольной точки
     *
     * @return
     */
    protected function show_plan_edit()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // выводим форму редактирования контрольной точки
        $this->get_standart_plan_edit_form();
    }

    /** Показать фрагмент формы, который отвечает за создание контрольной точки
     *
     * @return
     */
    protected function show_plan_create()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // даем пользователю выбрать варианты создания точки тематического планирования:

        foreach (['none', 'select', 'create'] as $actionname)
        {
            // 1) не создавать точку вообще (только создать событие)
            // 2) выбрать тему из списка контрольных точек (для того чтобы привязать точку к событию)
            // 3) создать контрольную точку самостоятельно (стандартная форма)
            $element = $mform->createElement(
                    'radio',
                    'plan_creation_type',
                    '',
                    '',
                    $actionname,
                    ['style' => 'display: none;']);
            $attrs = $element->getAttributes();
            $class = ! empty($attrs['class']) ? $attrs['class'] : '';
            $attrs['class'] = $class . ' ' . 'hide';
            $element->setAttributes($attrs);
            $mform->addElement($element);
        }
        $mform->setType('plan_creation_type', PARAM_ALPHA);

        // отключаем стандартную форму создания контрольной точки
        $this->disable_standart_plan_form('plan_creation_type', 'none');

        // устанавливаем правила
        // отключаем стандартную форму создания контрольной точки
        $this->disable_standart_plan_form('plan_creation_type', 'select');

        // отображение переключателя
        $switch = dof_html_writer::checkbox('switch-plan', '', true, '', ['id' => 'switch-plan']);
        $switch .= dof_html_writer::div(
                $this->dof->get_string('switch_plan_create__choose_theme', 'journal'),
                'switch-plan-wrapper__button switch-plan-wrapper__button_left');
        $switch .= dof_html_writer::label('', 'switch-plan');
        $switch .= dof_html_writer::div(
                $this->dof->get_string('switch_plan_create__create_theme', 'journal'),
                'switch-plan-wrapper__button switch-plan-wrapper__button_right switch-plan-wrapper__button_active');
        $switchwrapper = dof_html_writer::div($switch, 'switch-plan-wrapper');
        $mform->addElement('html', $switchwrapper);

        // список тем
        // получим список тем этого потока и добавляем выпадающее меню с ними
        $mform->addElement(
                'select',
                'existing_point',
                $this->dof->get_string('select_existing_point_theme_name', $this->im_code()) .':',
                $this->get_list_point($this->plan->id, $this->linktype, $this->linkid,1),
                ' style="max-width:400px;width:100%;" ');

        // устанавливаем правила
        // отключаем поле "список тем"
        $mform->disabledIf('existing_point', 'plan_creation_type', 'eq', 'create');
        // отключаем поле "список тем"
        $mform->disabledIf('existing_point', 'plan_creation_type', 'eq', 'none');

        // Занятие создается, поставим указатель на "Не создавать новую тему"
        $mform->setDefault('plan_creation_type', 'none');

        // подключаем стандартную форму создания контрольной точки
        $this->get_standart_plan_edit_form();
    }

    /** Показать стандартную часть формы создания/редактирования тематического планирования
     *
     * @return
     */
    protected function get_standart_plan_edit_form()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // получаем список всех возможных тематических разделов для этой контрольной точки
        $plansections = $this->dof_get_select_values(
                $this->get_plan_sections_list($this->linktype,
                        $this->linkid),
                array(0 => '--- '.$this->dof->modlib('ig')->igs('absent').' ---'));
        // тематический раздел
        $mform->addElement('select', 'plansectionsid', $this->dof->get_string('plansection',$this->im_code()).':',
                $plansections, ' style="max-width:400px;width:100%;" ');
        $mform->setType('plansectionsid', PARAM_INT);
        $mform->setDefault('plansectionsid', $this->plan->plansectionsid);

        // получаем список возможных родительских тем для тематиеского планирования
        $themes = $this->get_list_point($this->plan->id, $this->linktype, $this->linkid, null, true);
        // родительская тема 1
        $mform->addElement('select', 'parentid1', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;1:',
                $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid1', PARAM_INT);
        // родительская тема 2
        $mform->addElement('select', 'parentid2', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;2:',
                $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid2', PARAM_INT);
        // родительская тема 3
        $mform->addElement('select', 'parentid3', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;3:',
                $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid3', PARAM_INT);

        // Оцениваемый элемент
        $mform->addElement('advcheckbox', 'estimated', $this->dof->get_string('estimated', 'journal'));
        if ( ! empty($this->plan->id) )
        {
            $mform->setDefault('estimated', (bool)$this->plan->estimated);
        } else
        {
            $mform->setDefault(
                    'estimated',
                    (bool)$this->dof->storage('config')->get_config_value('estimated', 'storage', 'plans', $this->departmentid)
                    );
        }
        $this->add_help('estimated', 'estimated', 'journal');

        // обязательность оценки
        $mform->addElement(
                'select',
                'gradescompulsion',
                $this->dof->get_string('gradescompulsion', 'journal').':',
                $this->dof->modlib('journal')->get_manager('lessonprocess')->get_available_gradescompulsion());
        $this->add_help('gradescompulsion', 'gradescompulsion', 'journal');
        if ( ! empty($this->plan->id) )
        {
            $mform->setDefault('gradescompulsion', $this->plan->gradescompulsion);
        } else
        {
            $mform->setDefault('gradescompulsion', $this->dof->storage('config')->get_config_value('gradescompulsion', 'storage', 'plans', $this->cstream->departmentid));
        }

        // флаг переопределения настроек оценивания
        $mform->addElement('checkbox', 'scale_flag', $this->dof->get_string('use_discipline_scale', 'journal'));

        // шкала оценок
        $mform->addElement('text', 'scale', $this->dof->get_string('scale','journal'));
        $mform->setType('scale', PARAM_TEXT);
        $mform->disabledIf('scale', 'scale_flag', 'checked');

        // проходной балл
        $mform->addElement('text', 'mingrade', $this->dof->get_string('mingrade','journal'));
        $mform->setType('mingrade', PARAM_TEXT);
        $mform->disabledIf('mingrade', 'scale_flag', 'checked');

        // параметры конвертации оценки дисциплины
        $mform->addElement('textarea', 'modulegradesconversation', $this->dof->get_string('modulegradesconversation', 'journal').':', 'rows="5" cols="20"');
        $mform->setType('modulegradesconversation', PARAM_TEXT);
        $this->add_help('modulegradesconversation', 'modulegradesconversation', 'journal');
        $mform->disabledIf('modulegradesconversation', 'scale_flag', 'checked');

        if ( empty($this->plan->id) || empty($this->plan->scale) )
        {
            $mform->setDefault('scale_flag', 1);
        } else
        {
            $mform->setDefault('scale_flag', 0);
            $mform->setDefault('scale', $this->plan->scale);
            $mform->setDefault('mingrade', $this->plan->mingrade);
            $mform->setDefault('modulegradesconversation', $this->plan->modulegradesconversation);
        }

        // @todo переделать этот алгоритм тогда когда появится возможность задавать более 3-х родительских тем.
        // Сейчас он работает КРИВО и через ЗАДНИЦУ. Да простит меня Алан Тьюринг. Аминь.
        if ( $this->plan->id )
        {// устанавливаем по умолчанию значения в 3 родительские темы
            if ( $parentpoints = $this->dof->storage('planinh')->get_records(array('inhplanid'=>$this->plan->id)) )
            {
                $i = 1;
                foreach ( $parentpoints as $parentpoint )
                {
                    if ( $mform->elementExists('parentid'.$i) )
                    {
                        $mform->setDefault('parentid'.$i, $parentpoint->planid);
                    }
                    ++$i;
                }
            }
        }

        // название темы
        $mform->addElement('textarea', 'name',
                $this->dof->get_string('what_passed_on_lesson',$this->im_code()).':',
                ['style' => 'width:100%;max-width:400px;height:150px;']
                );
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $this->plan->name);
        $mform->addRule('name',$this->dof->modlib('ig')->igs('form_err_maxlength'), 'maxlength', 254,'client');
        $mform->addRule('name',$this->dof->modlib('ig')->igs('form_err_maxlength'), 'maxlength', 254,'server');

// TO DELETE
//         // взять название из оцениваемого элемента
//         $button = dof_html_writer::div(
//                 $this->dof->get_string('usemdlnameaslessontheme', 'journal'),
//                 'button btn test',
//                 ['onClick' => 'getMdlgradeitemName($(this))']
//                 );
//         $mform->addElement('html', $button);

        // тип темы
        $mform->addElement('select', 'type', $this->dof->get_string('typetheme',$this->im_code()).':',
                $this->dof->modlib('refbook')->get_lesson_types());
        $mform->setType('type', PARAM_ALPHANUM);
        $mform->setDefault('type', $this->plan->type);
        // Номер темы в плане
        // @todo включить опцию установки номера в планировании когда это станет возможным
        //$mform->addElement('text', 'number', $this->dof->modlib('ig')->igs('number').':', 'size="2"');
        //$mform->setType('number', PARAM_INT);
        //$mform->addRule('number',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');

        // домашнее задание
        // @todo отключить домашнее задание для итоговой аттестации
        // @todo сделать richtext-редактор для поля "домашнее задание"
        $mform->addElement('textarea', 'homework', $this->dof->get_string('homework',$this->im_code()).' :<br>'.
                $this->dof->get_string('homework_size',$this->im_code()),
                array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('homework', PARAM_TEXT);
        $mform->setDefault('homework', $this->plan->homework);
        // часов на домашнее задание (создаем группу элементов)
        $homeworkgroup = array();
        // настройки для select-элемента "часы"
        // @todo сейчас отключено, и домашнее задание указывается только в минутах
        //       если решим что так и должно быть - то удалить этот элемент совсем
        //$hoursoptions    = array();
        //$hoursoptions['availableunits']   = array(3600 => $this->dof->modlib('ig')->igs('hours'));
        //$homeworkgroup[] = &$mform->createElement('dof_duration', 'hours', null, $hoursoptions);
        // настройки для select-элемента "минуты"
        $minutesoptions  = array();
        $minutesoptions['availableunits'] = array(60 => $this->dof->modlib('ig')->igs('minutes'));
        $homeworkgroup[] = &$mform->createElement('dof_duration', 'minutes', null, $minutesoptions);
        // добавляем группу элементов "время на домашнее задание"
        $mform->addGroup($homeworkgroup, 'homeworkhoursgroup', $this->dof->get_string('homeworkhours', $this->im_code()).':', '&nbsp;');
        // поле "примечания"
        // @todo сделать ricktext-редактор для поля "примечания"
        $mform->addElement('textarea', 'note',  $this->dof->get_string('notes',$this->im_code()).':',
                array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('note', PARAM_TEXT);
        $mform->setDefault('note', $this->plan->note);

        // создаем поле, отвечающее за дату начала урока (абсолютную)
        if(! isset($this->event) OR ! $this->event->id)
        {
            $this->get_pinpoint_dateselector();
        }

        // заголовок настроек синхронизации
        $mform->addElement('header', 'mdlsyncgrades', $this->dof->get_string('mdlsyncgrades_header', 'journal'));

        // поле выбора грейд итема
        $coursecatid=null;
        $courseid=null;
        $gradeitemid=null;
        if ( ! empty($this->plan->mdlgradeitemid) )
        {
            $mform->setExpanded('mdlsyncgrades', true);
            $amagradeitem = $this->dof->modlib('ama')->grade_item($this->plan->mdlgradeitemid)->get();
            if (!empty($amagradeitem->courseid))
            {
                $courseid = $amagradeitem->courseid;
                $course = $this->dof->modlib('ama')->course($courseid)->get();
                $this->courses[$courseid] = $course->fullname;
                if (!empty($course->category))
                {
                    $coursecatid = $course->category;
                }
            }
            if (!empty($amagradeitem->id))
            {
                $gradeitemid = $amagradeitem->id;
            }
        } elseif ( ! empty($this->cstream->mdlcourse) )
        {
            $course = $this->dof->modlib('ama')->course($this->cstream->mdlcourse)->get();
            $courseid = $course->id;
            $this->courses[$courseid] = $course->fullname;
            $coursecatid = $course->category;
        } else
        {
            $pitem = $this->dof->storage('programmitems')->get_record(['id' => $this->cstream->programmitemid]);
            if ( ! empty($pitem->mdlcourse) )
            {
                $course = $this->dof->modlib('ama')->course($pitem->mdlcourse)->get();
                $courseid = $course->id;
                $this->courses[$courseid] = $course->fullname;
                $coursecatid = $course->category;
            }
        }
        $courseandcatselectattrs = ['class' => 'flexible_select'];

        $mform->addElement('hidden', 'gradeitemid', '', ['id' => 'lesson_edit_gradeitem_id']);
        $mform->setType('gradeitemid', PARAM_INT);
        $mform->disabledIf('gradeitemid', 'plan_creation_type', 'eq', 'none');

        $result = [];
        if ( ! empty($courseid) )
        {
            $result[0] = $this->dof->get_string('gradeitem_choose_category', 'journal');
            $coursecats = $this->dof->modlib('ama')->category(false)->search_by_name();
            if ( ! empty($coursecats) )
            {
                foreach ( $coursecats as $coursecat )
                {
                    $result[$coursecat->id] = $coursecat->name;
                }
            }
        } else
        {
            $result[0] = $this->dof->get_string('mdlcourseid_empty', 'journal');
        }
        $mform->addElement('select', 'mdlcategoryid', $this->dof->get_string('gradeitem_category', 'journal') . ':', $result, $courseandcatselectattrs);
        $mform->setDefault('mdlcategoryid', $coursecatid);

        // используем новое API деканата
        $ajaxoptions = [
            // предварительные опции селекта
            'newapi' => [
                'options' => array_key_exists($courseid, $this->courses) ? [
                    $courseid => dof_html_writer::link(
                            $this->dof->modlib('ama')->course($courseid)->get_course_view_url($courseid),
                            $this->courses[$courseid], [
                                'target' => '_blank'
                            ])
                ] : [
                    '0' => $this->dof->get_string('mdlcourseid_empty', 'journal')
                ],
                'methodname' => 'im_journal_get_courses',
                'on' => [
                    // название поля при AJAX запросе
                    'varname' => 'coursecatid',
                    // селектор поиска элемента
                    'selector' => '#id_mdlcategoryid',
                    // данные для языковой строки дефолтного значения (например --- Выберите курс ---)
                    'choosestr' => [
                        'ptype' => 'im',
                        'pcode' => 'journal',
                        'key' => 'gradeitem_choose_course'
                    ]
                ],
                // статические переменные
                'staticvars' => [
                ]
            ]
        ];

        // добавление элемента
        $mform->addElement('dof_ajaxselect', 'mdlcourseid', $this->dof->get_string('gradeitem_course', 'journal') . ':', $courseandcatselectattrs, $ajaxoptions);
        $mform->setDefault('mdlcourseid', $courseid);

        // получение оцениваемых элементов ЭД
        $ajaxoptions = [
            'newapi' => [
                'options' => ! empty($gradeitemid)
                ? [$gradeitemid => $this->courses[$courseid]]
                : ['0' => $this->dof->get_string('mdlcourseid_empty', 'journal')],
                'methodname' => 'im_journal_get_gradeitems',
                'on' => [
                    // название поля при AJAX запросе
                    'varname' => 'courseid',
                    // селектор поиска элемента
                    'selector' => '#id_mdlcourseid',
                    // данные для языковой строки дефолтного значения (например --- Выберите курс ---)
                    'choosestr' => [
                        'ptype' => 'im',
                        'pcode' => 'journal',
                        'key' => 'gradeitem_choose_name'
                    ]
                ],
                // статические переменные
                'staticvars' => [
                ]
            ]
        ];
        // добавление элемента
        $mform->addElement('dof_ajaxselect', 'mdlgradeitemid', $this->dof->get_string('gradeitem', 'journal') . ':', ['class' => 'flexible_select'], $ajaxoptions);
        $mform->setDefault('mdlgradeitemid', $gradeitemid);

        if ( empty($coursecatid) )
        {
            $mform->setDefault('mdlcategoryid', 0);
        }
        if ( empty($courseid) )
        {
            $mform->setDefault('mdlcourseid', 0);
            $mform->setDefault('mdlgradeitemid', 0);

            $mform->freeze(['mdlgradeitemid']);
        }

        // есть привязка учебного процесса/дисциплины с курсом Moodle
        $mform->freeze(['mdlcategoryid', 'mdlcourseid']);

        // синхронизация оценок
        $mform->addElement(
                'select',
                'gradessynctype',
                $this->dof->get_string('gradessynctype', 'journal'),
                $this->dof->modlib('journal')->get_manager('scale')->get_grades_synctypes()
                );
        $mform->disabledIf('gradessynctype', 'mdlgradeitemid', 'eq', 0);
        $this->add_help('gradessynctype', 'gradessynctype', 'journal');
        if ( property_exists($this->plan, 'gradessynctype') && ! is_null($this->plan->gradessynctype) )
        {
            $mform->setDefault('gradessynctype', $this->plan->gradessynctype);
        } else
        {
            $mform->setDefault('gradessynctype', 2);
        }

        // приоритет оценок
        $mform->addElement(
                'select',
                'gradespriority',
                $this->dof->get_string('gradespriority', 'journal'),
                $this->dof->modlib('journal')->get_manager('scale')->get_grades_priority()
                );
        $mform->disabledIf('gradespriority', 'mdlgradeitemid', 'eq', 0);
        $mform->disabledIf('gradespriority', 'gradessynctype', 'eq', 0);
        $this->add_help('gradespriority', 'gradespriority', 'journal');
        $gradespriority =  $this->dof->storage('config')->get_config_value('gradespriority', 'storage', 'plans', $this->departmentid);
        if ( property_exists($this->plan, 'gradespriority') && ! is_null($this->plan->gradespriority) )
        {
            $mform->setDefault('gradespriority', $this->plan->gradespriority);
        } else
        {
            $mform->setDefault('gradespriority', $gradespriority);
        }

        // перезаписывать оценки dof => moodle
        $mform->addElement(
                'selectyesno',
                'gradesoverride',
                $this->dof->get_string('gradesoverride', 'journal')
                );
        $mform->disabledIf('gradesoverride', 'mdlgradeitemid', 'eq', 0);
        $this->add_help('gradesoverride', 'gradesoverride', 'journal');
        if ( property_exists($this->plan, 'gradesoverride') && ! is_null($this->plan->gradesoverride) )
        {
            $mform->setDefault('gradesoverride', $this->plan->gradesoverride);
        } else
        {
            $mform->setDefault('gradesoverride', 0);
        }
        $mform->disabledIf('gradesoverride', 'gradespriority', 'eq', $gradespriority);
        $mform->disabledIf('gradesoverride', 'gradessynctype', 'eq', 0);

        //  выставлять флаг "отработка" при изменении оценки
        $mform->addElement(
                'advcheckbox',
                'workingoffautomaticgradechanges',
                $this->dof->get_string('workingoffautomaticgradechanges', 'journal')
                );
        $mform->disabledIf('workingoffautomaticgradechanges', 'mdlgradeitemid', 'eq', 0);
        $mform->disabledIf('workingoffautomaticgradechanges', 'gradessynctype', 'eq', 0);

        //  выставлять флаг "отработка" если оценка выставляется не во-время занятия (или после дедлайна, если есть только контрольная точка)
        $mform->addElement(
                'advcheckbox',
                'workingoffautomaticlessonover',
                $this->dof->get_string('workingoffautomaticlessonover', 'journal')
                );
        $mform->disabledIf('workingoffautomaticlessonover', 'mdlgradeitemid', 'eq', 0);
        $mform->disabledIf('workingoffautomaticlessonover', 'gradessynctype', 'eq', 0);
        $this->add_help('workingoffautomaticlessonover', 'workingoffautomaticlessonover', 'journal');

        // дефолтные значения
        if ( property_exists($this->plan, 'workingoffautomatic') && ! is_null($this->plan->workingoffautomatic) )
        {
            $mform->setDefault('workingoffautomaticgradechanges', $this->dof->storage('plans')->is_active_workingoff_grade_changes($this->plan));
            $mform->setDefault('workingoffautomaticlessonover', $this->dof->storage('plans')->is_active_workingoff_lesson_over($this->plan));
        } else
        {
            $mform->setDefault('workingoffautomaticgradechanges', 0);
            $mform->setDefault('workingoffautomaticlessonover', 0);
        }
    }

    /** Отобразить информацию о событии или форму редактирования события
     *
     * @return
     */
    protected function show_event()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        if ( $this->event->id )
        {// событие есть, отобразим информацию о событии
            // создаем заголовок формы
            $mform->addElement('header','formtitle_event', $this->dof->get_string('event', $this->im_code()));
            $mform->setExpanded('formtitle_event', true);

            // отображаем информацию
            $this->show_event_info();

            if ( ! $this->scheventaccess )
            {
                // Зафризим форму события
                $mform->freeze([
                    'event_lesson',
                    'event_place',
                    'event_date',
                    'event_ahours',
                    'event_duration',
                    'event_salfactor',
                    'event_url'
                ]);
            }
        } else
        {
            if ( $this->lesson->createform_allowed($this->cstream->id,'event', null, $this->departmentid) )
            {// есть право создавать событие
                // создаем заголовок формы
                $mform->addElement('header','formtitle_event', $this->dof->get_string('event', $this->im_code()));
                $mform->setExpanded('formtitle_event', true);
                // показываем форму создания
                $this->show_event_create();
            }
        }
    }

    /**
     * Отобразить информацию о событии
     *
     * @return
     */
    protected function show_event_info()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Получение списка типов событий
        $lessonforms = $this->dof->modlib('refbook')->get_event_form();

        // Указание типа события
        $mform->addElement(
                'static',
                'event_lesson',
                $this->dof->get_string('form_lesson', $this->im_code()).':',
                $lessonforms[$this->event->form]
                );

        // Указание кабинета, в котором произойдет событие
        $place = '';
        if ( ! empty($this->event->place) )
        {
            $place = $this->event->place;
        }
        // Кабинет
        if ( $this->dof->storage('schevents')->is_access('edit:place',$this->event->id) )
        {
            $mform->addElement('text', 'event_place', $this->dof->get_string('lesson_place',$this->im_code()).':', ['style' => "width:100px;"]);
            $mform->setType('event_place', PARAM_TEXT);
            $mform->setDefault('event_place', $place);
        } else
        {
            if ( empty($place) )
            {
                $place = $this->dof->get_string('lesson_place_empty', 'journal');
            }
            $mform->addElement('hidden','event_place', $this->event->place);
            $mform->setType('event_place', PARAM_INT);
            $mform->addElement(
                    'static',
                    'event_place_info',
                    $this->dof->get_string('lesson_place',$this->im_code()).':',
                    $place
                    );
        }

        // Указание даты начала события
        $mform->addElement(
                'static',
                'event_date',
                $this->dof->get_string('event_date',$this->im_code()).':',
                dof_userdate($this->event->date, '%Y-%m-%d %H:%M', $this->usertimezone, false)
                );

        // Указание академических часов по событию
        if ( $this->dof->storage('schevents')->is_access('edit:ahours',$this->event->id) )
        {// Пользователь может изменить количество академических часов по событию
            $mform->addElement(
                    'text',
                    'event_ahours',
                    $this->dof->get_string('ahours', $this->im_code()).':',
                    'size="4"'
                    );
            $mform->setType('event_ahours', PARAM_INT);
        }else
        {// Отображение академических часов
            $mform->addElement('hidden','event_ahours', $this->event->ahours);
            $mform->setType('event_ahours', PARAM_INT);
            $mform->addElement(
                    'static',
                    'event_ahours_info',
                    $this->dof->get_string('ahours',$this->im_code()).':',
                    $this->event->ahours
                    );
        }
        $mform->setDefault('event_ahours', $this->event->ahours);

        // устанавливаем по умолчанию поле "длительность"
        $mform->addElement('dof_duration', 'event_duration', $this->dof->get_string('duration', $this->im_code()).':', ['availableunits' => [60 => $this->dof->modlib('ig')->igs('minutes')]]);
        $mform->setType('event_duration', PARAM_INT);
        $mform->setDefault('event_duration', $this->event->duration);
        if ( ! $this->dof->storage('schevents')->is_access('edit:duration', $this->event->id) )
        {// Пользователь может изменить длительность по событию
            $mform->freeze('event_duration');
        }

        // Указание предварительного зарплатного коэфициента события
        $mform->addElement(
                'static',
                'event_salfactor',
                $this->dof->get_string('salfactor',$this->im_code()).':',
                $this->dof->storage('cstreams')->calculation_salfactor($this->event->cstreamid)
                );

        // Указание фактически проведенных академических часов
        $mform->addElement(
                'static',
                'event_rhours',
                $this->dof->get_string('rhours',$this->im_code()).':',
                $this->event->rhours
                );

        if ( ! empty($this->event->replaceid) )
        {// Событие является заменой

            // Получение замененного события
            $replace = $this->dof->storage('schevents')->get($this->event->replaceid);

            // Указание данных о замененном событии
            $url = $this->dof->url_im(
                    'journal',
                    '/group_journal/forms/lessonedit.php',
                    [
                        'csid' => $replace->cstreamid,
                        'planid' => $replace->planid,
                        'eventid' => $replace->id,
                        'departmentid' => $this->departmentid,
                        'page_layout' => 'popup'
                    ]
                    );
            $link = dof_html_writer::link(
                    $url,
                    dof_userdate($replace->date, '%Y-%m-%d %H:%M', $this->usertimezone, false)
                    );
            $mform->addElement(
                    'static',
                    'event_replace',
                    $this->dof->get_string('replace_from', $this->im_code()).':',
                    $link
                    );
        }

        // Поиск замен по текущему событию
        if ( $replaces = $this->dof->storage('schevents')->get_records(array('replaceid'=>$this->event->id),'date DESC') )
        {// Замены найдены

            // Получение последней замены
            $replace = current($replaces);

            // Указание данных о замене
            $url = $this->dof->url_im(
                    'journal',
                    '/group_journal/forms/lessonedit.php',
                    [
                        'csid' => $replace->cstreamid,
                        'planid' => $replace->planid,
                        'eventid' => $replace->id,
                        'departmentid' => $this->departmentid,
                        'page_layout' => 'popup'
                    ]
                    );
            $link = dof_html_writer::link(
                    $url,
                    dof_userdate($replace->date, '%Y-%m-%d %H:%M', $this->usertimezone, false)
                    );
            $mform->addElement(
                    'static',
                    'event_replaced',
                    $this->dof->get_string('replaced_on', $this->im_code()).':',
                    $link
                    );
        }

        // Ссылка на занятие (URL)
        if ( $this->dof->storage('schevents')->is_access('edit:url',$this->event->id) )
        {
            $mform->addElement('text','event_url', $this->dof->get_string('event_url', $this->im_code()) . ':');
            $mform->setType('event_url', PARAM_URL);
        } else
        {
            $mform->addElement(
                    'static',
                    'event_url_info',
                    $this->dof->get_string('event_url', $this->im_code()) . ':',
                    $this->event->url
                    );
        }
        $mform->setDefault('event_url', $this->event->url);
    }

    /** Показать фрагмент формы, который отвечает за создание события
     *
     * @return
     */
    protected function show_event_create()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // галочка "создать событие" - делает активными поля созданя события
        $mform->addElement('checkbox', 'create_event', '');
        // отключаем поля создания события если галочка не поставлена
        $mform->disabledIf('event_form', 'create_event', 'notchecked');
        $mform->disabledIf('event_date', 'create_event', 'notchecked');
        $mform->disabledIf('event_ahours', 'create_event', 'notchecked');
        $mform->disabledIf('event_url', 'create_event', 'notchecked');
        $mform->disabledIf('event_place', 'create_event', 'notchecked');
        $mform->disabledIf('event_duration', 'create_event', 'notchecked');

        // тип события
        $mform->addElement('select', 'event_form', $this->dof->get_string('form_lesson',$this->im_code()).':',
                $this->dof->modlib('refbook')->get_event_form());

        // Кабинет
        $mform->addElement('text', 'event_place', $this->dof->get_string('lesson_place',$this->im_code()).':', ['style' => "width:100px;"]);
        $mform->setType('event_place', PARAM_TEXT);

        // получаем дату начала и окончания по умолчанию из текущего периода
        $options = $this->get_dateselector_defaults();
        if ( $this->plan->id )
        {// есть контрольная точка - то берем дату из нее
            $date = $this->plan->reldate + $this->begindate;
        }else
        {// если нет контрольной точки - то подставляем текущую дату
            $date = time();
        }
        // планируемая дата события (по умолчанию совпадает с датой контрольной точки)
        $mform->addElement('date_time_selector', 'event_date',
                $this->dof->get_string('event_date', $this->im_code()).':', $options);
        $mform->setDefault('event_date', $date);
        $mform->addElement('text', 'event_ahours', $this->dof->get_string('ahours', $this->im_code()).':', 'size="4"');
        $mform->setType('event_ahours', PARAM_INT);
        $mform->setDefault('event_ahours', 1);
        // устанавливаем тип события - пол умолчанию  "normal"
        $mform->addElement('hidden','event_type', 'normal');
        $mform->setType('event_type', PARAM_ALPHANUM);

        // берем id учителя для потока из события
        $mform->addElement('hidden','event_teacherid', $this->cstream->teacherid);
        $mform->setType('event_teacherid', PARAM_INT);

        // устанавливаем по умолчанию поле "длительность"
        $mform->addElement('dof_duration', 'event_duration', $this->dof->get_string('duration', $this->im_code()).':', ['availableunits' => [60 => $this->dof->modlib('ig')->igs('minutes')]]);
        $mform->setType('event_duration', PARAM_INT);
        // Получение длительности урока по умолчанию из конфига
        $duration =  $this->dof->storage('config')->get_config_value('duration', 'storage', 'schevents', $this->departmentid);
        if ( ! empty($duration) )
        {
            $mform->setDefault('event_duration', $duration);
        } else
        {
            $mform->setDefault('event_duration', 2700);
        }

        // берем id назначения на должность из потока
        $mform->addElement('hidden','event_appointmentid', $this->cstream->appointmentid);
        $mform->setType('event_appointmentid', PARAM_INT);

        // Ссылка на занятие (URL)
        $mform->addElement('text','event_url', $this->dof->get_string('event_url', $this->im_code()) . ':');
        $mform->setType('event_url', PARAM_URL);
    }

    /** Отобразить список потоков, для которых будет создано событие/план
     * Используется только если перешли по ссылке "добавление события для нескольких учебных
     * процессов" из журнала. Идет проверка на право создания события
     *
     */
    protected function show_cstreams()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('cstreams', $this->im_code()));

        foreach($this->cstreams as $cstream)
        {//есть ли право создать событие для потока
            if ( $this->dof->im($this->im_code())->is_access('create_schevent', $cstream->id) )
            {//есть - отображаем
                $mform->addElement('checkbox', 'cstreams['.$cstream->id.']', '', $cstream->name);
            }
        }
    }

    /**
     * Отображение секции результата
     *
     * @param bool $result
     * @param string $result_string
     *
     * @return void
     */
    protected function show_result($result = false, $ingorenotification = false)
    {
        if ( !$ingorenotification )
        {
            if ( $result )
            {
                $this->dof->messages->add($this->dof->get_string('edit_success','journal'), DOF_MESSAGE_SUCCESS, $this->loggercode);
            } else
            {
                $this->dof->messages->add($this->dof->get_string('edit_fail','journal'), DOF_MESSAGE_ERROR, $this->loggercode);
            }
        }

        if ( (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_ERROR)) &&
                (! $this->dof->messages->get_stack_messages($this->loggercode, DOF_MESSAGE_WARNING)) )
        {
            $this->dof->modlib('nvg')->add_js('im', 'journal', '/group_journal/js/closemodal.js', false);
        }
    }

    /** Отключить все элементы формы создания элемента тематического планирования
     * Используется для того чтобы задать правила disabledif для всей формы
     * @todo найти способ выключить поле "время на домашнее задание"
     *
     * @return null
     * @param string $element - название элемента от которого зависит, будет выключена форма создания
     *                          контрольной точки или нет
     * @param string $value - значение, при котором будет выключена форма создания контрольной точки
     */
    protected function disable_standart_plan_form($element, $value)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

            // создаем массив полей формы создания тематического планирования,
            // которые нужно отключить
        $fields = [
            'mdlcategoryid',
            'mdlcourseid',
            'mdlgradeitemid',
            'gradessynctype',
            'gradespriority',
            'gradesoverride',
            'gradescompulsion',
            'workingoffautomaticgradechanges',
            'workingoffautomaticlessonover',
            'plansectionsid',
            'parentid1',
            'parentid2',
            'parentid3',
            'name',
            'type',
            'homework',
            'homeworkhoursgroup',
            'note',
            'pinpoint_date',
            'estimated',
            'scale_flag',
            'scale',
            'mingrade',
            'modulegradesconversation'
        ];

        foreach ( $fields as $field )
        {// перебираем все поля формы и для каждого устанавливаем правило disabledif
            $mform->disabledIf($field, $element, 'eq', $value);
        }
    }

    /** Получить элемент dateselector для выбора даты внутри периода
     * (Для контрольных точек относящихся к cpassed и ages)
     * @return null
     */
    protected function get_pinpoint_dateselector()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // получаем дату начала и окончания по умолчанию из текущего периода
        $options = $this->get_dateselector_defaults();

        // определяем дату точки тематического планирования по умолчанию
        // показываем дату события, если есть
        if ( $this->event->id )
        {
            $default = $this->event->date;
        }elseif ( $this->plan->id )
        {// если события нет - то берем дату из контрольной точки
            $default = $this->plan->reldate + $this->begindate;
        }else
        {// если нет ни события ни контрольной точки - то подставляем текущую дату
            $default = time();
        }

        $mform->addElement('date_time_selector', 'pinpoint_date',
                $this->dof->get_string('pinpoint_date', $this->im_code()).':', $options);
        $mform->setDefault('pinpoint_date', $default);
        $mform->disabledIf('pinpoint_date', 'create_event', 'checked');
    }

    /** Получить дату начала и дату окончания потока в виде массива настроек
     * для quickform-элемента date_selector или date_time_selector
     *
     * @return array - массив настроек
     */
    protected function get_dateselector_defaults()
    {
        if ( $age = $this->get_current_age($this->linktype, $this->linkid) )
        {// если мы можем точно определить период в котором работаем
            $startyear = dof_userdate($age->begindate,'%Y');
            $stopyear  = dof_userdate($age->enddate,'%Y');
        }else
        {// в остальных случаях - ставим только текущий год
            $startyear = dof_userdate(time(),'%Y');
            $stopyear  = dof_userdate(time(),'%Y');
        }
        // объявляем массив для установки значений по умолчанию
        $options = array();
        // устанавливаем год, с которого начинать вывод списка
        $options['startyear'] = $startyear;
        // устанавливаем год, которым заканчивается список
        $options['stopyear']  = $stopyear;
        // убираем галочку, делающую возможным отключение этого поля
        $options['optional']  = false;

        return $options;
    }


    /** Определить, какой тип даты выбирать - относительная или абсолютная
     *
     * @param string $linktype - тип связи контрольной точки с объектом
     * @return bool
     *             true - использовать относительную дату (от начала программы или предмета)
     *             false - использовать абсолютную дату (для периода или подписки на предмет)
     */
    protected function is_relative_dataselector($linktype)
    {
        switch ( $linktype )
        {
            case 'ages'          : return false;
            case 'cstreams'      : return false;
            case 'programmitems' : return true;
            case 'programms'     : return true;
            case 'plan'          : return false;
            // по умолчанию возвращаем относительную дату
            default : return true;
        }
    }

    /** Получить список разделов тематического планирования
     *
     * @return array
     * @param string $linktype
     * @param int $linkid
     */
    protected function get_plan_sections_list($linktype, $linkid)
    {
        // получаем список разделов тематического планирования для выбранного
        // предмета, программы, потока или периода
        $sections = $this->dof->storage('plansections')->get_theme_plan($linktype, $linkid, array('active'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plansections', 'code'=>'use'));
        $sections = $this->dof_get_acl_filtered_list($sections, $permissions);

        return $sections;
    }

    /** Подстановка данных по умолчанию
     *
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // устанавливаем значение часов/минут на домашнее задание по умолчанию
        $hwhours = $mform->getElementValue('homeworkhours');
        if ( ! empty($hwhours) )
        {
            $mform->setDefault('homeworkhoursgroup', ['minutes' => $hwhours]);
        }
    }

    /**
     * Проверка даты
     * @param int $event_date - дата события
     * @param int $cstream_begindate - начало потока
     * @param int $cstream_enddate - конец потока
     * @return string - Сообщение об ошибке или null в случае, если всё правильно
     */
    protected function date_check($event_date)
    {
        if( $event_date < $this->cstream->begindate )
        {//дата события меньше начальной даты потока
            $cstreambegin = dof_userdate($this->cstream->begindate,'%Y-%m-%d');
            return $this->dof->get_string('error_earlier_event_date', $this->im_code(), $cstreambegin);
        }
        if( $event_date > $this->cstream->enddate )
        {//дата события больше конечной даты потока
            $cstreamend = dof_userdate($this->cstream->enddate,'%Y-%m-%d');
            return $this->dof->get_string('error_later_event_date', $this->im_code(), $cstreamend);
        }
        return null;
    }

    /**
     * Проверяет соответствие КТ потоку и, при несоответствии, сразу выводит ошибку пользователю,
     * останевив всю работу
     * @param int $data_linkid - id потока, переданое через форму
     * @param string $linktype - тип связи, взятый из таблицы (должен быть 'cstreams')
     * @param int $linkid - id потока, взятое из таблицы
     */
    protected function plan_check($data_linkid, $linktype, $linkid)
    {
        if( ($linktype != 'cstreams') OR ($linkid != $data_linkid) )
        {// КТ не соответствует КТ потока
            $this->dof->print_error('plan_not_correspond_cstream', '', $data_linkid, 'im', 'journal');
        }
    }

    /**
     * Проверяет данные контрольной точки
     * @param mixed array $data - данные
     * @return string array - список сообщений об ошибках
     */
    protected function plan_data_check($data)
    {
        $errors = array();
        // проверка назнания темы
        if ( ! $data['parentid1'] AND ! $data['parentid2'] AND ! $data['parentid3'] )
        {// не указана ни одна из родительских тем
            if ( ! trim($data['name']) && empty($data['mdlgradeitemid']) )
            {// не указано название темы, и не указана ни одна из родительских тем - это ошибка
                $errors['name'] = $this->dof->modlib('ig')->igs('form_err_required');
            }
        }
        if ( ($data['parentid1'] == $data['parentid2'] OR $data['parentid1'] == $data['parentid3'])
                AND $data['parentid1'] != '0' )
        {// проверка на совпадение родительских тем
            $errors['parentid1'] = $this->dof->get_string('field_has', $this->im_code());
        }elseif ( ($data['parentid2'] == $data['parentid3']) AND $data['parentid2'] != '0' )
        {
            $errors['parentid2'] = $this->dof->get_string('field_has', $this->im_code());
        }
        if( isset($data['pinpoint_date']) AND ! isset($data['create_event']) )
        {
            // проверка правильности даты проведения
            if ( $age = $this->get_current_age($data['linktype'], $data['linkid']) )
            {// мы можем точно определить период, в котором работаем
                if ( $data['pinpoint_date'] < $age->begindate )
                {// абсолютная дата начала меньше даты начала периода
                    $agebegin = dof_userdate($age->begindate,'%Y-%m-%d');
                    $errors['pinpoint_date'] =
                    $this->dof->get_string('err_too_small_absdate', $this->im_code(), $agebegin);
                }
                if ( $data['pinpoint_date'] > $age->enddate )
                {// абсолютная дата окончания больше даты окончания периода
                    $ageend = dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['pinpoint_date'] =
                    $this->dof->get_string('err_too_large_absdate', $this->im_code(), $ageend);
                }
            }
        }

        // проверка поля "домашнее задание"
        if ( mb_strlen(trim($data['homework']),'utf-8') > 700 )
        {// слишком длинное домашнее задание (512 символов используется потому что данные передаются
            // из формы в двухбайтовой кодировке)
            $errors['homework'] = $this->dof->get_string('err_too_long_homework','plans');
        }

        if ( $this->dof->im($this->im_code())->get_cfg('deny_homework_without_hours') )
        {// если в конфиге запрещено задание домашних заданий без указания часов - проверим,
            // указано ли время на выполнение домашнего задания
            if ( ! trim($data['homeworkhours']) OR ! floatval($data['homeworkhours']) )
            {// не указаны часы на домашнее задание
                $errors['homeworkhours'] = $this->dof->get_string('err_no_homework_hours','plans');
            }
        }

        if ( empty($data['scale_flag']) )
        {
            $errors = array_merge(
                    $errors,
                    $this->dof->modlib('journal')->get_manager('scale')->is_scale_valid($data['scale'])
                    );
            if ( ! empty($data['mingrade']) )
            {// если у нас есть минимальная оценка, то проверим, приенадлежит ли она шкале
                if ( ! $this->dof->modlib('journal')->get_manager('scale')->is_grade_valid($data['mingrade'], $data['scale']) )
                {
                    $errors['mingrade'] = $this->dof->get_string('invalid_mingrade','journal');
                }
            }
            $res = [];
            if ( ! empty($data['modulegradesconversation']) &&
                    ! $this->dof->modlib('journal')->get_manager('scale')->is_valid_grades_conversation_options($data['modulegradesconversation'], $data['scale'], $res) )
            {
                // неверные опции конвертации оценки
                $errors['modulegradesconversation'] = implode('<br>', $res);
            }
        }

        return $errors;
    }

    /**Проверка прав доступа
     *
     * @param $access - право доступа, которое нужно проверить
     * @param $objid - id потока, события или плана (по умолчанию равен null)
     * @return
     */
    protected function access_check($access, $objid = null)
    {
        if( !$this->dof->im('journal')->is_access($access, $objid) )
        {
            $this->dof->print_error('access_denied', '', null, 'im', 'journal');
        }
    }

    /** Проверка данных на стороне сервера
     *
     * @return array
     * @param array $data[optional] - массив с данными из формы
     * @param array $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $returnurl = $this->dof->url_im('journal','/group_journal/index.php?departmentid='
                .$this->departmentid.'&csid='.$this->cstream->id);
        if ( ! isset($data['linktype']) OR ! isset($data['linkid']) )
        {//не переданы обязательные данные
            $this->dof->print_error('error_data_null', '', null, 'im', 'journal');
        }
        if( $data['linktype'] != 'cstreams' AND $data['linktype'] != 'programmitems' )
        {//редактирование и создание КТ и события может быть связано только с потоком или предметом
            $this->dof->print_error('error_data_linktype', '', null, 'im', 'journal');
        }

        if( $this->cstream->id != $data['linkid'])
        {// id потока передано неверно
            $this->dof->print_error('cstream_not_found', '', $data['linkid'], 'im', 'journal');
        }

        $errors = [];

        if ( ! empty($data['lesson_cancel']) ||
                ! empty($data['replace_lesson']) ||
                ! empty($data['postpone_lesson']) )
        {
            // Дополнительные действия, нет необходимости в последующей валидации
            return $errors;
        }

        // Проверка, что при создание занятия создается хотя бы событие/КТ
        if ( empty($data['planid']) &&
                empty($data['eventid']) &&
                empty($data['create_event']) &&
                ($data['plan_creation_type'] == 'none') )
        {
            $errors['create_event'] = $this->dof->get_string('error_lessoneditform_empty', 'journal');
        }

        // определяем, какой тип проверки использовать
        if ( ! $this->event->id AND $this->can_create_event() AND isset($data['create_event']) AND $data['create_event'] )
        {// событие можно и нужно было создать - проверка создания события
            if ( ! $this->dof->storage('schevents')->is_access('create') AND
                    ! $this->dof->storage('schevents')->is_access('create/in_own_journal',$this->cstream->id) )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            if( ($data['event_form'] != 'distantly') AND ($data['event_form'] != 'internal') )
            {//форма урока неверно задана
                $errors['event_form'] = $this->dof->get_string('error_event_form', 'journal');
            }
            if( $error = $this->date_check($data['event_date']) )
            {
                $errors['event_date'] = $error;
            }
        }elseif( isset($data['eventid']) AND $data['eventid'] )
        {// событие уже существует
            // проверка правильности привязки события к потоку
            if( ! isset( $this->event->cstreamid ) OR $this->event->cstreamid != $data['linkid'])
            {//Событие не соответствует событию потока
                $this->dof->print_error('event_not_correspond_cstream', '', $data['linkid'], 'im', 'journal');
            }
        }

        if ( ! empty($data['mdlgradeitemid']) )
        {
            if ( ! empty($this->courses) )
            {
                // проверим, что выбранный грейд итем соответствует доступному курсу
                $amagradeitem = $this->dof->modlib('ama')->grade_item($data['mdlgradeitemid'])->get();
                if ( ! array_key_exists($amagradeitem->courseid, $this->courses) )
                {
                    $errors['mdlgradeitemid'] = $this->dof->get_string('invalid_gradetem','journal');
                }
            } else
            {
                $errors['mdlgradeitemid'] = $this->dof->get_string('invalid_gradetem','journal');
            }

            if ( ! array_key_exists('gradessynctype', $data) ||
                    ! array_key_exists($data['gradessynctype'], $this->dof->modlib('journal')->get_manager('scale')->get_grades_synctypes()) )
            {
                $errors['gradessynctype'] = $this->dof->get_string('invalid_gradessynctype','journal');
            }
            if ( ! array_key_exists('gradespriority', $data) ||
                    ! array_key_exists($data['gradespriority'], $this->dof->modlib('journal')->get_manager('scale')->get_grades_priority() ) )
            {
                $errors['gradespriority'] = $this->dof->get_string('invalid_gradespriority','journal');
            }
        }

        if ( ! isset($data['planid']) OR ! $data['planid'] )
        {// если КТ создается
            if ( ! $this->lesson->createform_allowed($this->cstream->id, 'plan') )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            if( isset($data['plan_creation_type']) )
            {
                if ( $data['plan_creation_type'] == 'create' )
                {// проверка создания новой контрольной точки
                    $errors = array_merge($errors, $this->plan_data_check($data));
                } elseif ( $data['plan_creation_type'] == 'select' )
                {// проверка выбранной из списка контрольной точки
                    if ( isset($data['eventid']) AND $data['eventid'] )
                    {
                        if ( ! $this->dof->im('journal')->is_access('give_theme_event',$data['eventid']) AND
                            ! $this->dof->im('journal')->is_access('give_theme_event/own_event',$data['eventid']) )
                        {
                            $this->dof->print_error('access_denied', '', null, 'im', 'journal');
                        }
                    }
                    if ( ! $plan = $this->dof->storage('plans')->get($data['existing_point']) )
                    {// выбранная точка планирования не существует
                        $errors['existing_point'] = $this->dof->get_string('err_selected_point_not_exists', $this->im_code());
                    } else
                    {// выбранная точка планирования существует
                        // проверка правильности привязки контрольной точки к потоку
                        $this->plan_check($data['linkid'], $plan->linktype, $plan->linkid);
                        if( ($plan->status != 'active') AND ($plan->status != 'draft') )
                        {
                            $this->dof->print_error('error_plan_status', '', null, 'im', 'journal');
                        }
                    }
                }
            }

        } else
        {// КТ редактируется

            if ( ! $this->dof->storage('plans')->is_access('edit',$data['planid']) AND
                    ! $this->dof->storage('plans')->is_access('edit/in_own_journal',$data['planid']) )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            // проверка правильности привязки контрольной точки к потоку
            $this->plan_check($data['linkid'], $this->plan->linktype, $this->plan->linkid);
            // проверка обновления данных контрольной точки
            $errors = array_merge($errors, $this->plan_data_check($data));

            if ( empty($data['scale_flag']) &&
                    array_key_exists('scale', $data) )
            {
                if ( $this->plan->scale != $data['scale'] &&
                        !$this->dof->modlib('journal')->get_manager('scale')->can_change_plan_scale($data['planid']) )
                {
                    // нельзя сменить шкалу, так как по нейуже выставлена оценка хотя бы одному слушателю
                    $errors['scale'] = $this->dof->get_string('cannot_change_plan_scale', 'journal');
                }
            }
        }

        if ( $this->event->id )
        {// событие редактируется
            $appoint = $this->dof->storage('appointments')->get($this->event->appointmentid );
            if ( ! empty($appoint) )
            {// Назначение на должность для урока есть
                if ( $appoint->status == 'patient' )
                {// учитель на больничном не может отмечать уроки
                    $this->dof->print_error('err_patient_teacher', $returnurl, null, 'im', 'journal');
                } else if ( $appoint->status == 'vacation' )
                {// учитель в отпуске не может отмечать уроки
                    $this->dof->print_error('err_vacation_teacher', $returnurl, null, 'im', 'journal');
                }
            }
        }

        if ( isset($this->event) && ! empty($this->event) && isset($data['replace_lesson']) )
        {
            $access = $this->dof->im('journal')->is_access_replace($this->event->id);

            // Проверка по времени
            $ageid = $this->dof->storage('cstreams')->get_field($this->event->cstreamid, 'ageid');
            $age = $this->dof->storage('ages')->get($ageid);

            if ( ( $data['date'] < $age->begindate || $data['date'] > $age->enddate ) && ! $this->dof->is_access('datamanage') )
            {// Даты начала и окончания события не должны вылезать за границы периода
                $errors['date'] = $this->dof->get_string('err_date', 'journal', date('Y/m/d', time()).'-'.date('Y/m/d', $age->enddate));
            }
            if ( ! $access->ignorolddate )
            {// игнорировать новую дату урока нельзя
                if ( $data['date'] < time() )
                {// переносить можно только на еще не наступившее время
                    $errors['date'] = $this->dof->get_string('err_date_postfactum','journal');
                }

                // @todo сделать проверку, если у ученика или учителя уже есть на это время уроки
            }
        }

        if ( ! empty($errors) )
        {
            // отобразим облачко о том, что форма не прошла валидацию и необходимо поправить поля
            $this->dof->messages->add($this->dof->get_string('error_lessoneditform_notvalid', 'journal'), DOF_MESSAGE_ERROR);
            foreach ($errors as $fieldname => $error)
            {
                $this->dof->messages->add($this->dof->get_string('field', 'journal', $this->_form->getElement($fieldname)->_label) . $error, DOF_MESSAGE_ERROR);
            }
        }

        return $errors;
    }

    /**
     * Отложить урок на неопределенный срок (Перевод в статус postponed)
     *
     * @param stdClass $formdata
     *
     * @return bool
     */
    protected function process_lesson_postpone(stdClass $formdata)
    {
        // Смена статуса
        return $this->dof->modlib('journal')
        ->get_manager('lessonprocess')
        ->changestatus_schevent($this->event->id, 'postponed');
    }

    /**
     * Перенос занятия
     *
     * @param stdClass $formdata
     *
     * @return bool
     */
    protected function process_lesson_replace(stdClass $formdata)
    {
        // Объект переноса
        $replace_data = new stdClass;
        if ( empty($formdata->date) )
        {
            $replace_data->date = $this->dof->storage('schevents')->get_field($formdata->eventid, 'date');
        } else
        {
            $replace_data->date = $formdata->date;
        }
        $replace_data->teacher = $formdata->teacher;

        // Замена занятия
        return $this->dof->modlib('journal')
            ->get_manager('lessonprocess')
            ->schevent_replace($this->event->id, $replace_data);
    }

    /**
     * Отмена занятия
     *
     * @param stdClass $formdata
     *
     * @return bool
     */
    protected function process_lesson_cancel(stdClass $formdata)
    {
        // Смена статуса
        return $this->dof->modlib('journal')
            ->get_manager('lessonprocess')
            ->schevent_cancel($this->event->id);
    }

    /**
     * Отметка о том, что занятие состоялось
     *
     * @param stdClass $formdata
     *
     * @return bool
     */
    protected function process_lesson_complete(stdClass $formdata)
    {
        // Смена статуса
        return $this->dof->modlib('journal')
            ->get_manager('lessonprocess')
            ->schevent_complete($this->event->id);
    }

    /**
     * Сохранение события
     * @param stdClass $formdata
     * @return bool
     */
    protected function process_event_save(stdClass $formdata) : bool
    {
        $result = true;
        $eventid = $this->event->id;
        if ( isset($formdata->create_event) AND $formdata->create_event )
        {// Сохранение события
            $eventid = $this->save_journal_form_event($formdata);
            $result = (bool) $eventid && $result;
            if ( ! empty($eventid) )
            {
                $this->event->id = $eventid;
            }
        } elseif ( $this->event->id )
        {// обновляем событие
            $result = $this->update_journal_form_event($formdata) && $result;
        }
        return $result;
    }

    /**
     * Удаление события из занятия
     * @return bool
     */
    protected function process_event_delete() : bool
    {
        if ( ! empty($this->event->id) &&
                ! empty($this->plan->id) )
        {
            if ( $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->schevent_only_cancel($this->event->id, $this->plan->id) )
            {
                $this->event->id = 0;
            }
        }
        return true;
    }

    /**
     * Сохранение КТ
     * @param stdClass $formdata
     * @return bool
     */
    protected function process_plan_save(stdClass $formdata) : bool
    {
        $result = true;
        if ( empty($formdata->name) )
        {
            $formdata->name = "";
        }
        // нет имени темы - складываем ее из род.тем
        $name = isset($formdata->name) ? trim($formdata->name) : '';
        // @todo когда появится возможность задавать неограниченное количество родительских тем -
        // изменить алгоритм сохранения
        $parentids = array();
        if ( isset($formdata->parentid3) )
        {// если форма с парент активна
            if ( $formdata->parentid1 OR $formdata->parentid2 OR $formdata->parentid3 )
            {// если указана одна или несколько родительских тем
                $pointnames = array();
                if ( $formdata->parentid1 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid1, 'name');
                    }
                    $parentids[] = $formdata->parentid1;
                }
                if ( $formdata->parentid2 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid2, 'name');
                    }
                    $parentids[] = $formdata->parentid2;
                }
                if ( $formdata->parentid3 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid3, 'name');
                    }
                    $parentids[] = $formdata->parentid3;
                }
                if ( !$name )
                {
                    $formdata->name = implode($pointnames, '. ');
                }
            }
        }
        $planid = $formdata->planid;
        if ( ! $formdata->planid )
        {// контрольной точки нет
            if( property_exists($formdata, 'plan_creation_type') )
            {
                if ( $formdata->plan_creation_type == 'create' )
                {// нужно создать новую контрольную точку
                    $planid = $this->save_journal_form_plan($formdata, $parentids);
                    if ( $this->event->id )
                    {// если событие есть или было создано привязываем событие к контрольной точке
                        $result = (bool) $this->set_journal_form_event_link($formdata, $this->event->id, $planid) && $result;
                    }
                    if ( $planid )
                    {
                        $this->plan->id = $planid;
                    }
                } elseif ( $formdata->plan_creation_type == 'select' )
                {// нужно просто привязать событие к уже существующей контрольной точке
                    if ( $this->event->id )
                    {// если событие есть или было создано привязываем событие к контрольной точке
                        $result = (bool) $this->set_journal_form_event_link($formdata, $this->event->id, $formdata->existing_point) && $result;
                    }
                }
            }
        } else
        {// контрольная точка есть - и она редактируется
            $result =  (bool) $this->update_journal_form_plan($formdata, $parentids) && $result;
        }
        return $result;
    }

    /**
     * Удаление события из занятия
     * @return bool
     */
    protected function process_plan_delete() : bool
    {
        if ( ! empty($this->event->id) &&
                ! empty($this->plan->id) )
        {
            if ( $this->dof->modlib('journal')
                ->get_manager('lessonprocess')
                ->plan_only_cancel($this->event->id, $this->plan->id) )
            {
                $this->plan->id = 0;
                $this->event->planid = 0;
            }
        }
        return true;
    }

    /**
     * Процесс создания/редактирования занятия
     * @todo вставить проверку прав при создании и обновлении всех объектов
     *
     * @param object $formdata - объект из формы класса dof_im_journal_formtopic_teacher
     *
     * @return bool
     */
    protected function process_lesson_save(stdClass $formdata) : bool
    {
        return $this->process_event_save($formdata) & $this->process_plan_save($formdata);
    }

    /** Создать объект учебного события из данных формы
     * @todo возможно следует передавать еще один параметр - $planid - если
     * событие одновременно создается и привязывается к контрольной точке. Узнать, возможно ли одновременно
     * @todo когда появится возможность задавать место события - создать переменную place
     *
     * @return object - нужной структуры для таблицы plans
     * @param object $formdata
     */
    protected function get_event_object_from_form($formdata)
    {
        $event = new stdClass();

        $event->form          = $formdata->event_form;
        $event->date          = $formdata->event_date;
        $event->type          = $formdata->event_type;
        $event->teacherid     = $formdata->event_teacherid;
        $event->duration      = $formdata->event_duration;
        $event->appointmentid = $formdata->event_appointmentid;
        $event->url           = $formdata->event_url;
        $event->place           = $formdata->event_place;

        if ( isset($formdata->event_appointmentid) AND $formdata->event_appointmentid )
        {// назначение существует
            $status = $this->dof->storage('appointments')->get_field($formdata->event_appointmentid, 'status');
            if ( $status == 'patient' OR $status == 'vacation' )
            {// учитель на больничном не может быть назначен событию
                $event->teacherid     = 0;
                $event->appointmentid = 0;
            }
        }

        $event->ahours    = $formdata->event_ahours;
        $event->cstreamid = $formdata->csid;
        if ( isset($formdata->planid) AND $formdata->planid )
        {//Если КТ уже существует, то привязываем событие к нему
            $event->planid = $formdata->planid;
        }

        return $event;
    }

    /** Создать объект точки тематического планирования из данных формы
     *
     * @return object
     * @param object $formdata
     */
    protected function get_plan_object_from_form($formdata)
    {
        $plan    = new stdClass();
        $cstream = $this->dof->storage('cstreams')->get($formdata->csid);

        $plan->id       = $formdata->planid;
        $plan->linkid   = $formdata->linkid;
        $plan->linktype = $formdata->linktype;
        // относительная дата начала
        // из даты начала потока вычитаем дату начала занятия
        if ( isset($formdata->event_date) AND $formdata->event_date AND ( ( isset($formdata->create_event) AND $formdata->create_event )
                OR $formdata->eventid ) )
        {
            $plan->reldate = $formdata->event_date - $formdata->begindate;
        } elseif ( isset($formdata->eventid) AND $formdata->eventid AND
                $event = $this->dof->storage('schevents')->get($formdata->eventid) )
        {
            $plan->reldate = $event->date - $formdata->begindate;
        } elseif ( isset($formdata->pinpoint_date) AND $formdata->pinpoint_date )
        {
            $plan->reldate = $formdata->pinpoint_date - $formdata->begindate;
        }
        $plan->type     = $formdata->type;
        $plan->homework = $formdata->homework;
        // время на домашнюю работу - переводим из часов и минут в секунды
        // переводим часы и минуты в секунды
        // @todo сейчас время на домашнее задание задается только в минутах.
        //       если такое решение приживется
        $homeworkhours  = 0;
        $hoursname      = 'homeworkhoursgroup[hours]';
        $minutesname    = 'homeworkhoursgroup[minutes]';
        //if ( isset($formdata->$hoursname) )
        //{// собираем часы
        //    $homeworkhours += $formdata->$hoursname;
        //}
        if ( isset($formdata->$minutesname) )
        {// собираем минуты
            $homeworkhours += $formdata->$minutesname;
        }
        $plan->homeworkhours  = $homeworkhours;
        // темы созданные из журнала всегда имеют directmap=1
        $plan->directmap      = $formdata->directmap;
        // точная дата начала темы
        $plan->datetheme      = $formdata->begindate;
        $plan->plansectionsid = $formdata->plansectionsid;
        $plan->note           = $formdata->note;
        // шкала наследуется из предмета
        // @todo крайний срок сдачи в этой форме не указывается - возможно в будущем это следует изменить
        // @todo раскоментировать эту строку когда появится возможность указывать номер темы в плане
        // $plan->number
        // @todo когда появится возможность указывать в плане id moodle для синхронизации -
        // раскомментировать это поле
        // $plan->mdlinstance
        // $plan->typesync       =

        if ( empty($formdata->scale_flag) )
        {
            $plan->scale = strip_tags(trim($formdata->scale));
            $mingrade = trim($formdata->mingrade);
            if ( ! empty($mingrade) )
            {
                $plan->mingrade = $mingrade;
            } else
            {
                $plan->mingrade = null;
            }
            $plan->modulegradesconversation = $formdata->modulegradesconversation;
        } else
        {
            $plan->scale = null;
            $plan->mingrade = null;
            $plan->modulegradesconversation = null;
        }

        // сохраняем Moodle грейд итем идентификатор
        if ( ! empty($formdata->mdlgradeitemid) )
        {
            $plan->mdlgradeitemid = intval(strip_tags(trim($formdata->mdlgradeitemid)));
            // тип синхронизации оценок
            $plan->gradessynctype = $formdata->gradessynctype;
            // приоритет оценок
            if ( ! empty($plan->gradessynctype) )
            {
                $plan->gradespriority = $formdata->gradespriority;
            }
            // перезапись оценок в Moodle
            if ( empty($formdata->gradesoverride) )
            {
                $plan->gradesoverride = 0;
            } else
            {
                $plan->gradesoverride = $formdata->gradesoverride;
            }
            $workingoffautomatic = 0;
            if ( ! empty($formdata->workingoffautomaticgradechanges) )
            {
                $workingoffautomatic = $workingoffautomatic | 2;
            }
            if ( ! empty($formdata->workingoffautomaticlessonover) )
            {
                $workingoffautomatic = $workingoffautomatic | 1;
            }
            $plan->workingoffautomatic = $workingoffautomatic;
        } else
        {
            $plan->mdlgradeitemid = null;
            $plan->gradessynctype = null;
            $plan->gradespriority = null;
            $plan->gradesoverride = null;
            $plan->workingoffautomatic = null;
        }
        $plan->gradescompulsion = $formdata->gradescompulsion;
        if ( empty($formdata->name) )
        {
            $amagradeitem = $this->dof->modlib('ama')->grade_item($plan->mdlgradeitemid)->get();
            $plan->name = $amagradeitem->itemname;
        } else
        {
            $plan->name = $formdata->name;
        }
        $plan->estimated = intval($formdata->estimated);

        return $plan;
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и обновить событие
     *
     * @return bool - статус обновления в таблице schevents
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function update_journal_form_event($formdata)
    {
        $event                 = new stdClass;
        $event->ahours         = $formdata->event_ahours;

        if ( ! empty($formdata->event_duration) )
        {
            // Длительность
            $event->duration = $formdata->event_duration;
        }

        $event->salfactor      = $this->dof->workflow('schevents')->calculation_salfactor($this->event->id, true);
        // применяемый итоговый коэффициент
        $event->salfactorparts = serialize($this->dof->workflow('schevents')->calculation_salfactor($this->event->id, true, true));
        // сериализованный объект
        $event->rhours         = $this->dof->workflow('schevents')->calculation_salfactor($this->event->id);
        // Ссылка на занятие (URL)
        $event->url = $formdata->event_url;

        // Кабинет
        $event->place = $formdata->event_place;

        // продолжительность в условных часах
        // обновляем событие в базе
        return $this->dof->storage('schevents')->update($event, $this->event->id);
    }

    /** Привязать событие к контрольной точке
     * @todo больше комментариев в коде функции
     *
     * @return bool
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     * @param int $neweventid[optional] - если событие создавалось из формы - то
     *                                       id только что созданного события
     * @param int $newplanid[optional] - id новой контрольной точки, если она была создана
     */
    protected function set_journal_form_event_link($formdata, $neweventid = false, $newplanid = false)
    {
        $event = new stdClass();
        if ( $neweventid )
        {
            $event->id = $neweventid;
        } else
        {
            $event->id = $formdata->eventid;
        }
        if ( $newplanid )
        {
            $event->planid = $newplanid;
        } else
        {
            $event->planid = $formdata->planid;
        }

        return $this->dof->storage('schevents')->update($event);
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и создать из них учебное событие
     *
     * @return int - id нового созданного события в таблице schevents
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function save_journal_form_event($formdata)
    {
        // получаем объект события из формы
        $event = $this->get_event_object_from_form($formdata);
        // сохраняем событие в базу
        return $this->dof->storage('schevents')->insert($event, $formdata->eventid);
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и создать из них точку тематического планирования на поток
     *
     * @return int - id новой точки тематического планирования в таблице plans
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function save_journal_form_plan($formdata, $parentids = NULL)
    {
        // получаем объект тематического планироания из формы
        $plan = $this->get_plan_object_from_form($formdata);
        // вставляем собранный объект в базу и возвращаем его id
        if ( $id   = $this->dof->storage('plans')->insert($plan) )
        {// обновим род темы
            if ( $this->dof->storage('planinh')->create_point_links($id, $parentids) )
            {
                return $id;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и обновить данные о точке тематического планирования
     *
     * @return bool
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function update_journal_form_plan($formdata, $parentids = NULL)
    {
        // получаем объект тематического планироания из формы
        $plan = $this->get_plan_object_from_form($formdata);
        // обновляем существующую запись и возвращаем результат
        if ( $this->dof->storage('plans')->update($plan) )
        {// обновим род темы
            if ( $this->dof->storage('planinh')->upgrade_point_links($plan->id, $parentids) )
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_cancelled() )
        {
            $this->show_result(true, true);
        }

        if ( $formdata = $this->get_data() )
        {// Обработка данных формы
            $result = false;

            // Перенос занятия на неопределенный срок
            if ( isset($formdata->postpone_lesson) )
            {
                $result = $this->process_lesson_postpone($formdata);
            } elseif ( isset($formdata->replace_lesson) )
            {
                $result = $this->process_lesson_replace($formdata);
            } elseif ( isset($formdata->lesson_cancel) )
            {
                if ( isset($formdata->yes_cancel) )
                {// если стоит подтверждение отмены урока
                    $result = $this->process_lesson_cancel($formdata);
                }
            } elseif ( isset($formdata->lesson_complete) )
            {
                if ( isset($formdata->yes_complete) )
                {// если стоит подтверждение отмены урока
                    $result = $this->process_lesson_complete($formdata);
                }
            } elseif ( isset($formdata->save_event) )
            {// сохранение события
                $result = $this->process_event_save($formdata);
                if ( $result )
                {
                    $result = $this->process_plan_delete();
                }
            } elseif ( isset($formdata->save_plan) )
            {// сохранение контрольной точки
                $result = $this->process_plan_save($formdata);
                if ( $result )
                {
                    $result = $this->process_event_delete();
                }
            } elseif ( isset($formdata->save_lesson) )
            {// сохранения занятия
                $result = $this->process_lesson_save($formdata);
            }

            $this->show_result($result);

            if ( $this->create && ! empty($this->event->id) )
            {
                // Получение объекта события
                $eventobj = $this->dof->modlib('journal')
                    ->get_manager('lessonprocess')
                    ->get_schevent($this->event->id);
                if ( ! empty($eventobj->place) )
                {
                    // Проверка доступности кабинета в указанное время
                    $intersectionevents = $eventobj = $this->dof->modlib('journal')
                        ->get_manager('lessonprocess')
                        ->get_events_intersection_place($this->event->id, $eventobj->date, $eventobj->date + $eventobj->duration, $eventobj->place);
                    if ( ! empty($intersectionevents) )
                    {
                        // Создали новое занятие, перейдем на его редактирование
                        $actionurl = $mform->getAttribute('action') . "&planid={$this->plan->id}&eventid={$this->event->id}&savestatus=1";
                        redirect($actionurl);
                    }
                }
            }
        }
    }

    /** Возвращает список контрольных точек для select-элементов "родительская тема"
     *
     * @param int $pointid - id контрольной точки которую надо
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     * @return array
     */
    private function get_list_point($pointid, $linktype, $linkid, $direcrmap = 1, $noremoveitself = false)
    {
        $points = array();
        $points['0'] = $this->dof->get_string('none','plans');

        // получим список всех элементов тематического планирования
        $plans = $this->dof->storage('plans')->
        get_theme_plan($linktype, $linkid,
                array('active', 'fixed', 'checked'), true, $direcrmap, $noremoveitself);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plans', 'code'=>'use'));

        $plans = $this->dof_get_acl_filtered_list($plans, $permissions);

        if ( ! $plans )
        {// нет ни одного элемента - возвращаем массив с единственным элементом "выбрать"
            return $points;
        }
        // для каждого плана сформируем массив id плана=>имя плана
        foreach ($plans as $plan)
        {
            if ( ! $noremoveitself AND $this->dof->storage('schevents')->
                    get_records(array('planid'=>$plan->id,'status'=>
                        array('plan','completed','postponed','replaced'))) )
            {// если стоит флаг показать самого себя, то активных событий быть не должно
                continue;
            }
            if ( $plan->linktype != 'cstreams' AND $plan->linktype != 'plan' )
            {// только темы потока
                continue;
            }
            if ( $plan->id <> $pointid )
            {// забиваем все, кроме той, которой не надо
                $points[$plan->id] = $plan->name;
            }
        }
        return $points;
    }
    /** Возвращает строку заголовка формы
     *
     * @param int $id[optional] - id редактируемой в данной момент записи
     * @return string
     */
    private function get_form_title($id=null)
    {
        return $this->dof->get_string('form_topic_title', $this->im_code());
    }

    /** Получить текущий учебный период, или false если определить период
     * не представляется возможным
     * @todo в этой форме брать даты начала и окончания только из текущего потока
     *
     * @return object|bool
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     */
    protected function get_current_age($linktype, $linkid)
    {
        if ( $this->is_relative_dataselector($linktype) )
        {// это предмет либо программа - невозможно установить точную дату начала периода
            return false;
        }
        // в этой форме дату начала и окончания периода всегда берем из потока
        return $this->cstream;
    }

    /** Обработчик формы добавления события для нескольких потоков
     *  @param $departmentid - id подразделения
     */
    public function process_save_events()
    {
        // для того, что в библиотеке прописана навигация не ругалась
        $addvars = [];

        //создадим путь на журнал заняти
        $path = $this->dof->url_im('journal','/show_events/index.php?departmentid='.$this->departmentid);

        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу журнала
            redirect($path,'',0);
        }
        //обработчик формы
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {//даные переданы в текущей сессии - получаем
            if ( ! empty($formdata->cstreams) )
            {
                foreach($formdata->cstreams as $csid=>$value)
                {
                    $planid = 0;
                    $eventid = 0;

                    $formdata->csid = $csid;
                    $formdata->linkid = $csid;
                    $formdata->event_teacherid = $this->dof->storage('cstreams')->get_field($csid,'teacherid');
                    $formdata->event_appointmentid = $this->dof->storage('cstreams')->get_field($csid,'appointmentid');

                    // Сохранение занятия
                    $this->process_lesson_save($formdata);
                }
                echo '<div align=\'center\'><b style="color:#0b8000;">'
                        .$this->dof->get_string('add_event_success','journal').'</b></div>';
            }
            else
            {
                echo '<div align=\'center\'><b style="color:#f00;">'
                        .$this->dof->get_string('no_cstreams_choosed','journal').'</b></div>';
            }
        }
    }
}
