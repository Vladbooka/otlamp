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
 * Менеджер учебного процесса. Подсистема проведения занятия.
 * 
 * @package    modlib
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_journal_lessonprocess extends dof_modlib_journal_basemanager
{
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        require_once ($dof->plugin_path(
            'modlib', 
            'journal', 
            '/classes/managers/lessonprocess/lesson.php'
        ));
        require_once ($dof->plugin_path(
            'modlib',
            'journal',
            '/classes/managers/lessonprocess/lessonset.php'
        ));
        
        parent::__construct($dof);
    }

    /**
     * Список обрабатываемых vtнеджером событий
     *
     * @return array
     */
    public function list_catch_events()
    {
        return [
            // Отлавливаем изменение интервала подписки на учебный процесс и обрабатываем занятия
            [
                'plugintype' => 'storage',
                'plugincode' => 'cpassed',
                'eventcode' => 'update'
            ],
            // Отлавливаем изменение интервала подписки на учебный процесс и обрабатываем занятия
            [
                'plugintype' => 'storage',
                'plugincode' => 'schevents',
                'eventcode' => 'update'
            ]
        ];
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
        if ( $gentype === 'storage' && $gencode === 'cpassed' && $eventcode === 'update' )
        {
            // Произошло обновление подписки на cstream, обновим оценки/посещаемость за занятия
            // Идентификатор подписки
            $cpassedid = $intvar;
            
            // Старый объект подписки
            $oldcpassed = $mixedvar['old'];
            
            // Новый объект подписки
            $newcpassed = $mixedvar['new'];
            
            $this->process_cpassed_lessons_data($oldcpassed, $newcpassed);
        }
        if ( $gentype === 'storage' && $gencode === 'schevents' && $eventcode === 'update' )
        {
            // Очищаем оценки, если переход на статус "Заменено", "Отменено", "Отложено"
            if ( empty($mixedvar['new']->status) )
            {
                return true;
            }
            if ( $mixedvar['new']->status == 'plan' || $mixedvar['new']->status == 'completed' )
            {
                return true;
            }
            $schevent = $this->get_schevent($mixedvar['new']->id);
            if ( ! empty($schevent) )
            {
                if ( ! empty($schevent->planid) )
                {
                    $this->remove_plan_grades($schevent->planid);
                }
            }
        }
    }
    
    /**
     * Нормализация переменной с указанием названия поля
     *
     * @param stdClass|array|int $cstreamid - переменная для нормализации
     * @param string $search - название поля для нормализации
     *
     * @return string - результат переменной после нормализации
     */
    public function normalize($var = null, $search = 'id')
    {
        $normalized = null;
        
        // Нормализация данных
        if ( is_object($var) &&
                property_exists($var, $search) &&
                ! empty($var->{$search}) )
        {// Передан объект
            $normalized = $var->{$search};
        } elseif ( is_array($var) &&
                isset($var[$search]) &&
                ! empty($var[$search]) )
        {// Передан массив
            $normalized = $var[$search];
        } else 
        {
            $normalized = $var;
        }
        
        return $normalized;
    }
    
    /**
     * Сохранение посещаемости
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     * @param array $persons - массив студентов с результатами посещения
     * @param int $depid - ID подразделения
     *
     * @return bool - результат
     */
    public function save_students_presence($eventid, $schpresences = [], $depid = 0)
    {
        // Нормализация данных
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        if ( ! is_array($schpresences) ||
                empty($schpresences))
        {
            return false;
        }

        // Проверка прав
        if ( $this->can_save_presence($eventid, $depid) )
        {// Сохранение данных о посещаемости
            // ID текущего пользователя
            $personid = $this->dof->storage('persons')->get_bu()->id;
            
            // Сохранение посещаемости только с приказом
            // Получить объект приказа
            $order = $this->order('save_students_presence');
            // Данные для приказа
            $orderobj = new stdClass();
            // Автор приказа
            $orderobj->ownerid = $personid;
            // Подразделение, к которому он относится
            $orderobj->departmentid = $depid;
            // Дата создания приказа
            $orderobj->date = time();
            // Добавляем данные, о которых приказ
            $orderobj->data = [
                'scheventid' => $eventid,
                'schpresences' => $schpresences
            ];
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
            // Подписываем приказ
            $order->sign($personid);
            // Проверяем подписан ли приказ
            if ( ! $order->is_signed() )
            {// Приказ не подписан
                return false;
            }
            // Исполняем приказ
            if ( ! $order->execute() )
            {// Не удалось исполнить приказ
                return false;
            }
            return true;
        } else
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Сохранение учебного плана
     *
     * @param stdClass|array|int $planid - объект/массив/значение плана
     * @param array $persons - массив студентов с результатами посещения
     * @param int $depid - ID подразделения
     *
     * @return bool - результат
     */
    public function save_plan($planid, stdClass $data)
    {
        $plan = $this->get_plan($planid);
        if ( empty($plan) )
        {
            return false;
        }
        
        // Заполнение данных
        $update_obj = new stdClass();
        $update_obj->id = $plan->id;
        foreach ( $data as $name => $value )
        {
            $update_obj->{$name} = $value;
        }
        
        // Сохранение
        return $this->dof->storage('plans')->update($update_obj);
    }
    
    /**
     * Сохранение события
     *
     * @param stdClass|array|int $eventid - объект/массив/значение плана
     * @param array $persons - массив студентов с результатами посещения
     * @param int $depid - ID подразделения
     *
     * @return bool - результат
     */
    public function save_schevent($eventid, stdClass $data)
    {
        $event = $this->get_schevent($eventid);
        if ( empty($event) )
        {
            return false;
        }
        
        // Заполнение данных
        $updrecord = new stdClass();
        $updrecord->id = $event->id;
        foreach ( $data as $name => $value )
        {
            $updrecord->{$name} = $value;
        }
        
        // Сохранение
        return $this->dof->storage('schevents')->update($updrecord);
    }
    
    /**
     * Сохранение оценок
     *
     * @return bool - результат
     */
    public function save_students_grades($cstream = null, $plan = null, $department = null, $grades = [])
    {
        // Нормализация данных
        $cstream_id = $this->normalize($cstream, 'id');
        if ( empty($cstream_id) )
        {
            return false;
        }
        $plan_id = $this->normalize($plan, 'id');
        if ( empty($plan_id) )
        {
            return false;
        }
        if ( ! is_array($grades) ||
                empty($grades))
        {
            return false;
        }
        if ( empty($department) )
        {
            $department = $this->dof->storage('cstreams')->get_field(['id' => $cstream_id], 'departmentid');
        }
        
        // Проверка прав
        if ( $this->can_save_grades($plan_id, $cstream_id, $department) )
        {// Сохранение данных о посещаемости
            // ID текущего пользователя
            $personid = $this->dof->storage('persons')->get_bu()->id;
            
            // Сохранение оценок только с приказом
            // Получить объект приказа
            $order = $this->order('save_students_grades');
            // Данные для приказа
            $orderobj = new stdClass();
            // Автор приказа
            $orderobj->ownerid = $personid;
            // Подразделение, к которому он относится
            $orderobj->departmentid = $department;
            // Дата создания приказа
            $orderobj->date = time();
            // Добавляем данные, о которых приказ
            if ( ! $orderobj->data = $this->prepare_order_save_students_grades($plan, $personid, $grades) )
            {
                return false;
            }
            
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
            // Подписываем приказ
            $order->sign($personid);
            // Проверяем подписан ли приказ
            if ( ! $order->is_signed() )
            {// Приказ не подписан
                return false;
            }
            // Исполняем приказ
            if ( ! $order->execute() )
            {// Не удалось исполнить приказ
                return false;
            }
            
            // отправляем событие о том, что были выставлены оценки за КТ
            $this->sync_plan_grades_clear($plan_id, $orderobj);
            return true;
        } else
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Сохранить комментарии студентов
     *
     * @return bool - Результат сохранения комментариев
     */
    public function save_students_comments($data = null)
    {
        // Нормализация данных
        if ( empty($data) )
        {
            return false;
        }
        
        // Результат выполнения
        $result = true;
        
        foreach ( $data as $row )
        {
            $commentid = null;
            if ( ! empty($row->commentid) )
            {
                $commentid = $row->commentid;
            }
                
            if ( ! $this->save_student_comment($row->userid, $row->presenceid, $row->comment, $commentid) )
            {
                $result = false;
            }
        }
        
        return $result;
    }
    
    /**
     * Сохранить комментарий студента
     *
     * @return bool - Результат сохранения комментариев
     */
    public function save_student_comment($userid = null, $presenceid = null, $comment_text = null, $commentid = null)
    {
        // Нормализация данных
        $userid = $this->normalize($userid, 'id');
        if ( empty($userid) )
        {
            return false;
        }
        $presenceid = $this->normalize($presenceid, 'id');
        if ( empty($presenceid) )
        {
            return false;
        }
        
        
        // Сохранение комментария
        $comment = new stdClass();
        $comment->plugintype = 'storage';
        $comment->plugincode = 'schpresences';
        $comment->objectid = $presenceid;
        $comment->code = 'public';
        $comment->text = strip_tags(trim($comment_text));
        $comment->personid = $this->dof->storage('persons')->get_bu()->id;
        if ( $commentid > 0 )
        {// Идентификатор указан
            $comment->id = $commentid;
        }
        
        return $this->dof->storage('comments')->save($comment);
    }
    
    /**
     * Смена доступности студентов к элементу
     * 
     * @param int $cstream
     * @param int $plan
     * @param []stdClass $cpasseds
     * @param bool $changeaccessto
     * @param string $area - gradeitem|course
     *
     * @return bool - результат
     */
    public function save_students_access_to_mldarea($cstream = null, $plan = null, $cpasseds = [], $changeaccessto = 0, $area = 'gradeitem')
    {
        // Нормализация данных
        $cstreamid = $this->normalize($cstream, 'id');
        if ( empty($cstreamid) )
        {
            return false;
        }
        $planid = $this->normalize($plan, 'id');
        if ( empty($planid) )
        {
            return false;
        }
        $department = $this->dof->storage('cstreams')->get_field(['id' => $cstreamid], 'departmentid');
        
        if ( $area == 'gradeitem' )
        {
            // область - модуль/курс
            $plan = $this->get_plan($planid);
            if ( ! empty($plan->mdlgradeitemid) )
            {
                $objid = $plan->mdlgradeitemid;
            }
        } else
        {
            // область - курс
            $area = 'course';
            $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstreamid]);
            if ( ! empty($cstream->mdlcourse) )
            {
                $objid = $cstream->mdlcourse;
            } else 
            {
                $programmitem = $this->dof->storage('programmitems')->get_record(['id' => $cstream->programmitemid]);
                if ( ! empty($programmitem->mdlcourse) )
                {
                    $objid = $programmitem->mdlcourse;
                }
            }
        }
        if ( empty($area) )
        {
            return false;
        }
        
        if ( $area )
        
        // Проверка прав
        if ( $this->can_switch_mdl_access($cstreamid, $department) )
        {
            // ID текущего пользователя
            $personid = $this->dof->storage('persons')->get_bu()->id;
            
            // Получить объект приказа
            if ( $changeaccessto )
            {
                $order = $this->order('open_access');
            } else 
            {
                $order = $this->order('close_access');
            }
            // Данные для приказа
            $orderobj = new stdClass();
            // Автор приказа
            $orderobj->ownerid = $personid;
            // Подразделение, к которому он относится
            $orderobj->departmentid = $department;
            // Дата создания приказа
            $orderobj->date = time();
            // Добавляем данные, о которых приказ
            $orderobj->data = new stdClass();
            
            $orderobj->data->area = $area;
            $orderobj->data->objid = $objid;
            if ( empty($cpasseds) )
            {
                $orderobj->data->cpasseds = $this->dof->modlib('learningplan')
                    ->get_manager('cpassed')
                    ->get_active_cpasseds(['cstreamid' => $cstreamid]);
            } else
            {
                $orderobj->data->cpasseds = $cpasseds;
            }
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
            // Подписываем приказ
            $order->sign($personid);
            // Проверяем подписан ли приказ
            if ( ! $order->is_signed() )
            {// Приказ не подписан
                return false;
            }
            
            // Исполняем приказ
            if ( ! $order->execute() )
            {// Не удалось исполнить приказ
                return false;
            }
            return true;
        } else
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Сохранение оценок
     *
     * @return bool - результат
     */
    public function remove_student_lesson_data($cstream = null, $planid = null, $scheventid = null, $listener = [], $department = null)
    {
        // Нормализация данных
        $cstreamid = $this->normalize($cstream, 'id');
        if ( empty($cstreamid) )
        {
            return false;
        }
        if ( empty($department) )
        {
            $department = $this->dof->storage('cstreams')->get_field(['id' => $cstreamid], 'departmentid');
        }
        
        // Проверка прав
        if ( $this->can_change_not_studied($scheventid, $department) )
        {// Сохранение данных о посещаемости
            // ID текущего пользователя
            $personid = $this->dof->storage('persons')->get_bu()->id;
            
            // Сохранение оценок только с приказом
            // Получить объект приказа
            $order = $this->order('remove_student_lesson_data');
            // Данные для приказа
            $orderobj = new stdClass();
            // Автор приказа
            $orderobj->ownerid = $personid;
            // Подразделение, к которому он относится
            $orderobj->departmentid = $department;
            // Дата создания приказа
            $orderobj->date = time();
            
            $orderobj->data = new stdClass();
            $orderobj->data->planid = $planid;
            $orderobj->data->scheventid = $scheventid;
            $orderobj->data->listener = $listener;
            $orderobj->data->cstreamid = $cstreamid;
            
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
            // Подписываем приказ
            $order->sign($personid);
            // Проверяем подписан ли приказ
            if ( ! $order->is_signed() )
            {// Приказ не подписан
                return false;
            }
            // Исполняем приказ
            if ( ! $order->execute() )
            {// Не удалось исполнить приказ
                return false;
            }
            return true;
        } else
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Удаление оценок за КТ
     * 
     * @param string $planid
     * 
     * @magic Не используй метод, если на разобрался с КТ
     * 
     * @return bool
     */
    public function remove_plan_grades($planid)
    {
        // Удаление оценок
        $plan = $this->get_plan($planid);
        
        $result = true;
        if ( ! empty($plan) )
        {
            $gradesdata = $this->dof->storage('cpgrades')->get_records(['planid' => $planid]);
            if ( ! empty($gradesdata) )
            {
                // Удаление оценок
                foreach ( $gradesdata as $grade )
                {
                    $result = $this->dof->storage('cpgrades')->delete($grade->id) && $result;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Очистка данных по занятиям, которые не входят в промежуток подписки на учебный процесс
     * 
     * @param stdClass $odlcpassedobj
     * @param stdClass $newcpassedobj
     *
     * @return bool - результат
     */
    public function process_cpassed_lessons_data($odlcpassedobj, $newcpassedobj)
    {
        global $addvars;
        if ( empty($addvars) || ! is_array($addvars) )
        {
            $addvars = [];
        }
        
        // Если даты не менялись, то нечего обрабатывать
        if ( ((int)$odlcpassedobj->begindate === (int)$newcpassedobj->begindate) && 
                ((int)$odlcpassedobj->enddate === (int)$newcpassedobj->enddate) )
        {
            return true;
        }
        
        // Нормализация данных
        $cstreamid = $newcpassedobj->cstreamid;
        if ( empty($cstreamid) )
        {
            return false;
        }
        
        if ( ! $this->dof->storage('cpassed')->get_record(['id' => $newcpassedobj->id]) )
        {
            return false;
        }
        $listener = [];
        $listener['cpassed'] = $newcpassedobj;
        $listener['person'] = $this->dof->storage('persons')->get_record(['id' => $newcpassedobj->studentid]);
        
        $result = true;
        
        // Получение всех занятий
        $lessonsset = $this->get_lessons($cstreamid);
        if ( ! empty($lessonsset) )
        {
            // Получение занятий
            $lessons = $lessonsset->get_lessons();
            if ( ! empty($lessons) )
            {
                foreach ( $lessons as $lesson )
                {
                    // Затираем все оценки, которые не входят в интервал подписки
                    $lessonouter = ($lesson->get_startdate() < $newcpassedobj->begindate) ||
                        ($lesson->get_startdate() > $newcpassedobj->enddate);
                    
                    // Обновляем Н/О для тех занятий, в которых в старом cpassed студент не обучался, а в новом интервала уже обучается
                    $lessonin = (! $lessonouter) && (($lesson->get_startdate() < $odlcpassedobj->begindate) ||
                        ($lesson->get_startdate() > $odlcpassedobj->enddate));
                    
                    if ( $lessonouter || $lessonin )
                    {
                        $planid = 0;
                        $eventid = 0;
                        if ( $lesson->plan_exists() )
                        {
                            $planid = $lesson->get_plan()->id;
                        }
                        if ( $lesson->event_exists() )
                        {
                            $eventid = $lesson->get_event()->id;
                        }
                        
                        // Формируем приказ и удаляем данные
                        // ID текущего пользователя
                        $personid = $this->dof->storage('persons')->get_bu()->id;
                        // Сохранение оценок только с приказом
                        // Получить объект приказа
                        $order = $this->order('remove_student_lesson_data');
                        // Данные для приказа
                        $orderobj = new stdClass();
                        // Автор приказа
                        $orderobj->ownerid = $personid;
                        // Подразделение, к которому он относится
                        if ( array_key_exists('departmentid', $addvars) )
                        {
                            $orderobj->departmentid = $addvars['departmentid'];
                        } else 
                        {
                            $orderobj->departmentid = 0;
                        }
                        // Дата создания приказа
                        $orderobj->date = time();
                        
                        $orderobj->data = new stdClass();
                        $orderobj->data->planid = $planid;
                        $orderobj->data->scheventid = $eventid;
                        $orderobj->data->listener = $listener;
                        $orderobj->data->cstreamid = $cstreamid;
                        $orderobj->data->quiteschpresencedelete = true;
                        
                        // Сохраняем приказ в БД и привязываем экземпляр приказа к id
                        $order->save($orderobj);
                        // Подписываем приказ
                        $order->sign($personid);
                        // Проверяем подписан ли приказ
                        if ( ! $order->is_signed() )
                        {// Приказ не подписан
                            $result = false;
                        }
                        
                        // Занятие за рамками подписки, очищаем данные
                        $result = $order->execute() && $result;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Формирование данных для приказа
     *
     * @return bool | stdClass - результат
     */
    public function prepare_order_save_students_grades($plan = null, $teacher = null, $grades = [])
    {
        // Нормализация данных
        $plan = $this->normalize($plan, 'id');
        if ( empty($plan) )
        {
            return false;
        }
        $teacher = $this->normalize($teacher, 'id');
        if ( empty($teacher) )
        {
            return false;
        }
        if ( ! is_array($grades) ||
                empty($grades))
        {
            return false;
        }
        
        // Сбор объекта данных для приказа
        $order_data_obj = new stdClass();
        $order_data_obj->date = dof_im_journal_get_date(time());
        $order_data_obj->teacherid = $teacher;
        $order_data_obj->planid = $plan;
        $order_data_obj->grades = [];
        foreach ( $grades as $grade )
        {
            $schevent = $this->get_schevent_by_plan($order_data_obj->planid);
            if ( ! empty($schevent) )
            {
                $cpassed = $this->dof->storage('cpassed')->get($grade['cpassedid']);
                if (!empty($cpassed))
                {
                    $presence = $this->get_present_status($cpassed->studentid, $schevent->id);
                    if ($presence === false)
                    {// у урока есть событие, но нет присутствия (== не обучался), поэтому не сохраняем оценку
                        continue;
                    }
                }
            }
            if ( array_key_exists('workingoff', $grade) )
            {
                if ( strlen($grade['grade']) )
                {
                    $workingoff = $grade['workingoff'];
                } else 
                {
                    $workingoff = 0;
                }
            } else 
            {
                $workingoff = -1;
            }
            
            $order_data_obj->grades[$grade['cpassedid']] = [
                'cpassedid' => $grade['cpassedid'],
                'grade' => $grade['grade'],
                'status' => 'tmp',
                'estimatedin' => ! empty($grade['estimatedin']) ? $grade['estimatedin'] : 'dof',
            ];
            if ( $workingoff !== -1 )
            {
                $order_data_obj->grades[$grade['cpassedid']]['workingoff'] = $workingoff;
            }
        }
        
        return $order_data_obj;
    }
    
    /**
     * Проверка, что студент является должником по занятию
     * 
     * @param dof_lesson $lesson 
     * @param stdClass $cpassed
     * 
     * 
     * @return false|int (1 - пропустил занятие, 2 - оценка ниже проходного)
     */
    public function is_debtor(dof_lesson $lesson, stdClass $cpassed)
    {
        if ( ! $lesson->plan_exists() )
        {
            return false;
        }
        if ( $lesson->event_exists() )
        {
            if ( ($this->get_present_status($cpassed->studentid, $lesson->get_event()->id) === false) && ($lesson->get_event()->status != 'replaced') )
            {
                // студент не обучался в этом время
                return false;
            }
        }
        
        // Получение данных о работе на занятии
        $gradedata = $lesson->get_listener_gradedata($cpassed->id);
        
        $plan = $lesson->get_plan();
        
        // обязательность оценки - обычная оценка, у занятия не бывает должников
        if ( empty($plan->gradescompulsion) )
        {
            return false;
        }
        
        // текущее время 
        $currenttime = time();
        
        // длительность занятия по умолчанию
        $duration = 0;
        if ( $lesson->event_exists() )
        {
            $duration = $lesson->get_event()->duration + (int)$this->dof->storage('config')->get_config_value(
                    'organizational_time', 
                    'im', 
                    'journal', 
                    optional_param('departmentid', 0, PARAM_INT));
        }
        
        // урок пройден
        $isskipped = $currenttime > $lesson->get_startdate() + $duration;
        if ( ! $isskipped )
        {
            return false;
        }
        
        // обязательно требуется оценка
        if ( empty($gradedata->grades) )
        {
            return 1;
        } else if ( $gradedata->overenroltime === true )
        {
            // студент не обучался в это время
            return false;
        }
        $grade = reset($gradedata->grades);
        $currentgrade = $grade->item->grade;
        if ( strlen($currentgrade) && 
                ($plan->gradescompulsion == 1) )
        {
            return false;
        }
        if ( ! strlen($currentgrade) &&
                ($plan->gradescompulsion == 1 || $plan->gradescompulsion == 2) )
        {
            return 1;
        }
        
        // обязательно требуется положительная оценка
        if ( $plan->gradescompulsion == 2 )
        {
            $passgrade = $this->dof->modlib('journal')->get_manager('scale')->get_plan_mingrade($plan);
            $scale = $this->dof->modlib('journal')->get_manager('scale')->get_plan_scale($plan);
            
            if ( strlen($passgrade) > 0 )
            {
                $res = ! $this->dof->modlib('journal')->get_manager('scale')->is_positive_grade($currentgrade, $passgrade, $scale);
                return $res ? 2 : false;
            } else 
            {
                return false;
            }
        }
        
        return 1;
    }
    
    /**
     * Перевести статус события на новый
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     * @param string $newstatus - новый статус
     *
     * @return bool
     */
    public function changestatus_schevent($eventid = null, $newstatus = null, $departmentid = 0)
    {
        // Нормализация данных
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        if ( empty($newstatus) )
        {
            return false;
        }
        
        if ( $this->can_changestatus_schevent($eventid, $departmentid) )
        {// Результат смены
            return $this->dof->workflow('schevents')->change($eventid, $newstatus);
        } else 
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Перевести статус плана на новый
     *
     * @param stdClass|array|int $planid - объект/массив/значение плана
     * @param string $newstatus - новый статус
     *
     * @return bool
     */
    public function changestatus_plan($planid = null, $newstatus = '', $departmentid = 0)
    {
        // Нормализация данных
        $eventid = $this->normalize($planid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        if ( empty($newstatus) )
        {
            return false;
        }
        
        if ( $this->can_changestatus_plan($planid, $departmentid) )
        {// Результат смены
            return $this->dof->workflow('plans')->change($planid, $newstatus);
        } else
        {// Нет прав
            return false;
        }
    }
    
    /**
     * Перенос занятия
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function schevent_replace($eventid = null, $replace)
    {
        // Нормализация данных
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        $date = $this->normalize($replace, 'date');
        if ( is_object($date) || empty($date) )
        {
            return false;
        }
        $appointmentid = $this->normalize($replace, 'teacher');
        if ( is_object($appointmentid) || empty($appointmentid) )
        {
            return false;
        }
        
        // Объект сохранения
        $obj = new stdClass();
        $obj->date = $date;
        $obj->appointmentid = $appointmentid;
        
        // Статус
        $status = false;
        
        if ( $this->can_changestatus_schevent($eventid) )
        {// Результат смены
            $status = $this->dof->workflow('schevents')->change($eventid, 'replaced');
        }
        
        if ( $this->dof->im('journal')->is_access_replace($eventid) &&
                $this->dof->storage('schevents')->replace_events($eventid, $obj) )
        {
            $status = true;
        }
        
        return $status;
    }
    
    /**
     * Отмена занятия занятия
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function schevent_cancel($eventid = null)
    {
        // Нормализация данных
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        return $this->dof->storage('schevents')->cancel_event($eventid, true, true);
    }
    
    /**
     * Отмена КТ у занятия
     *
     * @param int $planid
     *
     * @return bool
     */
    public function plan_cancel($planid = null) : bool
    {
        // Нормализация данных
        $planid = $this->normalize($planid, 'id');
        if ( empty($planid) )
        {
            return false;
        }
        
        return $this->dof->storage('plans')->cancel_checkpoint($planid);
    }
    
    /**
     * Отмена у занятия только КТ и отлинковка от события
     *
     * @param int $eventid
     * @param int $planid
     *
     * @return bool
     */
    public function plan_only_cancel(int $eventid, int $planid) : bool
    {
        if ( empty($eventid) || empty($planid) )
        {
            return false;
        }
        
        if ( !$this->save_schevent($eventid, (object)['planid' => null]) )
        {
            return false;
        }
        
        return $this->plan_cancel($planid);
    }
    
    /**
     * Отмена у занятия только события и отлинковка от контрольной точки
     *
     * @param int $eventid
     * @param int $planid
     *
     * @return bool
     */
    public function schevent_only_cancel(int $eventid, int $planid) : bool
    {
        if ( empty($eventid) || empty($planid) )
        {
            return false;
        }
        
        if ( !$this->save_schevent($eventid, (object)['planid' => null]) )
        {
            return false;
        }
        
        return $this->schevent_cancel($eventid);
    }
    
    /**
     * Отмена занятия
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function schevent_complete($eventid = null, $depid = null)
    {
        // Нормализация данных
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // обработка учащихся, не обучавшихся в момент урока
        $this->process_notstudied($eventid, $depid);
        
        return $this->changestatus_schevent($eventid, 'completed', $depid);
    }
    
    /**
     * Обработка учащихся, которые не обучались во время урока
     * (cpassed был в статусе "приостановлен")
     *
     * @param int $eventid - идентификатор события
     * @param int $depid - идентификатор подразделения
     *
     * @return void
     */
    protected function process_notstudied($eventid, $depid)
    {
        $schevent = $this->get_schevent($eventid);
        
        if (empty($schevent) || empty($schevent->cstreamid))
        {
            return false;
        }
        $cstreamid = $schevent->cstreamid;
        
        $planid = 0;
        if (!empty($schevent->planid))
        {
            $planid = $schevent->planid;
        }
        
        
        $lesson = $this->get_lesson($cstreamid, $eventid, $planid);
        $listeners = $lesson->get_listeners_data();
        foreach($listeners as $cpassedid => $listener)
        {
            $startdate = $lesson->get_startdate();
            $enddate = $startdate + $schevent->duration;
            // список статусов, которые применялись во время проведения урока
            $statuschanges = $this->dof->storage('statushistory')->get_statuses(
                'storage',
                'cpassed',
                $cpassedid,
                $startdate,
                $enddate
            );
            // статус, который был на момент начала урока
            $status = $this->dof->storage('statushistory')->get_status(
                'storage',
                'cpassed',
                $cpassedid,
                $startdate
            );
            
            if ($status == 'suspend' && empty($statuschanges))
            {// статус на момент начала урока - приостановлен и в течение урока не менялся
                
                // Ученик не обучался, создадим приказ и очистим все данные (оценка/посещаемость)
                $this->remove_student_lesson_data($cstreamid, $planid, $eventid, $listener, $depid);
            }
            
        }
    }
    
    /**
     * Проверка права на заполнение посещаемости
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function can_save_presence($eventid = null, $departmentid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->im('journal')->is_access('give_attendance', $eventid, null, $departmentid) ||
            $this->dof->im('journal')->is_access('give_attendance/own_event', $eventid, null, $departmentid);
    }
    
    /**
     * Проверка права на сохранение оценок
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function can_save_grades($planid = null, $cstreamid = null, $departmentid = null)
    {
        // Нормализация
        $planid = $this->normalize($planid, 'id');
        if ( empty($planid) )
        {
            return false;
        }

        // Право выставления оценок за учебный процесс
        $gradecstream = $this->dof->im('journal')->is_access('give_grade', $cstreamid, null, $departmentid) ||
            $this->dof->im('journal')->is_access('give_grade/in_own_journal', $cstreamid, null, $departmentid);
        
        // Проверим, что занятие не является заменой
        $replace = false;
        $schevent = $this->get_schevent_by_plan($planid);
        if ( ! empty($schevent) )
        {
            $replacedschevent = $this->dof->storage('schevents')->get_replaced_event($schevent->id);
            if ( ! empty($replacedschevent) )
            {
                $replace = true;
            }
        }
        
        // Право выставления оценок за контрольные точки
        $gradeplan = $this->dof->im('journal')->is_access('give_grade_plan', $planid, null, $departmentid) ||
            $this->dof->im('journal')->is_access('give_grade_plan/owner', $planid, null, $departmentid);
        
        return ($gradecstream && !$replace) || $gradeplan;
    }
    
    /**
     * Проверка права на задание темы для события
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function can_save_theme($eventid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->im('journal')->is_access('give_theme_event', $eventid) ||
                    $this->dof->im('journal')->is_access('give_theme_event/own_event', $eventid);
    }
    
    /**
     * Проверка права на задание темы для события
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function can_change_not_studied($eventid = null, $departmentid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->im('journal')->is_access('remove_not_studied', $eventid, null, $departmentid) ||
            $this->dof->im('journal')->is_access('remove_not_studied/owner', $eventid, null, $departmentid);
    }
    
    /**
     * Проверка права на смену статуса события
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return bool
     */
    public function can_changestatus_schevent($eventid = null, $depid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->workflow('schevents')->is_access('changestatus', $eventid, null, $depid);
    }
    
    /**
     * Проверка права на смену статуса плана
     *
     * @param stdClass|array|int $planid - объект/массив/значение плана
     *
     * @return bool
     */
    public function can_changestatus_plan($planid = null)
    {
        // Нормализация
        $planid = $this->normalize($planid, 'id');
        if ( empty($planid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->workflow('plans')->is_access('changestatus', $planid);
    }
    
    /**
     * Право на изменение доступа к грейд-итему
     */
    public function can_switch_mdl_access($cstreamid, $departmentid)
    {
        // Право выставления оценок за учебный процесс
       if ( $this->dof->im('journal')->is_access('switch_mdl_access', $cstreamid, null, $departmentid) ||
            $this->dof->im('journal')->is_access('switch_mdl_access/own', $cstreamid, null, $departmentid) )
       {
           return true;
       }
       
       return false;
    }
    
    /**
     * Проверка включения контроля доступа в СДО
     *
     * @todo пока не решили куда класть этот метод
     * @return bool
     */
    public function is_control_active()
    {
        return ! empty(get_config('local_authcontrol', 'authcontrol_select'));
    }
    
    /**
     * Получить статус присутствия ученика на занятии
     *
     * @param int $studentid - id студента
     * @param int $scheventid - ученика
     *
     * @return mixed int статус присутствия или bool false если событие не найдено
     */
    public function get_present_status($studentid, $scheventid)
    {
        return $this->dof->storage('schpresences')->get_present_status($studentid, $scheventid);
    }
    
    /**
     * Получить объект события из базы данных
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return stdClass
     */
    public function get_schevent($eventid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->storage('schevents')->get_record(['id' => $eventid]);
    }
    
    /**
     * Получить объект события из базы данных
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return stdClass | null
     */
    public function get_last_replaced_schevent($eventid = null)
    {
        $eventid = (int)$eventid;
        
        if ( $replace = $this->dof->storage('schevents')->get_record(['replaceid' => $eventid, 'status' => array_keys($this->dof->workflow('schevents')->get_meta_list('real'))]) )
        {// Если замена есть, найдем ее замену
            return $this->get_last_replaced_schevent($replace->id);
        }else
        {// Это последняя замена
            return null;
        }
    }
    
    /**
     * Получить объект события из базы данных
     *
     * @param stdClass|array|int $eventid - объект/массив/значение события
     *
     * @return stdClass
     */
    public function get_next_replaced_schevent($eventid = null)
    {
        $eventid = (int)$eventid;
        
        if ( $replace = $this->dof->storage('schevents')->get_record(['replaceid' => $eventid, 'status' => array_keys($this->dof->workflow('schevents')->get_meta_list('real'))]) )
        {// Если замена есть, найдем ее замену
            return $replace;
        }else
        {// Это последняя замена
            return null;
        }
    }
    
    /**
     * Получить объект события из базы данных
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return bool | stdClass
     */
    public function get_schevent_by_plan($planid = null)
    {
        // Нормализация
        $planid = $this->normalize($planid, 'id');
        if ( empty($planid) )
        {
            return false;
        }
        
        $result = $this->dof->storage('schevents')->get_records(['planid' => $planid]);
        if ( ! empty($result) )
        {
            $result = array_pop($result);
        } else 
        {
            $result = false;
        }
        
        // Результат
        return $result;
    }
    
    /**
     * Получить объект плана из базы данных
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return stdClass
     */
    public function get_plan($planid = null)
    {
        // Нормализация
        $planid = $this->normalize($planid, 'id');
        if ( empty($planid) )
        {
            return false;
        }
        
        // Результат
        return $this->dof->storage('plans')->get_record(['id' => $planid]);
    }
    
    /**
     * Получить комментарии студента к событию
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return []
     */
    public function get_student_comments($eventid = null, $userid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        $userid = $this->normalize($userid, 'id');
        if ( empty($userid) )
        {
            return false;
        }
        
        // Параметры для поиска
        $params = [];
        $params['personid'] = $userid;
        $params['eventid'] = $eventid;
        $presence = $this->dof->storage('schpresences')->get_records($params);
        if ( ! empty($presence) )
        {
            $presence = array_pop($presence);
            
            // Получение списка комментариев
            $comments = $this->dof->storage('comments')->get_comments_by_object('storage', 'schpresences', $presence->id, 'public');
            if ( ! empty($comments) )
            {
                foreach ( $comments as &$comment )
                {
                    $comment->presenceid = $presence->id;
                }
                
                return $comments;
            }
        }
        
        return [];
    }
    
    /**
     * Получить объект присутствия пользователя на событи (presence)
     *
     * @param stdClass|array|int $planid - объект/массив/значение КТ
     *
     * @return []
     */
    public function get_student_presences($eventid = null, $userid = null)
    {
        // Нормализация
        $eventid = $this->normalize($eventid, 'id');
        if ( empty($eventid) )
        {
            return false;
        }
        $userid = $this->normalize($userid, 'id');
        if ( empty($userid) )
        {
            return false;
        }
        
        // Параметры для поиска
        $params = [];
        $params['personid'] = $userid;
        $params['eventid'] = $eventid;
        
        return $this->dof->storage('schpresences')->get_records($params);
    }
        
    /**
     * Получить список занятий в учебном процессе, отсортированных по дате
     *
     * @param int $cstreamid - ID учебного процесса
     * @param bool $showall - все/последние(30 дней назад от текущей) занятия
     * 
     * @return dof_lessonset
     */
    public function get_lessons($cstreamid, $showall = false)
    {
        // Получение контрольных точек для учебного процесса
        $checkpoints = $this->dof->storage('schevents')->get_mass_date(
                (int)$cstreamid,
                ['plan', 'completed', 'postponed', 'replaced'],
                ['active', 'fixed', 'checked', 'completed'],
                true,
                $showall
                );
        
        // Инициализация занятий
        $lessons = [];
        foreach ( $checkpoints as $checkpoint )
        {
            $plan = null;
            if ( ! empty($checkpoint->plan) )
            {// Добавление тематического плана
                $plan = $checkpoint->plan;
            }
            $event = null;
            if ( ! empty($checkpoint->event) )
            {// Добавление события
                $event = $checkpoint->event;
            }
            $lessons[] = new dof_lesson($this->dof, $plan, $event);
        }
        $lessonset = new dof_lessonset($this->dof, $lessons);

            // Добавление полного списка подписок на учебный процесс
        $showjunkcfg = $this->dof->storage('config')->get_config_value(
                'showjunkstudents', 
                'im', 
                'journal',
                optional_param('departmentid', 0, PARAM_INT));
        // Получение всех подписок на предмето-класс
        $cpasseds = (array)$this->dof->storage('cpassed')->get_records(
            [
                'cstreamid' => $cstreamid,
                'status' => array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunkcfg))
            ]
        );
        
        // Добавим подписки в пачку занятий
        $lessonset->add_cpasseds($cpasseds);
        
        // Добавить подписки на учебный процесс к списку занятий
        $lessonset->merge_cpasseds();
        
        return $lessonset;
    }
    
    /**
     * Получить занятие
     *
     * @param int $cstreamid - ID учебного процесса
     * @param int $eventid
     * @param int $planid
     *
     * @return dof_lesson
     */
    public function get_lesson($cstreamid, $eventid, $planid)
    {
        // Получение КТ
        if ( empty($planid) )
        {
            $plan = null;
        } else 
        {
            $plan = $this->dof->storage('plans')->get_record(['id' => $planid]);
        }
        // Получение события
        if ( empty($eventid) )
        {
            if ( ! empty($plan) && in_array($plan->status, ['active', 'fixed', 'checked', 'completed']) )
            {
                $schevents = $this->dof->storage('schevents')->get_records(
                        [
                            'planid' => $plan->id,
                            'status' => ['plan', 'completed', 'postponed']
                        ]
                        );
                $schevent = end($schevents);
                if ( ! empty($schevent) )
                {
                    $event = $schevent;
                } else 
                {
                    $event = null;
                }
            } else
            {
                $event = null;
            }
        } else
        {
            $event = $this->dof->storage('schevents')->get_record(['id' => $eventid]);
        }
        
        // Формирование объекта занятия
        $lesson = new dof_lesson($this->dof, $plan, $event, $cstreamid);
        
        return $lesson;
    }
    
    /**
     * Получить причину
     *
     * @param int $id
     *
     * @return stdClass
     */
    public function get_reason($id)
    {
        return $this->dof->storage('schabsenteeism')->get_record(['id' => intval($id)]);
    }
    
    /**
     * Проверка занятости кабинет в выбранный промежуток
     *
     * @param number $eventid
     * @param number $datestart
     * @param number $datestop
     * @param number $place
     *
     * @return false | array
     */
    public function get_events_intersection_place($eventid = 0, $datestart = 0, $datestop = 0, $place = '')
    {
        return $this->dof->storage('schevents')->get_events_intersection_place($eventid, $datestart, $datestop, $place);
    }
    
    /**
     * Возвращает идентификатор курса Moodle учебного процесса
     * 
     * @param int $cstreamid
     * 
     * @return false|int
     */
    public function get_cstream_mdlcourse($cstreamid)
    {
        $cstream = $this->dof->storage('cstreams')->get_record(['id' => $cstreamid]);
        if ( empty($cstream) )
        {
            return false;
        }
        if ( ! empty($cstream->mdlcourse) )
        {
            return $cstream->mdlcourse;
        }
        $pitem = $this->dof->storage('programmitems')->get_record(['id' => $cstream->programmitemid]);
        if ( ! empty($pitem->mdlcourse) )
        {
            return $pitem->mdlcourse;
        }
        
        return false;
    }
    
    /**
     * Возвращает идентификатор курса Moodle КТ
     *
     * @param int $cstreamid
     *
     * @return false|int
     */
    public function get_plan_mdlcourse($planid)
    {
        $plan = $this->dof->storage('plans')->get_record(['id' => $planid]);
        if ( empty($plan) )
        {
            return false;
        }
        if ( ! empty($plan->mdlgradeitemid) )
        {
            return $this->dof->modlib('ama')->grade_item($plan->mdlgradeitemid)->get()->courseid;
        }
        if ( $plan->linktype != 'cstreams')
        {
            return false;
        }
        $cstream = $this->dof->storage('cstreams')->get_record(['id' => $plan->linkid]);
        if ( empty($cstream) )
        {
            return false;
        }
        if ( ! empty($cstream->mdlcourse) )
        {
            return $cstream->mdlcourse;
        }
        $pitem = $this->dof->storage('programmitems')->get_record(['id' => $cstream->programmitemid]);
        if ( ! empty($pitem->mdlcourse) )
        {
            return $pitem->mdlcourse;
        }
        
        return false;
    }
    
    /**
     * Получение грейд итемов курса
     * 
     * @param int $mdlcourseid
     * 
     * @return array
     */
    public function get_gradeitems_for_lesson($mdlcourseid)
    {
        $result = [];
        
        $gradeitems = $this->dof->modlib('ama')->grade_item(false)->get_course_grade_items($mdlcourseid);
        if ( ! empty($gradeitems) )
        {
            foreach ($gradeitems as $gradeitem)
            {
                if ( in_array($gradeitem->itemtype, ['mod', 'manual']) )
                {
                    $result[$gradeitem->id] = $gradeitem->itemname;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Получение доступных типов обязательности оценки
     * 
     * @return array
     */
    public function get_available_gradescompulsion()
    {
        return [
            0 => $this->dof->get_string('gradescompulsion_normal', 'journal', null, 'modlib'),
            1 => $this->dof->get_string('gradescompulsion_need_grade', 'journal', null, 'modlib'),
            2 => $this->dof->get_string('gradescompulsion_need_positive_grade', 'journal', null, 'modlib')
        ];
    }
    
    /**
     * Получить открытые занятия слушателей учебного процесса
     * 
     * @param stdClass $cstream
     * @param dof_lessonset $lessonset
     * 
     * @example результат [studentid1 => [planid1, lessonid2], studentid2 => [planid2, lessonid2]]
     * 
     * @return array 
     */
    public function get_opened_lessons_for_students(stdClass $cstream, dof_lessonset $lessonset = null)
    {
        global $CFG;
        $result = [];
        if ( ! $this->is_control_active() )
        {
            return $result;
        }
        $mdlcourse = $this->get_cstream_mdlcourse($cstream->id);
        if ( empty($mdlcourse) )
        {
            return false;
        }
        
        if ( is_null($lessonset) )
        {
            $lessonset = $this->dof->modlib('journal')->get_manager('lessonprocess')->get_lessons($cstream->id, true);
        }
        
        $cpasseds = $lessonset->get_cpasseds_fullset_lastname();
        if ( empty($cpasseds) )
        {
            // не по кому собирать данные
            return $result;
        }
        
        $lessons = $lessonset->get_lessons();
        if ( empty($lessons) )
        {
            // нет занятий
            return $result;
        }
        $usersids = [];
        foreach ( $cpasseds as $cpassed )
        {
            $result[$cpassed->studentid] = [];
            do
            {
                $person = $this->dof->storage('persons')->get_record(['id' => $cpassed->studentid]);
                if ( empty($person->sync2moodle) || empty($person->mdluser) )
                {
                    break;
                }
                if ( ! $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
                {
                    break;
                }
                $usersids[$person->mdluser] = $cpassed->studentid;
            } while (false);
        }
        if ( empty($usersids) )
        {
            // нет пользователей moodle
            return $result;
        }
        require_once $CFG->dirroot . '/local/authcontrol/lib.php';
        $data = local_authcontrol_get_context_info_data($mdlcourse, array_keys($usersids));
        foreach ( $lessons as $lesson )
        {
            if ( ! $lesson->plan_exists() ||
                    ! $lesson->mdl_gradeitem_exists())
            {
                // доступ открывается только к плану с привязкой к грейд итему
                continue;
            }
            $amagradeitem = $lesson->get_mdl_gradeitem();
            $gradeitem = $amagradeitem->get();
            if ( ! $gradeitem->is_external_item() )
            {
                continue;
            }
            $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance, $gradeitem->courseid);
            foreach ( $data as $authdata )
            {
                if ( empty($authdata->moduleid) ||
                        $authdata->moduleid === $cm->id )
                {
                    $result[$usersids[$authdata->userid]][] = $lesson->get_plan()->id;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Синхронизация оценок КТ с Mdlgradeitem
     * 
     * @param int $planid
     * 
     * @return bool
     */
    public function sync_plan_grades($planid)
    {
        $plan = $this->dof->storage('plans')->get_record(['id' => $planid]);
        if ( empty($plan) ||
                $plan->linktype != 'cstreams' )
        {
            return false;
        }
        $cstream = $this->dof->storage('cstreams')->get_record(['id' => $plan->linkid]);
        if ( ! $this->can_save_grades($plan->id, $cstream->id, $cstream->departmentid) )
        {
            return false;
        }
        
        $this->sync_plan_grades_clear($planid);
        return true;
    }
    
    /**
     * Инициация синхронизации оценок без проверок
     * Запуск только из доверенных мест
     * 
     * @param int $planid
     * 
     * @return void
     */
    protected function sync_plan_grades_clear($planid)
    {
        // отправляем событие о том, что были выставлены оценки за КТ
        $this->dof->send_event('modlib', 'journal', 'plan_grades_saved', $planid);
    }
    
    /**
     * Метод проверяет, необходимо ли выставить слушателю флаг "отработка"
     * Используется в плагине sync/grades при синхронизации оценок из Moodle
     * 
     * @param stdClass $cpassedid
     * @param stdClass $cpassed
     * 
     * @return bool
     */
    public function should_set_workingoff(stdClass $plan, stdClass $cpassed)
    {
        // получение занятия
        $lesson = $this->get_lesson(null, null, $plan->id);
        if ( ! $lesson->plan_exists() )
        {
            return false;
        }
        
        // дефолтные значения флагов
        $lessonover = false;
        $gradechanges = false;
        
        if ( $this->dof->storage('plans')->is_active_workingoff_lesson_over($plan) )
        {
            // длительность занятия
            $duration = 0;
            if ( $lesson->event_exists() )
            {
                $duration = $lesson->get_event()->duration + (int)$this->dof->storage('config')->get_config_value(
                        'organizational_time',
                        'im',
                        'journal',
                        optional_param('departmentid', 0, PARAM_INT));
            }
            
            $lessonover =  time() > $lesson->get_startdate() + $duration;
        }
        
        if ( $this->dof->storage('plans')->is_active_workingoff_grade_changes($plan) )
        {
            // получение данных о работе на занятии
            $gradedata = $lesson->get_listener_gradedata($cpassed->id);
            
            if ( ! empty($gradedata->grades) )
            {
                $grade = reset($gradedata->grades);
                $gradechanges = (bool)strlen($grade->item->grade);
            }
        }
        
        return $lessonover || $gradechanges;
    }
}
