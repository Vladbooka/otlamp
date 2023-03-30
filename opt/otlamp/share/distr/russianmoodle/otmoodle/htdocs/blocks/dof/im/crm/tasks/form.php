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

// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы добавления - редактирования задачи
 */
class block_dof_im_crm_task_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        $this->dof    = $this->_customdata->dof;
        $taskid =  $this->_customdata->taskid;
        $departmentid =  $this->_customdata->departmentid;

        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Создаем заголовок формы
        if ( empty($taskid) )
        {
            $mform->addElement('header','formtitle', $this->dof->get_string('create_task_title', 'crm'));

            //Данные для автозаполнения персон
            $options = array();
            $options['plugintype']   = "storage";
            $options['plugincode']   = "persons";
            $options['sesskey']      = sesskey();
            $options['type']         = 'autocomplete';
            $options['departmentid'] = $departmentid;
            $options['querytype'] = "persons_list";


            // Кому поручена
            $mform->addElement('dof_autocomplete', 'assignedpersonid', $this->dof->get_string('assignperson','crm'),'', $options);
            $mform->setType('assignedpersonid', PARAM_INT);

            // Заголовок
            $mform->addElement('text','title', $this->dof->get_string('task_title','crm'));
            $mform->setType('title', PARAM_TEXT);

            // Описание
            $mform->addElement('editor','about', $this->dof->get_string('about_task','crm'));

            // Дата актуализации
            $mform->addElement('date_time_selector','actualdate', $this->dof->get_string('actual_date','crm'));

            // Дедлайн
            $mform->addElement('date_time_selector','deadlinedate', $this->dof->get_string('deadline_date','crm'));
        } else
        {
            $task = $this->dof->storage('tasks')->get($taskid);
            $mform->addElement('header','formtitle', $this->dof->get_string('edit_task_title', 'crm'));

            // Кому поручена
            $mform->addElement('text', 'assignedperson', $this->dof->get_string('assignperson','crm'), array('disabled' => 'disabled'));

            $mform->setDefault('assignedperson', $this->dof->storage('persons')->get_fullname($task->assignedpersonid));
            $mform->setType('assignedperson', PARAM_TEXT);

            // Заголовок
            $mform->addElement('text','title', $this->dof->get_string('task_title','crm'));
            $mform->setType('title', PARAM_TEXT);

            // Описание
            $mform->addElement('editor','about', $this->dof->get_string('about_task','crm'));

            // Дата актуализации
            $mform->addElement('date_time_selector','actualdate', $this->dof->get_string('actual_date','crm'));

            // Дедлайн
            $mform->addElement('date_time_selector','deadlinedate', $this->dof->get_string('deadline_date','crm'));
        }

        // кнопка "сброс"
        $this->add_action_buttons('true', $this->dof->get_string('create_task', 'crm'));
    }


    function validation($data, $files)
    {
        $errors = array();

        // возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Функци для обработки данных из формы создания/редактирования
     *
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{
		    redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
		}
		if ( $this->is_submitted() AND $formdata = $this->get_data() )
		{
		    $person = $this->dof->storage('persons')->get_bu();
		    $taskid =  $this->_customdata->taskid;
		    if ( $taskid > 0 )
		    {
		        $update = new stdClass();

		        $update->id = $taskid;
		        if ( empty($formdata->title) )
                {
                    $update->title = $this->dof->get_string('default_task_title','crm');
                } else
                {
                    $update->title = $formdata->title;
                }
		        $update->about = $formdata->about['text'];
		        $update->actualdate = $formdata->actualdate;
		        $update->deadlinedate = $formdata->deadlinedate;

		        if ( $this->dof->storage('tasks')->update_task($update) )
		        {// Если успешно обновлена
		            $addvars['success'] = 1;
		            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
		        } else
		        {// Если ошибка
		            $addvars['success'] = 0;
		            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
		        }
		    } else
		    {
		        $insert = new stdClass();

		        $insert->assignedpersonid = $formdata->assignedpersonid['id'];
		        $insert->purchaserpersonid = $person->id;
		        if ( empty($formdata->title) )
                {
                    $insert->title = $this->dof->get_string('default_task_title','crm');
                } else
                {
                    $insert->title = $formdata->title;
                }
		        $insert->about = $formdata->about['text'];
		        $insert->actualdate = $formdata->actualdate;
		        $insert->deadlinedate = $formdata->deadlinedate;
		        $insert->date = intval(date('U'));

		        if ( $this->dof->storage('tasks')->add_task($insert) )
		        {// Если успешно добавлена
		            $addvars['success'] = 1;
		            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
		        } else
		        {// Если ошибка
		            $addvars['success'] = 0;
		            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
		        }
		    }
		}
    }
}

/**
 * Класс формы делегирования задачи
 */
class block_dof_im_crm_delegatetask_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $parent;

    function definition()
    {
        $this->dof    = $this->_customdata->dof;
        $this->parent = $this->dof->storage('tasks')->get_record(array('id' => $this->_customdata->taskid));
        $departmentid    = $this->_customdata->departmentid;
        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('create_task_title', 'crm'));

        // Id родительской задачи
        $mform->addElement('hidden','parentid');
        $mform->setType('parentid', PARAM_INT);
        $mform->setDefault('parentid', $this->parent->id);

        //Данные для автозаполнения персон
        $options = array();
        $options['plugintype']   = "storage";
        $options['plugincode']   = "persons";
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        $options['departmentid'] = $departmentid;
        $options['querytype'] = "persons_list";

        // Кому делегируем
        $mform->addElement('dof_autocomplete', 'assignedpersonid', $this->dof->get_string('assignperson','crm'),'', $options);
        $mform->setType('assignedpersonid', PARAM_INT);

        // Заголовок
        $mform->addElement('text','title', $this->dof->get_string('task_title','crm'));
        $mform->setType('title', PARAM_TEXT);
        $mform->setDefault(
                'title',
                $this->parent->title.$this->dof->get_string('delegation', 'crm')
        );

        // Описание
        $mform->addElement('editor','about', $this->dof->get_string('about_task','crm'))
            ->setValue( array('text' => $this->parent->about) );


        // Дата актуализации
        $mform->addElement('date_time_selector','actualdate', $this->dof->get_string('actual_date','crm'));
        $mform->setDefault(
                'actualdate',
                $this->parent->actualdate
        );

        // Дедлайн
        $mform->addElement('date_time_selector','deadlinedate', $this->dof->get_string('deadline_date','crm'));
        $mform->setDefault(
                'deadlinedate',
                $this->parent->deadlinedate
        );

        // кнопка "сброс"
        $this->add_action_buttons('true', $this->dof->get_string('create_task', 'crm'));
    }


    function validation($data, $files)
    {
        $errors = array();

        // Если дата дедлайна задачи позже дедлайна родителя
        if ( $data['deadlinedate'] > $this->parent->deadlinedate )
        {
            $errors['deadlinedate'] =
                $this->dof->get_string(
                        'error_deadlinedate_overflow','crm').date('d-m-Y h:i', $this->parent->deadlinedate);
        }
        // Если id пользователя - 0
        if ( $data['assignedpersonid']['id'] == 0 )
        {
            $errors['assignedpersonid'] =
            $this->dof->get_string('error_assignedpersonid_zero','crm');
        }
        // возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Функци для обработки данных из формы создания/редактирования
     *
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
        {
            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
        }
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
        {
            // Получаем персону
            $person = $this->dof->storage('persons')->get_bu();

            // Формируем объект
            $insert = new stdClass();

            $insert->parentid = $this->parent->id;
            $insert->assignedpersonid = $formdata->assignedpersonid['id'];
            $insert->purchaserpersonid = $person->id;
            if ( empty($formdata->title) )
            {
                $insert->title = $this->dof->get_string('default_task_title','crm');
            } else
            {
                $insert->title = $formdata->title;
            }
            $insert->about = $formdata->about['text'];
            $insert->actualdate = $formdata->actualdate;
            $insert->deadlinedate = $formdata->deadlinedate;
            $insert->date = intval(date('U'));

            if ( $this->dof->storage('tasks')->add_task($insert) )
            {// Если успешно добавлена
                $addvars['success'] = 1;
                redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
            } else
            {// Если ошибка
                $addvars['success'] = 0;
                redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
            }

        }
    }
}

/** Класс формы делегирования задачи
 *
 */
class block_dof_im_crm_childrentask_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $parent;

    function definition()
    {
        $this->dof    = $this->_customdata->dof;
        $this->parent = $this->dof->storage('tasks')->get_record(array('id' => $this->_customdata->taskid));

        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('create_task_title', 'crm'));

        // Id родительской задачи
        $mform->addElement('hidden','parentid');
        $mform->setType('parentid', PARAM_INT);
        $mform->setDefault('parentid', $this->parent->id);

        // Заголовок
        $mform->addElement('text','title', $this->dof->get_string('task_title','crm'));
        $mform->setType('title', PARAM_TEXT);
        $mform->setDefault(
                'title',
                $this->parent->title.$this->dof->get_string('childrentask', 'crm')
        );

        // Описание
        $mform->addElement('editor','about', $this->dof->get_string('about_task','crm'))
            ->setValue( array('text' => $this->parent->about) );


        // Дата актуализации
        $mform->addElement('date_time_selector','actualdate', $this->dof->get_string('actual_date','crm'));
        $mform->setDefault(
                'actualdate',
                $this->parent->actualdate
        );

        // Дедлайн
        $mform->addElement('date_time_selector','deadlinedate', $this->dof->get_string('deadline_date','crm'));
        $mform->setDefault(
                'deadlinedate',
                $this->parent->deadlinedate
        );


        // кнопка "сброс"
        $this->add_action_buttons('true', $this->dof->get_string('create_task', 'crm'));
    }

    /**
     * Функци для обработки данных из формы создания/редактирования
     *
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
        {
            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
        }
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
        {
            // Получаем персону
            $person = $this->dof->storage('persons')->get_bu();

            // Формируем объект
            $insert = new stdClass();

            $insert->parentid = $this->parent->id;
            $insert->assignedpersonid = $person->id;
            $insert->purchaserpersonid = $person->id;
            if ( empty($formdata->title) )
            {
                $insert->title = $this->dof->get_string('default_task_title','crm');
            } else
            {
                $insert->title = $formdata->title;
            }
            $insert->about = $formdata->about['text'];
            $insert->actualdate = $formdata->actualdate;
            $insert->deadlinedate = $formdata->deadlinedate;
            $insert->date = intval(date('U'));

            if ( $this->dof->storage('tasks')->add_task($insert) )
            {// Если успешно добавлена
                $addvars['success'] = 1;
                redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
            } else
            {// Если ошибка
                $addvars['success'] = 0;
                redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
            }

        }
    }

    function validation($data, $files)
    {
        $errors = array();

        // Если дата дедлайна задачи позже дедлайна родителя
        if ( $data['deadlinedate'] > $this->parent->deadlinedate )
        {
            $errors['deadlinedate'] =
                $this->dof->get_string(
                        'error_deadlinedate_overflow','crm').date('d-m-Y h:i', $this->parent->deadlinedate);
        }
        // возвращаем ошибки, если они возникли
        return $errors;
    }
}

/**
 * Класс формы добавления решения проблемы
 */
class block_dof_im_crm_task_complete extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        $this->dof    = $this->_customdata->dof;
        $taskid = $this->_customdata->taskid;

        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Id задачи
        $mform->addElement('hidden','taskid');
        $mform->setType('taskid', PARAM_INT);
        $mform->setDefault('taskid', $taskid);

        // Описание
        $mform->addElement('editor','solution', $this->dof->get_string('solution','crm'));

        // кнопка "сброс"
        $this->add_action_buttons('true', $this->dof->get_string('complete_task', 'crm'));
    }

    function validation($data, $files)
    {
        $errors = array();

        // Если дата дедлайна задачи позже дедлайна родителя
        if ( empty ($data['solution']['text']) )
        {
            $errors['solution'] =
            $this->dof->get_string('error_solution_empty','crm');
        }
        // возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Функци для обработки данных из формы
     *
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
        {
            redirect($this->dof->url_im('crm','/tasks/mytasks.php',$addvars));
        }
        if ( $this->is_submitted() AND $formdata = $this->get_data() )
        {
            // Формируем объект
            $update = new stdClass();

            $update->solution = $formdata->solution['text'];
            $update->id = $formdata->taskid;

            $addvars['taskid'] = $update->id;
            if ( $this->dof->storage('tasks')->update($update) &&
                 $this->solved_task($update->id)
            )
            {// Если успешно добавлена
                $addvars['success'] = 1;
                redirect($this->dof->url_im('crm','/tasks/task.php',$addvars));
            } else
            {// Если ошибка
                $addvars['success'] = 0;
                redirect($this->dof->url_im('crm','/tasks/task.php',$addvars));
            }
        }
    }

    /**
     * Каскадно обновить статус Завершено у задачи и ее дочерних элементов
     *
     * @param int $taskid - ID задачи
     *
     * @return bool - true/false в зависимости от результата обновления (успешно/ошибка)
     */
    private function solved_task($taskid)
    {
        // Получим задачу
        $task = $this->dof->storage('tasks')->get($taskid);

        // Если статус не удален и не просрочен, обновляем статус задачи
        if ( ! ( $task->status == 'failed' ||  $task->status == 'deleted' ) )
        {
            // Пытаемся обновить задачу
            if ( ! $this->dof->workflow('tasks')->change($taskid, 'completed') )
            { // При обновлении произошла ошибка, прекращаем задачу
                return false;
            }
        }

        // Получаем все дочерние задачи
        $childrentasks = $this->dof->storage('tasks')->get_records(array('parentid' => $taskid));
        // Если дочерних элементов нет, а родителя мы уже успешно обновили - возвращаем true
        if ( empty($childrentasks) )
        {
            return true;
        }
        // Для каждой из дочерних задач продолжаем каскадное обновление
        foreach ( $childrentasks as $item )
        {
            // Обновляем
            $return = $this->solved_task($item->id);
            // Если статус не обновился - завершаем обновление
            if ( ! $return )
            {
                return false;
            }
        }
        // Каскадное обновление для данного родителя завершилось
        return true;
    }
}
?>