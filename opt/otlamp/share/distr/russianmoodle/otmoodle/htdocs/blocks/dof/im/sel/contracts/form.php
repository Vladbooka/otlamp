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


// Подключаем библиотеки
require_once(dirname(realpath(__FILE__))."/../lib.php");
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();
if ( $DOF->plugin_exists('im', 'persons') )
{
    require_once($DOF->plugin_path('im', 'persons', '/form.php'));
}

/*
 * Форма сохранения договора
 */
class im_sel_contract_save_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * GET-параметры текущей страницы
     *
     * @var array
     */
    protected $addvars = [];

    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = '';

    /**
     * URL для отмены формы
     *
     * @var string
     */
    protected $cancelurl = '';

    /**
     * Имя переменной, которая будет добавлена в returnurl
     * Содержит результат сохранения договора
     *
     * @var string
     */
    protected $idparam = 'contractid';

    /**
     * Возможность ручного указания номера договора
     *
     * @var bool
     */
    protected $createnumber = false;

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Заблокированные элементы
        $freezed = [];

        // Добавление свойств
        $this->dof =& $this->_customdata->dof;
        $this->returnurl = $this->_customdata->returnurl;
        $this->cancelurl = $this->_customdata->cancelurl;
        $this->addvars = $this->_customdata->addvars;
        $contractid = (int)$this->_customdata->contractid;
        if ( isset($this->_customdata->idparam) && ! empty($this->_customdata->idparam) )
        {// Имя параметра переопределено
            $this->idparam = $this->_customdata->idparam;
        }
        if ( isset($this->_customdata->createnumber) AND $this->_customdata->createnumber )
        {// Разрешено ручное указание номера договора
            $this->createnumber = true;
        }

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'contractid', $contractid);
        $mform->setType('contractid', PARAM_INT);

        // Заголовок формы
        $mform->addElement(
            'header',
            'cldheader',
            $this->dof->get_string('cldheader', 'sel')
        );

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );

        // Поле создания номера договора
        $defaultnumber = $this->dof->storage('contracts')->get_default_contractnum($contractid);
        $mform->addElement('text', 'num', $this->dof->get_string('num', 'sel'));
        $mform->setType('num', PARAM_TEXT);
        $mform->setDefault('num', $defaultnumber);
        if ( ! $this->createnumber )
        {// Пользователю недоступно изменение номера договора
            $freezed[] = 'num';
        }

        // Дата заключения договора
        $mform->addElement(
            'date_selector',
            'date',
            $this->dof->get_string('date', 'sel')
        );
        $mform->setType('date', PARAM_INT);

        // Подразделение договора
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null, '0', null, true);
        $permissions = [['plugintype' => 'storage', 'plugincode' => 'departments', 'code' => 'use']];
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        $mform->addElement(
            'select',
            'department',
            $this->dof->get_string('department', 'sel').':',
            $departments
        );
        $mform->setType('department', PARAM_TEXT);
        $mform->addRule('department', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client');

        // Заметка
        $mform->addElement(
            'textarea',
            'notes',
            $this->dof->get_string('notes', 'sel'),
            ['style' => 'width: 100%']
        );

        // Метаконтракт текущего договора
        $ajaxparams = $this->autocomplete_params('metacontracts','client', $contractid);
        $mform->addElement(
            'dof_autocomplete',
            'metacontract',
            $this->dof->get_string('metacontract','sel'),
            [],
            $ajaxparams
        );

        // Выбор учащегося по договору
        $mform->addElement(
            'header',
            'stheader',
            $this->dof->get_string('student', 'sel')
        );
        // Cоздать новую персону
        $mform->addElement(
            'radio',
            'student',
            null,
            $this->dof->get_string('new', 'sel'),
            'new'
        );
        // Использовать персону деканата
        $mform->addElement(
            'radio',
            'student',
            null,
            $this->dof->get_string('personid', 'sel'),
            'personid'
        );
        // Выбор персоны Деканата
        $ajaxparams = $this->autocomplete_params('personid', 'student', $contractid);
        $mform->addElement(
            'dof_autocomplete',
            'st_person_id',
            $this->dof->modlib('ig')->igs('search'),
            [],
            $ajaxparams
        );
        // Использовать пользователя Moodle
        if ( ! $contractid )
        {// Договор создается
            $mform->addElement(
                'radio',
                'student',
                null,
                $this->dof->get_string('mdluser','sel'),
                'mdluser'
            );
            $ajaxparams = $this->autocomplete_params('mdluser', 'student', $contractid);
            $mform->addElement(
                'dof_autocomplete',
                'st_mdluser_id',
                $this->dof->modlib('ig')->igs('search'),
                [],
                $ajaxparams
            );
        }
        // Блокировка при редактировании
        $change = new stdClass();
        $change->studentid = 'change';
        if ( ! $this->dof->workflow('contracts')->is_change($contractid, $change) )
        {// Запрет редактирования ученика
            $freezed[] = 'student';
            $freezed[] = 'st_person_id';
            if ( $mform->elementExists('st_mdluser_id') )
            {
                $freezed[] = 'st_mdluser_id';
            }
        }

        // Данные по законному представителю
        $mform->addElement(
            'header',
            'clheader',
            $this->dof->get_string('specimen', 'sel')
        );
        // Создать новую персону
        $mform->addElement(
            'radio',
            'client',
            null,
            $this->dof->get_string('new','sel'),
            'new'
        );
        // Представитель совпадает с учеником
        $mform->addElement(
            'radio',
            'client',
            null,
            $this->dof->get_string('сoincides_with_student','sel'),
            'student'
        );
        // Выбор персоны Деканата
        $mform->addElement(
            'radio',
            'client',
            null,
            $this->dof->get_string('personid', 'sel'),
            'personid'
        );
        $ajaxparams = $this->autocomplete_params('personid', 'client', $contractid);
        $mform->addElement(
            'dof_autocomplete',
            'cl_person_id',
            $this->dof->modlib('ig')->igs('search'),
            null,
            $ajaxparams
        );

        // Использовать пользователя Moodle
        if ( ! $contractid )
        {// Договор создается
            $mform->addElement(
                'radio',
                'client',
                null,
                $this->dof->get_string('mdluser', 'sel'),
                'mdluser'
            );
            $ajaxparams = $this->autocomplete_params('mdluser', 'client', $contractid);
            $mform->addElement(
                'dof_autocomplete',
                'cl_mdluser_id',
                $this->dof->modlib('ig')->igs('search'),
                null,
                $ajaxparams
            );
        }

        // Данные по куратору
        $mform->addElement(
            'header',
            'clheader',
            $this->dof->get_string('curator', 'sel')
        );

        // Не использовать куратора
        $mform->addElement(
            'checkbox',
            'nocurator',
            null,
            $this->dof->get_string('nocurator', 'sel')
        );

        // Поиск персоны в Деканате
        $ajaxparams = $this->autocomplete_params('personid', 'curator', $contractid);
        $mform->addElement(
            'dof_autocomplete',
            'cur_person_id',
            $this->dof->modlib('ig')->igs('search'),
            null,
            $ajaxparams
        );

        // Блокировка полей
        if ( $contractid )
        {// Редактирование
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'student');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'new');
        }else
        {// Создание договора
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'student');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'student');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'mdluser');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'personid');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'new');
            $mform->disabledIf('st_mdluser_id', 'student', 'eq', 'new');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'mdluser');
            $mform->disabledIf('st_mdluser_id', 'student', 'eq', 'personid');
        }
        $mform->disabledIf('cur_person_id', 'nocurator', 'checked');
        if ( ! empty($freezed) )
        {
            $mform->freeze($freezed);
        }

        $mform->setType('student', PARAM_ALPHANUM);
        $mform->setType('client', PARAM_ALPHANUM);

        // Кнопки действий
        $this->add_action_buttons(true, $this->dof->get_string('continue', 'sel'));

        // Фильтрация всех полей
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверка данных формы
     */
    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Базовые проверки
        if ( ! isset($data['student']) )
        {// Не указан способ добавления ученика
            $errors['student'] = $this->dof->get_string('error_choice', 'sel');
        }
        if ( ! isset($data['client']) )
        {// Не указан способ выбора законного представителя
            $errors['client'] = $this->dof->get_string('error_choice', 'sel');
        }
        if ( ! isset($data['department']) || ! isset($data['contractid']))
        {// Не указаны базовые данные договора
            $errors['department'] = $this->dof->modlib('ig')->igs('form_err_required');
        }

        if ( ! empty($errors) )
        {// Вывод текущего уровня ошибок
            return $errors;
        }

        // Валидация договора
        if ( isset($data['contractid']) && ! empty($data['contractid']) )
        {// Редактирование договора

            // Валидация номера договора
            $contract = $this->dof->storage('contracts')->get($data['contractid']);
            if ( empty($contract) )
            {// Договор не найден
                $errors['hidden'] = $this->dof->get_string('contract_save_error_contract_not_found', 'sel');
            }

            if ( $this->dof->storage('contracts')->is_access('edit', $data['contractid'], null, $data['departmentid']) )
            {// Право на изменение есть

                // Валидация персон по договору
                $errors = $this->validation_update($data, $files);

                if ( isset($data['num']) && ( $data['num'] != $contract->num ) &&
                     $this->dof->storage('contracts')->get_records(['num' => $data['num']]) )
                {// Номер договора не уникален
                    $errors['num'] = $this->dof->get_string('err_num_nounique', 'sel');
                }
                // Ограничение на число договоров в подразделении
                if ( $data['department'] != $contract->departmentid &&
                     ! $this->dof->storage('config')->get_limitobject('contracts', $data['department']) )
                {// Превышен лимит договоров в подразделении
                    $errors['department'] = $this->dof->get_string('limit_message', 'sel');
                }
            } else
            {// Права на изменение нет
                $errors['hidden'] = $this->dof->get_string('contract_save_error_edit_access_denied', 'sel');
            }
        } else
        {// Создание договора
            if ( $this->dof->storage('contracts')->is_access('create', null, null, $data['departmentid']) )
            {// Право на создание есть

                // Валидация персон по договору
                $errors = $this->validation_create($data, $files);

                if ( isset($data['num']) && $this->dof->storage('contracts')->
                    get_records(['num'=>$data['num']]) )
                {// Номер договора не уникален
                    $errors['num'] = $this->dof->get_string('err_num_nounique', 'sel');
                }
                // Ограничение на число договоров в подразделении
                if ( ! $this->dof->storage('config')->get_limitobject('contracts', $data['department']) )
                {// Превышен лимит договоров в подразделении
                    $errors['department'] = $this->dof->get_string('limit_message','sel');
                }
            } else
            {// Права на создание нет
                $errors['hidden'] = $this->dof->get_string('contract_save_error_edit_create_denied', 'sel');
            }
        }

        // Валидация метаконтракта
        if ( isset($data['metacontract']) )
        {
            // Подучение значения метаконтракта
            $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete('metacontract', $data['metacontract']);

            // Проверка в зависимости от действия
            switch ($value['do'])
            {
                // Создание нового метаконтракта
                case 'create' :
                    if ( $this->dof->storage('metacontracts')->is_exists(['num' => $value['name']]) )
                    {// Метаконтракт существует
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract', 'sel');
                    }
                    break;
                // Переименовывание метаконтракта
                case 'rename' :
                    if ( $this->dof->storage('metacontracts')->is_exists(['num' => $value['name']]) )
                    {// Имя уже используется
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract', 'sel');
                    }
                // Выбор метаконтракта
                case 'choose' :
                    if ( ! $this->dof->storage('metacontracts')->is_exists($value['id']) )
                    {// Метаконтракт не найден
                        $errors['metacontract'] = $this->dof->get_string('metacontract_no_exist','sel', $value['id']);
                    } elseif ( ! $this->dof->storage('metacontracts')->is_access('use', $value['id'], null, $data['departmentid']) )
                    {// Нет прав на использование метаконтракта
                        $errors['metacontract'] = $this->dof->get_string('error_use_metacontract', 'sel', $value['id']);
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Проверка данных персон при создании договора
     */
    protected function validation_create($data, $files)
    {
        $errors = [];

        // Выбор ученика
        if ( $data['student'] != 'personid' && $data['student'] != 'mdluser' )
        {// Создание новой персоны
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['student'] = $this->dof->
                    get_string('contract_save_error_student_create_access_denied', 'sel');
            }
        }
        if ( isset($data['st_person_id']['id']) && $data['student'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['st_person_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_person_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $student = $this->dof->storage('persons')->get($data['st_person_id']['id']);
                if ( empty($student) )
                {// Выбранная персона не найдена
                    $errors['st_person_id'] = $this->dof->
                        get_string('contract_save_error_student_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$student->status]) )
                    {// Персона не в актуальном статусе
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                }
            }
        }
        if ( isset($data['st_mdluser_id']['id']) && $data['student'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['st_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $student = $this->dof->storage('persons')->get_by_moodleid($data['st_mdluser_id']['id']);

                if ( empty($student) && $this->dof->storage('persons')->is_access('create') )
                {// Выбранная персона не найдена, но мы можем попытаться создать ее
                    $mdluser = $this->dof->modlib('ama')->user($data['st_mdluser_id']['id'])->get();
                    $studentid = (int)$this->dof->storage('persons')->reg_moodleuser($mdluser);
                    $student = $this->dof->storage('persons')->get($studentid);
                }

                if ( empty($student) )
                {// Выбранная персона не найдена и не создана
                    $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$student->status]) )
                    {// Персона не в актуальном статусе
                        $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                }
            }
        }

        // Проверки выбора законного представителя
        if ( $data['client'] != 'personid' && $data['client'] != 'mdluser' && $data['client'] != 'student' )
        {// Проверка прав на создание новой персоны законного представителя
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['client'] = $this->dof->
                    get_string('contract_save_error_client_create_access_denied', 'sel');
            }
        }
        if ( isset($data['cl_person_id']['id']) && $data['client'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['cl_person_id']['id'] < 1 )
            {// Законный представитель не указан
                $errors['cl_person_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $client = $this->dof->storage('persons')->get($data['cl_person_id']['id']);
                if ( empty($client) )
                {// Выбранная персона не найдена
                    $errors['cl_person_id'] = $this->dof->
                        get_string('contract_save_error_client_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$client->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                }
            }
        }
        if ( isset($data['cl_mdluser_id']['id']) && $data['client'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['cl_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['cl_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $client = $this->dof->storage('persons')->get_by_moodleid($data['cl_mdluser_id']['id']);

                if ( empty($client) && $this->dof->storage('persons')->is_access('create') )
                {// Выбранная персона не найдена, но мы можем попытаться создать ее
                    $mdluser = $this->dof->modlib('ama')->user($data['cl_mdluser_id']['id'])->get();
                    $clientid = (int)$this->dof->storage('persons')->reg_moodleuser($mdluser);
                    $client = $this->dof->storage('persons')->get($clientid);
                }

                if ( empty($client) )
                {// Выбранная персона не найдена
                    $errors['cl_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_client_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$client->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                }
            }
        }
        // Проверки выбора куратора
        if ( ! isset($data['nocurator']) && isset($data['cur_person_id']['id']) )
        {// Выбрана персона из деканата
            if ( $data['cur_person_id']['id'] < 1 )
            {// Куратор не указан
                $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $curator = $this->dof->storage('persons')->get($data['cur_person_id']['id']);
                if ( empty($curator) )
                {// Выбранная персона не найдена
                    $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$curator->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $curator->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Проверка данных формы
     */
    protected function validation_update($data, $files)
    {
        $errors = [];

        // Получение текущего договора
        $contract = $this->dof->storage('contracts')->get($data['contractid']);

        // ПРОВЕРКИ ВЫБРАННОГО УЧЕНИКА
        // Возможность изменять ученика по договору
        $params = new stdClass();
        $params->studentid = 'change';
        $canchangestudent = (bool)$this->dof->workflow('contracts')->is_change($data['contractid'], $params);

        // Выбор ученика
        if ( $data['student'] != 'personid' && $data['student'] != 'mdluser' )
        {// Создание новой персоны
            if ( $canchangestudent )
            {// Можно изменять студента по договору
                if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
                {// Доступ к созданию персоны закрыт
                    $errors['student'] = $this->dof->
                        get_string('contract_save_error_student_create_access_denied', 'sel');
                }
            } else
            {// Нельзя изменять студента по договору
                $errors['hidden'] = $this->dof->
                    get_string('contract_save_error_student_change_denied', 'sel');
            }
        }
        if ( isset($data['st_person_id']['id']) && $data['student'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['st_person_id']['id'] != $contract->studentid )
            {// Студент изменен
                if ( $canchangestudent )
                {// Можно изменять студента по договору
                    if ( $data['st_person_id']['id'] < 1 )
                    {// Студент не указан
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_not_set', 'sel');
                    } else
                    {// Поиск выбранной персоны
                        $student = $this->dof->storage('persons')->get($data['st_person_id']['id']);
                        if ( empty($student) )
                        {// Выбранная персона не найдена
                            $errors['st_person_id'] = $this->dof->
                                get_string('contract_save_error_student_not_found', 'sel');
                        } else
                        {// Проверка доступа к персоне
                            $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                            if ( ! isset($statuses[$student->status]) )
                            {// Персона не в актуальном статусе
                                $errors['st_person_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                            if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                            {// Доступ к использованию персоны закрыт
                                $errors['st_person_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                        }
                    }
                } else
                {// Нельзя изменять студента по договору
                    $errors['hidden'] = $this->dof->
                        get_string('contract_save_error_student_change_denied', 'sel');
                }
            }
        }
        if ( isset($data['st_mdluser_id']['id']) && $data['student'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['st_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $studentid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['st_mdluser_id']['id']);
                if ( $studentid != $contract->studentid )
                {// Изменение студента
                    if ( $canchangestudent )
                    {
                        $student = $this->dof->storage('persons')->get($studentid);
                        if ( empty($student) )
                        {// Выбранная персона не найдена
                            $errors['st_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_student_not_found', 'sel');
                        } else
                        {// Проверка доступа к персоне
                            $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                            if ( ! isset($statuses[$student->status]) )
                            {// Персона не в актуальном статусе
                                $errors['st_mdluser_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                            if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                            {// Доступ к использованию персоны закрыт
                                $errors['st_mdluser_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                        }
                    } else
                    {// Нельзя изменять студента по договору
                        $errors['hidden'] = $this->dof->
                            get_string('contract_save_error_student_change_denied', 'sel');
                    }
                }
            }
        }

        // Проверки выбора законного представителя
        if ( $data['client'] != 'personid' && $data['client'] != 'mdluser' && $data['client'] != 'student' )
        {// Проверка прав на создание новой персоны законного представителя
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['client'] = $this->dof->
                    get_string('contract_save_error_student_create_access_denied', 'sel');
            }
        }
        if ( isset($data['cl_person_id']['id']) && $data['client'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['cl_person_id']['id'] != $contract->clientid )
            {// Представитель изменен
                if ( $data['cl_person_id']['id'] < 1 )
                {// Законный представитель не указан
                    $errors['cl_person_id'] = $this->dof->
                        get_string('contract_save_error_client_not_set', 'sel');
                } else
                {// Поиск выбранной персоны
                    $client = $this->dof->storage('persons')->get($data['cl_person_id']['id']);
                    if ( empty($client) )
                    {// Выбранная персона не найдена
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$client->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cl_person_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cl_person_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                    }
                }
            }
        }
        if ( isset($data['cl_mdluser_id']['id']) && $data['client'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['cl_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['cl_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $clientid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['cl_mdluser_id']['id']);
                if ( $clientid != $contract->clientid )
                {// Представитель изменен
                    $client = $this->dof->storage('persons')->get($clientid);
                    if ( empty($client) )
                    {// Выбранная персона не найдена
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$client->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cl_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cl_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                    }
                }
            }
        }
        // Проверки выбора куратора
        if ( ! isset($data['nocurator']) && isset($data['cur_person_id']['id']) )
        {// Выбрана персона из деканата
            if ( $data['cur_person_id']['id'] != $contract->curatorid )
            {// Куратор изменен
                if ( $data['cur_person_id']['id'] < 1 )
                {// Куратор не указан
                    $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_set', 'sel');
                } else
                {// Поиск выбранной персоны
                    $curator = $this->dof->storage('persons')->get($data['cur_person_id']['id']);
                    if ( empty($curator) )
                    {// Выбранная персона не найдена
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$curator->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $curator->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                        }
                    }
                }
            }

        }

        return $errors;
    }

    /**
     * Получить массив опций для autocomplete-элемента
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       personid - поиск по персонам
     *                       mdluser - поиск по пользователям Moodle
     *                       metacontracts - метаконтракты
     * @param string $side - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблице contracts (если договор редактируется)
     * @return array - массив опций
     */
    protected function autocomplete_params($type, $side, $contractid)
    {
        $options = array();
        $options['plugintype']   = "storage";
        $options['plugincode']   = "persons";
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        $options['departmentid'] = $this->addvars['departmentid'];

        //получаем контракт
        $contract = $this->dof->storage('contracts')->get($contractid);
        //тип данных для автопоиска
        switch ($type)
        {
            //id персоны
            case 'personid':
                $options['querytype'] = "persons_list";

                $personid = 0;
                if ( ! $contractid )
                {// договор создается - значение по умолчанию не устанавливае
                    return $options;
                }else
                {
                    $column = $side.'id';
                    $personid = $contract->$column;
                }
                // если договор редактируется - установим в autocomplete значение по умолчани
                if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
                {// не получили договор - не можем установить значение по умолчанию
                    // id есть, а договора нет - нестандартная ситуация, сообщим об этом разработчикам
                    dof_debugging('autocomplete_params() cannot find contract by $contractid',
                            DEBUG_DEVELOPER);
                    return $options;
                }

                // законный представитель совпадает с учеником
                if ( ($contract->studentid == $contract->clientid) AND ($side == 'client') )
                {
                    // не ставим значение по умолчанию
                    return $options;
                }

                // не получили персону по id
                if ( ! $person = $this->dof->storage('persons')->get($personid) AND ($side != 'curator'))
                { // ошибка, но поле с куратором допускается пустое
                    dof_debugging('autocomplete_params() cannot find person by $personid',
                            DEBUG_DEVELOPER);
                    // возвращаем опции, т.к. значение по умолчанию уже не сможем получить
                    return $options;
                }

                // нашли персону - установим ее как значение по умолчанию
                $default = array($personid => $this->dof->storage('persons')->get_fullname($person));
                $options['default'] = $default;

                break;
            //пользователь в moodle
            case 'mdluser':
                $options['querytype'] = "mdluser_list";

                break;
            //метаконтракты
            case 'metacontracts':
                $options['querytype'] = "metacontracts_list";
                $options['plugincode'] = "metacontracts";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                //если не удалось получить контракт
                if ($contract === false)
                {
                    return $options;
                }
                // получили метаконтракт
                if (!empty($contract->metacontractid))
                {//подставляем по умолчанию
                    $options['extoptions']->empty = true;
                    $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                    $options['default'] = array($contract->metacontractid =>
                            $metacontract->num.' ['.$metacontract->id.']');
                }

                break;
        }

        return $options;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        // Отмена формы
        if ( $this->is_cancelled() )
        {
            // Редирект на страницу отмены
            redirect($this->cancelurl);
        }

        // Отправка формы
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы

            // Сохранение договора
            $contract = new stdClass();

            // Вызов обработчика метаконтракта
            $contract->metacontractid = $this->dof->storage('metacontracts')
                ->handle_metacontract($formdata->metacontract, $formdata->department);

            if ( isset($formdata->contractid) && ! empty($formdata->contractid) )
            {// Договор редактируется
                $contract->id = (int)$formdata->contractid;
            } else
            {// Договор создается
                $seller = $this->dof->storage('persons')->get_bu(null, true);
                if ( empty($seller) )
                {
                    $contract->sellerid = null;
                } else
                {
                    $contract->sellerid = $seller->id;
                }
            }

            // Получение ученика
            switch ($formdata->student)
            {
                // Персона выбрана
                case 'personid':
                    $contract->studentid = $formdata->st_person_id['id'];
                    break;
                // Персона выбрана через пользователя Moodle
                case 'mdluser':
                    $contract->studentid = $this->dof->storage('persons')->
                        get_by_moodleid_id($formdata->st_mdluser_id['id']);
                    break;
                // Новая персона
                case 'new':
                    $contract->studentid = 0;
                    break;
                // Нет ученика
                default :
                    $contract->studentid = 0;
                    break;
            }
            // Получение представителя
            switch ($formdata->client)
            {
                case 'new':
                    $contract->clientid = 0;
                    break;
                // Представитель является учеником
                case 'student':
                    if ( $contract->studentid )
                    {// Студент определен
                        $contract->clientid = $contract->studentid;
                    } else
                    {// Представитель связан со студентом
                        $contract->clientid = null;
                    }
                    break;
                // Персона выбрана
                case 'personid':
                    $contract->clientid = $formdata->cl_person_id['id'];
                    break;
                // Персона выбрана через пользователя Moodle
                case 'mdluser':
                    $contract->clientid = $this->dof->storage('persons')->
                        get_by_moodleid_id($formdata->st_mdluser_id['id']);
                    break;
                // Нет законного представителя
                default :
                    $contract->clientid = null;
            }
            if( ! isset($formdata->nocurator) )
            {// Установка куратора
                $contract->curatorid = $formdata->cur_person_id['id'];
            } else
            {// Куратор не установлен
                $contract->curatorid = null;
            }

            $contract->departmentid = $formdata->department;
            $contract->notes        = $formdata->notes;
            $contract->date         = $formdata->date + 3600*12;
            if ( $this->createnumber && isset($formdata->num) AND ! empty($formdata->num) )
            {// Можно изменять номер договора
                $contract->num = $formdata->num;
            }

            try {
                $contractid = $this->dof->storage('contracts')->save($contract);

                $url = $this->get_returnurl($contractid);
                redirect($url);
            } catch ( dof_exception_dml $e )
            {// Ошибка сохранения
                $this->dof->messages->add(
                    $this->dof->get_string($e->errorcode, 'contracts', null, 'storage'),
                    'error'
                );
            }
        }
    }

    /**
     * Получить URL для возврата после обработки
     */
    protected function get_returnurl($contractid)
    {
        // Формирование URL для редиректа
        $parsedurl = parse_url($this->returnurl);

        // Массив GET-параметров для URL возврата
        $query = [];
        if ( isset($parsedurl['query']) && ! empty($parsedurl['query']) )
        {// В URL возврата указаны GET-параметры
            $parsedquery = explode('&', $parsedurl['query']);
            foreach ( $parsedquery as $parameter )
            {// Формирование GET-массива
                $parameter = explode('=', $parameter);
                if ( isset($parameter[0]) && isset($parameter[1]) )
                {// Валидный параметр
                    // Очистка от возможного параметра-массива
                    $parameter[0] = str_replace('[]', '', $parameter[0]);
                    if ( ! isset($query[$parameter[0]]) )
                    {// Добавление значения
                        $query[$parameter[0]] = $parameter[1];
                    } else
                    {// Параметр уже найден среди имеющихся. Формирование массива значений
                        $query[$parameter[0]] = (array)$query[$parameter[0]];
                        $query[$parameter[0]][] = $parameter[1];
                    }
                }
            }
        }

        // Добавление результатов обработки формы
        $query[$this->idparam] = $contractid;

        // Формирование результирующего URL
        $resultquery = [];
        foreach ( $query as $name => $value )
        {
            if ( is_array($value) )
            {
                foreach ( $value as $element )
                {
                    $resultquery[] = $name.'[]='.$element;
                }
            } else
            {
                $resultquery[] = $name.'='.$value;
            }
        }
        $query = implode('&', $resultquery);
        $url = $parsedurl['path'].'?'.$query;

        return $url;
    }
}


/*
 * Класс формы для ввода данных договора (вторая страничка)
 */
class sel_contract_form_two_page extends dof_im_persons_edit_form
{

    /**
     * Инициализация базовых данных формы
     */
    protected function init()
    {
        // Основной процесс инициализации
        parent::init();

        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->contractid = $this->_customdata->contractid;
        $this->contract = $this->dof->storage('contracts')->get($this->contractid);

        // Скрытые поля
        $mform->addElement('hidden', 'contractid', $this->contractid);
        $mform->setType('contractid', PARAM_INT);
    }

    /**
     * Добавление дополнительных полей для персон
     *
     * @see dof_im_persons_edit_form::add_person_fields()
     */
    protected function add_person_fields($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        if ( $personcode == 'clientid' )
        {// Добавление полей для законного представителя
            // Организация
            $ajaxparams = $this->autocomplete_params(
                'organizations',
                'client',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'clorganization',
                $this->dof->get_string('organization','sel'),
                null,
                $ajaxparams
            );
            // Должность
            $ajaxparams = $this->autocomplete_params(
                'workplaces',
                'client',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'clworkplace',
                $this->dof->get_string('workplace','sel'),
                null,
                $ajaxparams
            );
        }

        if ( $personcode == 'studentid' )
        {// Добавление полей для ученика
            // Организация
            $ajaxparams = $this->autocomplete_params(
                'organizations',
                'student',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'storganization',
                $this->dof->get_string('organization','sel'),
                null,
                $ajaxparams
            );
            // Должность
            $ajaxparams = $this->autocomplete_params(
                'workplaces',
                'student',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'stworkplace',
                $this->dof->get_string('workplace','sel'),
                null,
                $ajaxparams
            );
        }
    }

    /**
     * Дополнительные поля формы
     *
     * @see dof_im_persons_edit_form::add_fields_after_persons()
     */
    protected function add_fields_after_persons()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        if ( $this->_customdata->countsbc == false )
        {// если подписок нет или она одна
            $mform->addElement('hidden', 'programmsbcid', 0);
            $mform->setType('programmsbcid', PARAM_INT);
            //создаем или редактируем подписку на программу
            $mform->addElement('header','header', $this->dof->get_string('create_programmsbc', 'sel'));
            $mform->addElement('checkbox', 'programmsbc',null, $this->dof->get_string('create_programmsbc', 'sel'));
            $options = $this->get_select_options();
            // при помощи css делаем так, чтобы надписи в форме совпадали с элементами select
            $mform->addElement('html', '<div style=" line-height: 1.9; ">');
            // добавляем новый элемент выбора зависимых вариантов форму
            $myselect =& $mform->addElement('dof_hierselect', 'prog_and_agroup',
                                            $this->dof->get_string('programm', 'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agenum',   'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agroup',   'programmsbcs').':',
                                            null,'<div class="col-12 px-0"></div>');
            // закрываем тег выравнивания строк
            $mform->addElement('html', '</div>');
            // устанавливаем для него варианты ответа
            // (значения по умолчанию устанавливаются в методе definition_after_data)
            $myselect->setOptions(array($options->programms, $options->agenums, $options->agroups ));
            $mform->disabledIf('prog_and_agroup', 'programmsbc');
            // получаем все возможные формы обучения
            $eduforms = $this->get_eduforms_list();
            // создаем меню выбора формы обучения
            $mform->addElement('select', 'eduform', $this->dof->get_string('eduform', 'sel'), $eduforms);
            $mform->disabledIf('eduform', 'programmsbc');
            $mform->setType('eduform', PARAM_TEXT);
            // получаем все возможные типы обучения
            $edutypes = $this->get_edutypes_list();
            // создаем меню выбора типа обучения
            $mform->addElement('select', 'edutype', $this->dof->get_string('edutype', 'sel'), $edutypes);
            $mform->disabledIf('edutype', 'programmsbc');
            $mform->setType('edutype', PARAM_TEXT);
            $mform->setDefault('edutype','group');
            // свободное посещение
            $mform->addElement('selectyesno', 'freeattendance', $this->dof->get_string('freeattendance', 'sel'));
            $mform->disabledIf('freeattendance', 'programmsbc');
            $mform->setType('freeattendance', PARAM_INT);
            $ages = $this->get_list_ages();
            $mform->addElement('select', 'agestart', $this->dof->get_string('agestart', 'sel'), $ages);
            $mform->disabledIf('agestart', 'programmsbc');
            $mform->setType('agestart', PARAM_INT);
            $options = array();
            $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
            $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
            $options['optional']  = false;
            $mform->addElement('date_selector', 'datestart', $this->dof->get_string('datestart', 'sel'), $options);
            $mform->disabledIf('datestart', 'programmsbc');
            //$mform->setType('datestart', PARAM_INT);
            // поправочный зарплатный коэффициент
            $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor','sel').':', 'size="10"');
            $mform->setType('salfactor', PARAM_TEXT);
            $mform->setDefault('salfactor', '0.00');
        }else
        {// если их много - создаем ссылки на подписки
            $mform->addElement('header','header', $this->dof->get_string('programmsbcs', 'sel'));
            $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records(['contractid' => $this->_customdata->contractid]);
            foreach ( $programmsbcs as $sbc )
            {
                $mform->addElement('html', '&nbsp;&nbsp;&nbsp;<a href='.
                       $this->dof->url_im('programmsbcs','/edit.php?programmsbcid='.$sbc->id).'>'.
                       $this->dof->get_string('view_programmsbcs', 'sel', $this->get_programm_name($sbc->programmid)).
                       '</a><br>');
            }
        }
    }

    /**
     * Валидация формы
     */
    public function validation($data, $files)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        $errors = parent::validation($data, $files);

        $reqfield = [];

        // Валидация подписки на программу
        if ( isset($data['programmsbc']) AND ($data['programmsbc'] == 1) )
        {// если создается подписка
            // проверим существование программы
            if ( ! isset($data['prog_and_agroup'][0]) OR
                ! $this->dof->storage('programms')->is_exists($data['prog_and_agroup'][0]) )
            {// такая программа не существует
                $errors['prog_and_agroup'] = $this->dof->get_string('err_required','sel');
            }elseif ( ! isset($data['prog_and_agroup'][2]) AND $data['prog_and_agroup'][2] )
            {// проверяем существование группы
                if ( ! $agroup = $this->dof->storage('agroups')->get($data['prog_and_agroup'][2]) )
                {// если она указана, но ее id не найден - то это ошибка
                    $errors['prog_and_agroup'] = $this->dof->get_string('err_required','sel');
                }elseif ( $agroup->programmid <> $data['prog_and_agroup'][0] )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agroup','sel');
                }elseif ( $agroup->agenum <> $data['prog_and_agroup'][1] AND $agroup->status <> 'plan' )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agenum','sel');
                }
            }
        }

        // Валидация дополнительных полей студента
        //проверим, существует ли в форме автокомплит для организаций и передано ли в поле число
        if ( isset($data['storganization']['storganization']) AND
            preg_match("/^[0-9]+$/", $data['storganization']['storganization']) )
        {
            $checkid = $data['storganization']['storganization'];
            //проверим, существует ли такая организация
            if ( !$this->dof->storage('organizations')->is_exists($checkid) )
            {// такой организации не существует
                $errors['storganization'] = $this->dof->get_string('org_no_exist','sel');
            }elseif ( ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// нельзя использовать данную организацию
                $errors['storganization'] = $this->dof->get_string('error_use_org','sel',$checkid);
            }
        } elseif ( isset($data['storganization']['storganization']) )
        {
            if ( $checkid = $this->dof->storage('organizations')->get_field($data['storganization']['storganization'],'id') AND
                ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// такая организация уже существует и ее нельзя использовать
                $errors['storganization'] = $this->dof->get_string('error_use_exists_org','sel',$checkid);
            }
        } elseif ( ! empty($data['storganization']['id']) )
        {// передано id - проверим на использование
            if ( ! $this->dof->storage('organizations')->is_access('use',$data['storganization']['id']) )
            {
                $errors['storganization'] = $this->dof->get_string('error_use_org','sel',$data['storganization']['id']);
            }
        }

        // Валидация дополнительных полей законного представителя
        //проверим, существует ли в форме автокомплит для организаций и передано ли в поле число
        if ( isset($data['clorganization']['clorganization']) AND
            preg_match("/^[0-9]+$/", $data['clorganization']['clorganization']) )
        {
            $checkid = $data['clorganization']['clorganization'];
            //проверим, существует ли такая организация
            if ( !$this->dof->storage('organizations')->is_exists($checkid) )
            {// такой организации не существует
                $errors['clorganization'] = $this->dof->get_string('org_no_exist','sel');
            }elseif ( ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// нельзя использовать данную организацию
                $errors['clorganization'] = $this->dof->get_string('error_use_org','sel',$checkid);
            }
        }elseif ( isset($data['clorganization']['clorganization']) )
        {
            if ( $checkid = $this->dof->storage('organizations')->get_field($data['clorganization']['clorganization'],'id') AND
                ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// такая организация уже существует и ее нельзя использовать
                $errors['clorganization'] = $this->dof->get_string('error_use_exists_org','sel',$checkid);
            }
        }elseif ( ! empty($data['clorganization']['id']) )
        {// передано id - проверим на использование
            if ( ! $this->dof->storage('organizations')->is_access('use',$data['clorganization']['id']) )
            {
                $errors['clorganization'] = $this->dof->get_string('error_use_org','sel',$data['clorganization']['id']);
            }
        }

        return $errors;
    }

    /**
     * Дополнительный обработчик формы
     *
     * {@inheritDoc}
     * @see dof_im_persons_edit_form::process_after_persons()
     */
    protected function process_after_persons($formdata)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Базовые данные для обработчика
        $studentid = $this->persons['added']['studentid'];

        $clientid = 0;
        if ( isset($this->persons['added']['clientid']) )
        {
            $clientid = $this->persons['added']['clientid'];
        }

        // Должность студента
        if ( empty($formdata->stworkplace['stworkplace']) )
        {// Должность студента не указана
            $formdata->stworkplace['stworkplace'] = $this->dof->get_string('empty_workplace', 'sel');
        }
        // Организация студента
        if ( ! empty($formdata->storganization['storganization']) )
        {
            // Добавление новой организации
            $orgid = $this->dof->storage('organizations')->
                handle_organization('storganization', $formdata->storganization);

            if ( ! empty($orgid) )
            {// Организация создана
                // Привязка метаконтракта к созданной организации
                $obj = new stdClass();
                $obj->organizationid = $orgid;
                $this->dof->storage('metacontracts')->update($obj, $this->contract->metacontractid);
                // Привязка должности студента к организации и метаконтракту
                $this->dof->storage('workplaces')->handle_workplace('stworkplace', $formdata->stworkplace, $studentid, $orgid);
            }
        } else
        {// Установка organizationid = 0 и должность "Не указана"
            $this->dof->storage('workplaces')->handle_workplace('stworkplace', $formdata->stworkplace, $studentid);
        }

        // Обработчик данных законного представителя
        if ( $studentid <> $clientid && $clientid > 0 )
        {
            // Должность законного представителя
            if ( empty($formdata->stworkplace['clworkplace']) )
            {// Должность студента не указана
                $formdata->stworkplace['clworkplace'] = $this->dof->get_string('empty_workplace', 'sel');
            }

            // Организация Законного представителя
            if ( ! empty($formdata->clorganization['clorganization']) )
            {
                // Добавление новой организации
                $orgid = $this->dof->storage('organizations')->
                    handle_organization('clorganization', $formdata->clorganization);

                if ( ! empty($orgid) )
                {// Организация создана
                    // Привязка метаконтракта к созданной организации
                    $obj = new stdClass();
                    $obj->organizationid = $orgid;
                    $this->dof->storage('metacontracts')->update($obj, $this->contract->metacontractid);
                    // Привязка должности студента к организации и метаконтракту
                    $this->dof->storage('workplaces')->handle_workplace('clworkplace', $formdata->stworkplace, $clientid, $orgid);
                }
            } else
            {// Установка organizationid = 0 и должность "Не указана"
                $this->dof->storage('workplaces')->handle_workplace('clworkplace', $formdata->stworkplace, $clientid);
            }
        }

        // Обновление договора
        $contract = new stdClass();
        $contract->id = $this->contractid;
        $contract->studentid = $studentid;
        if ( $clientid )
        {
            $contract->clientid = $clientid;
        } else
        {
            $contract->clientid = $studentid;
        }

        // Сохранение договора
        try {
            $this->dof->storage('contracts')->save($contract);
        } catch ( dof_exception_dml $e )
        {// Ошибка сохранения
            $this->errors[] = $this->dof->
                get_string('error_save', 'sel', $this->dof->get_string('m_contract', 'sel')
            );
            return;
        }

        // Обработка подписки на программу
        if ( isset( $formdata->programmsbc ) )
        {
            // Сохранение подписки
            $sbc = new stdClass();
            $sbc->id = $formdata->programmsbcid;
            $sbc->contractid = $this->contractid;
            $sbc->programmid = $formdata->prog_and_agroup[0]; // id программы
            $sbc->agenum = $formdata->prog_and_agroup[1]; //парраллель

            if ( isset($formdata->prog_and_agroup[2]) AND ($formdata->prog_and_agroup[2] <> 0) )
            {// и если указана группа - сохраняем группу
                $sbc->agroupid = $formdata->prog_and_agroup[2]; // id группы
            } else
            {// иначе - индивидуальный
                $sbc->agroupid = null;
            }

            $sbc->edutype = $formdata->edutype; // тип обучения
            $sbc->eduform = $formdata->eduform; // форма обучения
            $sbc->freeattendance = $formdata->freeattendance; // свободное посещение
            $sbc->datestart = $formdata->datestart;
            $sbc->salfactor = $formdata->salfactor;

            // сохраним подписку
            if ( ! $sbc->departmentid = $this->dof->storage('contracts')->get_field($sbc->contractid, 'departmentid') )
            {// Не удалось получить ID подразделения
                $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs', 'sel');
            } elseif ( $this->dof->storage('programmsbcs')->
                        is_programmsbc( $sbc->contractid, $sbc->programmid, $sbc->agroupid, $sbc->datestart, $sbc->id) )
            {// если такая подписка уже существует - сохранять нельзя
                $this->errors[] = $this->dof->get_string('programmsbc_exists', 'sel');
            } else
            {//можно сохранять

                if ( !empty($sbc->id) )
                {// подписка на курс редактировалась - обновим запись в БД
                    if ( ! $this->dof->storage('programmsbcs')->update($sbc, $sbc->id) )
                    {// не удалось произвести редактирование - выводим ошибку
                        $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs','sel');
                    }
                    if ( $history = $this->dof->storage('learninghistory')->get_first_learning_data($sbc->id) )
                    {
                        $this->dof->storage('learninghistory')->delete($history->id);
                    }
                    if ( $formdata->agestart )
                    {
                        //имитируем cpassed
                        $contract = new stdClass();
                        $contract->programmsbcid = $sbc->id;
                        $contract->ageid         = $formdata->agestart;
                        $contract->status        = 'active';
                        $this->dof->storage('learninghistory')->add($contract);
                    }
                } else
                {// подписка на курс создавалась
                    // сохраняем запись в БД
                    $sbc->status = 'application';
                    if( $id = $this->dof->storage('programmsbcs')->sign($sbc) )
                    {// все в порядке - сохраняем статус и возвращаем на страниу просмотра подписки
                        $this->dof->workflow('programmsbcs')->init($id);
                        //имитируем cpassed
                        $contract = new stdClass();
                        $contract->programmsbcid = $id;
                        $contract->ageid         = $formdata->agestart;
                        $contract->status        = 'active';
                        $this->dof->storage('learninghistory')->add($contract);
                    }else
                    {// подписка на курс выбрана неверно - сообщаем об ошибке
                        $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs','sel');
                    }
                }
            }
        }
    }

    /** Получить весь список опций для элемента hierselect
     * @todo переделать эту функцию в рекурсивную процедуру, чтобы сократить объем кода
     * @return stdClass object объект, содержащий данные для элемента hierselect
     */
    private function get_select_options()
    {
        $result = new stdClass();
        // получаем список всех учеников
        $programms = $this->get_list_programms();
        // создаем массив для учебных программ
        $agroups  = array();
        // создаем массив для параллелей
        $agenums  = array();
        foreach ( $programms as $progid=>$programm )
        {// для каждой программы составим список возможных академических групп,
            // и тем самым создадим иерархию второго уровня
            $agenums[$progid] = $this->get_list_agenums($progid);
            foreach ($agenums[$progid] as $num=>$agenum)
            {
                $agroups[$progid][$num] = $this->get_list_agroups($progid, $num);
            }
        }
        // записываем в результурующий объект все что мы получили
        $result->programms = $programms;
        $result->agroups   = $agroups;
        $result->agenums   = $agenums;

        // возвращаем все составленные массивы в упорядоченном виде
        return $result;
    }

    /** Внутренняя функция. Получить параметры для autocomplete-элемента
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       personid - поиск по персонам
     *                       mdluser - поиск по пользователям Moodle
     * @param string $side - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблице contracts (если договор редактируется)
     *
     * @return array
     */
    private function autocomplete_params($type, $side, $contractid)
    {
        $options = array();
        $options['plugintype'] = "storage";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';

        //получаем контракт
        $contract = $this->dof->storage('contracts')->get($contractid);

        // определяем, для какого поля получать значение (ученик или законный представитель)
        $personid = 0;
        if ($contract !== false)
        {
            $column = $side.'id';
            $personid = $contract->$column;
        }

        //тип данных для автопоиска
        switch ($type)
        {
            //организация
            case 'organizations':
                $options['plugincode'] = "organizations";
                $options['querytype'] = "organizations_list";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                $organizationid = $this->dof->storage('workplaces')
                ->get_field(array('personid' => $personid,'statuswork' => 'active'),'organizationid');

                if (!empty($organizationid))
                {
                    $organization = $this->dof->storage('organizations')->get($organizationid,'id,shortname');
                    // получили организацию
                    if (!empty($organization->metacontractid))
                    {//подставляем по умолчанию
                        $options['extoptions']->empty = true;
                        $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                        $options['default'] = array($organizationid => $organization->shortname);
                    }

                }


                break;

                //должность
            case 'workplaces':
                $options['plugincode'] = "workplaces";
                $options['querytype'] = "workplaces_list";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                $workplaceid = $this->dof->storage('workplaces')
                ->get_field(array('personid' => $personid, 'statuswork' => 'active'),'id');

                if (!empty($workplaceid))
                {
                    $workplace = $this->dof->storage('workplaces')->get($workplaceid, 'post');
                    if (!empty($workplace->metacontractid))
                    {//подставляем по умолчанию
                        $options['extoptions']->empty = true;
                        $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                        $options['default'] = array($workplaceid => $workplace->post);
                    }

                }

                break;
        }

        return $options;
    }

    /** Получить список всех возможных программ обучения
     * @return array массив вариантов для элемента hierselect
     */
    private function get_list_programms()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programms')->
            get_records(array('status'=>array('available')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);

        return $result;
    }

    /** Получить список академических групп
     *
     * @return array
     */
    private function get_list_agroups($programmid, $agenum)
    {
        $result = array();
        // добавляем первый вариант со словом "Индивидуально"
        $result[0] = $this->dof->get_string('no','programmsbcs');
        // получаем все программы
        $agroups = $this->dof->storage('agroups')->get_records(array('programmid'=>$programmid));
        if ( $agroups )
        {// если группы извлеклись - то добавим их в массив
            foreach ( $agroups as $id=>$agroup )
            {// составляем массив нужной для select-элемента структуры
                if ( $agroup->agenum == $agenum OR $agroup->status == 'plan')
                {
                    $result[$id] = $agroup->name.' ['.$agroup->code.']';
                }
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);

        return $result;
    }

    /** Получить список доступных учебных периодов для этой программы
     *
     * @return array массив элементов для hierselect
     * @param int $programmid - id учебной программы из таблицы programms
     */
    private function get_list_agenums($programmid)
    {
        $result = array();
        // добавляем первый вариант со словом "Индивидуально"
        $result[0] = $this->dof->get_string('no','programmsbcs');
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {// переданная учебная программа не найдена
            return $result;
        }
        // заполняем массив данными
        for ( $i=1; $i<=$programm->agenums; $i++ )
        {
            $result[$i] = $i.' '; // пустой пробел в конце обязателен
        }

        return $result;
    }
    /** Возвращает массив периодов
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages()
    {
        $rez = $this->dof->storage('ages')->get_records(array('status'=>array('plan',
                                                                            'createstreams',
                                                                            'createsbc',
                                                                            'createschelude',
                                                                            'active')));
        $rez = $this->dof_get_select_values($rez);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);

        return $rez;
    }
    /** Получить список всех возможных форм обучения для элемента select
     *
     * @return array
     */
    private function get_eduforms_list()
    {
        return $this->dof->storage('programmsbcs')->get_eduforms_list();
    }

    /** Получить список всех возможных типов обучения для элемента select
     *
     * @return array
     */
    private function get_edutypes_list()
    {
        return $this->dof->storage('programmsbcs')->get_edutypes_list();
    }

    /** Получить название программы
     * @param int $programmid - id программы
     * @return string
     */
    private function get_programm_name($programmid)
    {
        if ( ! $programmname = $this->dof->storage('programms')->get_field($programmid, 'name') )
        {//программа не указана - выведем пустую строчку
            $programmname = '&nbsp;';
        }
        if ( ! $programmcode = $this->dof->storage('programms')->get_field($programmid, 'code') )
        {//код программы не указан - выведем пустую строчку
            $programmcode = '&nbsp;';
        }
        if ( ($programmname <> '&nbsp;') OR ($programmcode <> '&nbsp;') )
        {// если код группы или имя были найдены - выведем их вместе
            $programm = $programmname.' ['.$programmcode.']';
        }else
        {// не найдены - пустую строчку
            $programm = '&nbsp;';
        }
        return $programm;
    }
}

/**
 * Панель управления договорами
 */
class sel_listeditor_form extends dof_modlib_widgets_form
{
    /**
     * Список договоров
     *
     * @var array
     */
    protected $contracts = [];

    /**
     * GET параметры для ссылки
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
     * @var dof_control
     */
    protected $dof;

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->addvars = $this->_customdata->addvars;
        $this->contracts = $this->_customdata->contracts;
        $this->dof = $this->_customdata->dof;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }

        // Установка HTML-атрибутов формы
        $formattrs = $mform->getAttributes();
        $formattrs['class'] = $formattrs['class']." sel-listeditor";
        $formattrs['id'] = "form_sel_list_editor";
        $mform->setAttributes($formattrs);

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);

        // Формирование таблицы договоров
        if ( empty($this->contracts) )
        {// Договоров не найдено
            $mform->addElement('html', '<p align="center">(<i>' . $this->dof->get_string('no_contracts_found', 'sel') . '</i>)</p>');
        } else
        {// Договоры указаны

            // Генерация таблицы договоров
            $this->contracts_table();

            // Поле для вывода сообщений об ошибках скрытых элементов
            $mform->addElement(
                'static',
                'form_listeditor_hidden',
                ''
            );

            // Список действий над договорами
            $actions = [
                '' => $this->dof->get_string('form_listeditor_massactions_select_action', 'sel')
            ];
            // Список опций массовых действий над договорами
            $actionoptions = [];
            $actionoptions[''][''] = $this->dof->get_string('form_listeditor_massactions_select_option', 'sel');

            // Действие смены статуса договоров
            $contractstatuses = $this->dof->workflow('contracts')->get_list();

            $availablestatuses = [];
            foreach ( $contractstatuses as $contractstatus => $name )
            {
                $access = $this->dof->workflow('contracts')->
                    is_access('changestatus:to:'.$contractstatus, null, null, $this->addvars['departmentid']);
                if ( $access )
                {// Перевод в указанный статус доступен
                    $availablestatuses[$contractstatus] = $name;
                }
            }

            if ( ! empty($availablestatuses) )
            {// Список статусов получен

                // Заполнение списка массовых операций
                $actions['contracts_status'] = $this->dof->get_string('form_listeditor_massactions_change_status', 'sel');
                $actionoptions['contracts_status'][''] = $this->dof->
                    get_string('form_listeditor_massactions_select_option_status', 'sel');
                $actionoptions['contracts_status'] = $actionoptions['contracts_status'] + $availablestatuses;
            }

            // Действие смены подразделения
            if ( $this->dof->storage('contracts')->is_access('edit', null, null, $this->addvars['departmentid']) )
            {// Доступ к изменению договоров дан

                // Определение колбэка для дополнитеьной проверки прав
                $selectoptions['access_callback'] = function($departmentid)
                {
                    return $this->dof->storage('contracts')->
                        is_access('create', null, null, $departmentid);
                };
                // Получение списка подразделений
                $departments = $this->dof->im('departments')->get_departments_select_options(0, $selectoptions);

                // Сортировка по кодам
                $departmentscodes = [];
                foreach ( $departments as $departmentid => $departmentname )
                {
                    if ( is_int($departmentid) )
                    {
                        $department = $this->dof->storage('departments')->get($departmentid);
                        if ( $department )
                        {
                            $departmentscodes[$department->code] = $department;
                        }
                    }
                }
                ksort($departmentscodes);
                $departments = [];
                foreach ( $departmentscodes as $department )
                {
                    $departments[(string)$department->id] = '['.$department->code.'] '.$department->name;
                }

                if ( ! empty($departments) )
                {// Список подразделений получен
                    $actions['contracts_department'] = $this->dof->get_string('form_listeditor_massactions_change_department', 'sel');
                    $actionoptions['contracts_department']['0'] = $this->dof->
                        get_string('form_listeditor_massactions_select_option_department', 'sel');
                    $actionoptions['contracts_department'] = $actionoptions['contracts_department'] + $departments;
                }
            }

            // Список дополнительных опций массовых действий над договорами
            $additionaloptions = $actionoptions;
            foreach ( $additionaloptions as $action => &$options )
            {
                // Добавление дополнительных опций к каждому из вариантов
                foreach ( $options as $option => &$string )
                {
                    $string = [];
                    switch ( $action )
                    {
                        case 'contracts_department' :
                            $string[''] =  $this->dof->
                                get_string('form_listeditor_massactions_select_additionaloptions_contracts_department_none', 'sel');
                            $string['person'] =  $this->dof->
                                get_string('form_listeditor_massactions_select_additionaloptions_contracts_department_person', 'sel');
                            $string['programmsbcs'] =  $this->dof->
                                get_string('form_listeditor_massactions_select_additionaloptions_contracts_department_programmsbcs', 'sel');
                            $string['personandprogrammsbcs'] =  $this->dof->
                                get_string('form_listeditor_massactions_select_additionaloptions_contracts_department_personandprogrammsbcs', 'sel');
                            break;
                        default:
                            $string = ['' => $this->dof->get_string('form_listeditor_massactions_select_additionaloptions', 'sel')];
                            break;
                    }
                }
            }

            // Массовые действия на договорами
            $select = $mform->addElement(
                'dof_hierselect',
                'form_listeditor_massactions',
                $this->dof->get_string('form_listeditor_massactions_title', 'sel'),
                '',
                null,
                '<div class="col-12 px-0"></div>'
            );
            // Установка набора значений
            $select->setOptions([$actions, $actionoptions, $additionaloptions]);

            // Кнопка подтверждения
            $mform->addElement(
                'dof_confirm_submit', 'form_listeditor_submit', 'confirm_submit',
                [
                    'modalbuttonname' => $this->dof->get_string('form_listeditor_modalbuttonname', 'sel'),
                    'modaltitle' => $this->dof->get_string('form_listeditor_modaltitle', 'sel'),
                    'modalcontent' => $this->dof->get_string('form_listeditor_modalcontent', 'sel'),
                    'submitbuttonname' => $this->dof->get_string('form_listeditor_submitbuttonname', 'sel'),
                    'cancelbuttonname' => $this->dof->get_string('form_listeditor_cancelbuttonname', 'sel')
                ]
            );
        }
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Массив ошибок
        $errors = [];

        $contractids = [];
        if ( ! empty($data['form_listeditor_contractids']) )
        {// Найдены договора
            $contractids = array_keys ($data['form_listeditor_contractids'], 1);
        }

        if ( empty($contractids) )
        {// Не указано ни одного договора для массового действия
            $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_contractids_empty', 'sel');
            return $errors;
        }

        if ( empty($data['form_listeditor_massactions'][0]) )
        {// Не выбрано действие
            $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_massactions_action_empty', 'sel');
            return $errors;
        }

        if ( empty($data['form_listeditor_massactions'][1]) )
        {// Не выбрана опция действия
            $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_massactions_option_empty', 'sel');
            return $errors;
        }

        switch ($data['form_listeditor_massactions'][0])
        {
            // Смена статуса
            case 'contracts_status' :
                $statuses = $this->dof->workflow('contracts')->get_list();

                if ( ! isset($statuses[$data['form_listeditor_massactions'][1]]) )
                {// Неизвестный статус
                    $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_massactions_contracts_status_invalid_status', 'sel');
                    return $errors;
                }
                break;
            // Смена подразделения
            case 'contracts_department' :
                $targetdepartmentexist = $this->dof->storage('departments')->
                    is_exists($data['form_listeditor_massactions'][1]);
                if ( ! $targetdepartmentexist )
                {// Целевое подразделение не найдено
                    $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_massactions_contracts_department_department_not_found', 'sel');
                    return $errors;
                }
                if ( ! $this->dof->storage('contracts')->is_access('create', null, null, $data['form_listeditor_massactions'][1]) ||
                     ! $this->dof->storage('contracts')->is_access('edit', null, null, $this->addvars['departmentid'])
                   )
                {// Недостаточно прав
                    $errors['form_listeditor_hidden'] = $this->dof->get_string('form_listeditor_error_massactions_contracts_department_access_denied', 'sel');
                    return $errors;
                }

                // Проверка дополнительных опций
                switch ( $data['form_listeditor_massactions'][2] )
                {
                    case 'personandprogrammsbcs' :

                        // Проверка доступа к перемещению персоны
                        $accesscreate = $this->dof->storage('persons')->
                            is_access('create', null, null, $data['form_listeditor_massactions'][1]);
                        $accessedit = $this->dof->storage('persons')->
                            is_access('edit', null,  null, $this->addvars['departmentid']);
                        if ( ! $accesscreate || ! $accessedit )
                        {
                            $errors['form_listeditor_hidden'] = $this->dof->
                                get_string('form_listeditor_error_massactions_contracts_department_person_move_access_denied', 'sel');
                            return $errors;
                        }

                        // Проверка доступа к перемещению подписок на программы
                        $accesscreate = $this->dof->storage('programmsbcs')->
                            is_access('create', null, null, $data['form_listeditor_massactions'][1]);
                        $accessedit = $this->dof->storage('programmsbcs')->
                            is_access('edit', null,  null, $this->addvars['departmentid']);
                        if ( ! $accesscreate || ! $accessedit )
                        {
                            $errors['form_listeditor_hidden'] = $this->dof->
                                get_string('form_listeditor_error_massactions_contracts_department_programmsbcs_move_access_denied', 'sel');
                            return $errors;
                        }
                        break;
                    case 'person' :
                        // Проверка доступа к перемещению персоны
                        $accesscreate = $this->dof->storage('persons')->
                            is_access('create', null, null, $data['form_listeditor_massactions'][1]);
                        $accessedit = $this->dof->storage('persons')->
                            is_access('edit', null,  null, $this->addvars['departmentid']);
                        if ( ! $accesscreate || ! $accessedit )
                        {
                            $errors['form_listeditor_hidden'] = $this->dof->
                                get_string('form_listeditor_error_massactions_contracts_department_person_move_access_denied', 'sel');
                            return $errors;
                        }
                        break;
                    case 'programmsbcs' :
                        // Проверка доступа к перемещению подписок на программы
                        $accesscreate = $this->dof->storage('programmsbcs')->
                            is_access('create', null, null, $data['form_listeditor_massactions'][1]);
                        $accessedit = $this->dof->storage('programmsbcs')->
                            is_access('edit', null,  null, $this->addvars['departmentid']);
                        if ( ! $accesscreate || ! $accessedit )
                        {
                            $errors['form_listeditor_hidden'] = $this->dof->
                                get_string('form_listeditor_error_massactions_contracts_department_programmsbcs_move_access_denied', 'sel');
                            return $errors;
                        }
                        break;
                }
                break;
            // Неизвестное массовое действие
            default :
                $errors['form_listeditor_massactions'] = $this->dof->
                    get_string('form_listeditor_error_massactions_option_invalid_action', 'sel');
                break;
        }

        return $errors;
    }

    /**
     * Обработчик формы
     */
    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Форма подтверждена и данные получены

            $action = $formdata->form_listeditor_massactions[0];
            $option = $formdata->form_listeditor_massactions[1];
            $suboption = $formdata->form_listeditor_massactions[2];


            $contractids = [];
            foreach ( $formdata->form_listeditor_contractids as $contractid => $checkstate )
            {
                if ( $checkstate )
                {
                    $contractids[] = $contractid;
                }
            }

            // Генерация приказа в соответствие с задачей
            $order = $this->dof->im('sel')->order($action);

            // Формирование данных для сбора отчета
            $reportdata = new stdClass();

            // Заполняем дефолтными значениями
            $orderobj = new stdClass();
            $orderobj->departmentid = $formdata->departmentid;
            $orderobj->ownerid = $this->dof->storage('persons')->get_bu(null, true)->id;
            $orderobj->date = time();
            $orderobj->data = new stdClass();
            $orderobj->data->contractids = implode(';', $contractids);
            $orderobj->data->target = $option;
            $orderobj->data->option = $suboption;

            // Сохранение приказа
            $order->save($orderobj);

            // Подпись от имени персоны
            $order->sign($orderobj->ownerid);

            // Исполнение приказа
            $result = (bool)$order->execute();

            redirect(
                dof_build_url(
                    $this->returnurl,
                    ['massactions_'.$action.'_complete' => $result]
                )
            );
        }
    }

    /**
     * Добавление в форму таблицы договоров
     *
     * @return void
     */
    private function contracts_table()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Заголовок таблицы
        $mform->addElement(
            'header',
            'selids-table-header',
            $this->dof->get_string('form_listeditor_headertext', 'sel')
        );

        $mform->addElement('html', '<table class="generaltable boxaligncenter"><tr><th>');
        $this->add_checkbox_controller(1, $this->dof->get_string('form_listeditor_headercheckbox', 'sel'));
        $mform->addElement('html', '</th><th>');
        // Получение заголовков таблицы
        $headers = $this->contracts_table_headers();
        $mform->addElement('html', implode('</th><th>',$headers).'</th></tr>');

        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

        if ( ! empty($this->contracts) )
        {// Если договоры найдены

            foreach( $this->contracts as $conract )
            {
                $mform->addElement('html', '</td><td>');
                $mform->addElement('advcheckbox', 'form_listeditor_contractids['.$conract->id.']', '', '', ['group' => 1]);

                $contractrow = [];
                $fullname = '';

                // Действия над договором
                $actions = '';

                // Просмотр договора
                $url = $this->dof->url_im('sel', '/contracts/view.php', $this->addvars + ['id' => $conract->id]);
                $title = $this->dof->get_string('form_listeditor_view_contract', 'sel');
                $actions .= $this->dof->modlib('ig')->icon('view', $url, ['title' => $title]);
                // Просмотр подписок на программы
                $url = $this->dof->url_im('programmsbcs', '/list.php', $this->addvars + ['contractid' => $conract->id]);
                $title = $this->dof->get_string('form_listeditor_view_programmsbcs', 'sel');
                $actions .= $this->dof->modlib('ig')->icon('programmsbcs', $url, ['title' => $title]);
                // Просмотр зачетки студента
                if ( $this->dof->storage('persons')->is_exists($conract->studentid) )
                {
                    $url = $this->dof->url_im('recordbook', '/index.php', $this->addvars + ['clientid' => $conract->studentid]);
                    $title = $this->dof->get_string('form_listeditor_view_recordbook', 'sel');
                    $actions .= $this->dof->modlib('ig')->icon_plugin(
                        'recordbook',
                        'im',
                        'sel',
                        $url,
                        ['title' => $title]
                    );

                    $fullname = $this->dof->storage('persons')->get_fullname($conract->studentid);
                }
                $contractrow[] = $actions;

                // Номер договора
                $url = $this->dof->url_im('sel', '/contracts/view.php', $this->addvars + ['id' => $conract->id]);
                $contractrow[] = dof_html_writer::link($url, $conract->num);

                // Студент по договору
                if ( $fullname )
                {
                    $url = $this->dof->url_im(
                        'persons',
                        '/view.php', $this->addvars + ['id' => $conract->studentid]);
                    $contractrow[] = dof_html_writer::link($url, $fullname);
                } else
                {
                    $contractrow[] = dof_html_writer::span('');
                }


                // Дата
                $contractrow[] = dof_html_writer::span(dof_userdate($conract->date,'%d.%m.%Y', $usertimezone, false));

                // Статус
                $contractrow[] = $this->dof->workflow('contracts')->get_name($conract->status);

                $mform->addElement('html','</td><td>'.implode('</td><td>', $contractrow).'</td></tr>');
            }
        }
        $mform->addElement('html','</table>');
    }

    /**
     * Генерация заголовков
     *
     * @return array
     */
    private function contracts_table_headers()
    {
        $headers = [];

        // Получение GET-параметров ссылки возврата
        $addvars = [];
        $parseurl = parse_url($this->returnurl);
        $returnurl_addvars = explode('&', $parseurl['query']);
        // Определение GET-параметров ссылки возврата
        foreach ( $returnurl_addvars as $addvar )
        {// Получение данных по параметру
            $parameter = explode('=', $addvar);
            if ( isset($parameter[0]) && isset($parameter[1]) && ! isset($filter_addvars[$parameter[0]]) )
            {// Параметр валиден
                $addvars[$parameter[0]] = $parameter[1];
            }
        }

        // Действия над договором
        $headers[] = $this->dof->modlib('ig')->igs('actions');

        // Номер договора
        $icon = $this->dof->modlib('ig')->get_icon_sort('num', $this->addvars['sort'], $this->addvars['dir']);
        $url = $this->get_sort_url($parseurl['path'], array_merge($addvars, ['dir' => $icon[0], 'sort' => 'num']));
        $headers[] = dof_html_writer::link(
            $url,
            $this->dof->get_string('num','sel').$icon[1]
        );

        // ФИО студента
        $icon = $this->dof->modlib('ig')->get_icon_sort('fullname', $this->addvars['sort'], $this->addvars['dir']);
        $url = $this->get_sort_url($parseurl['path'], array_merge($addvars, ['dir' => $icon[0], 'sort' => 'fullname']));
        $headers[] = dof_html_writer::link(
            $url,
            $this->dof->get_string('fullname','sel').$icon[1]
        );

        // Дата
        $icon = $this->dof->modlib('ig')->get_icon_sort('date', $this->addvars['sort'], $this->addvars['dir']);
        $url = $this->get_sort_url($parseurl['path'], array_merge($addvars, ['dir' => $icon[0], 'sort' => 'date']));
        $headers[] = dof_html_writer::link(
            $url,
            $this->dof->get_string('date','sel').$icon[1]
        );

        // Статус
        $icon = $this->dof->modlib('ig')->get_icon_sort('status', $this->addvars['sort'], $this->addvars['dir']);
        $url = $this->get_sort_url($parseurl['path'], array_merge($addvars, ['dir' => $icon[0], 'sort' => 'status']));
        $headers[] = dof_html_writer::link(
            $url,
            $this->dof->get_string('status', 'sel').$icon[1]
        );

        return $headers;
    }

    /**
     * Получение ссылки для поля сортировки
     */
    private function get_sort_url($path, $addvars)
    {
        // Сборка GET-параметров
        foreach ( $addvars as $name => &$value )
        {
            $value = $name.'='.$value;
        }
        $addvars = implode('&', $addvars);

        return $path.'?'.$addvars;
    }

}


/**
 * Фильтр договоров на обучение
 */
class dof_im_sel_contracts_filter extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * GET параметры для ссылки
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
     * Данные фильтрации
     *
     * @var array
     */
    protected $filterdata = [];

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }

        // Получение данных фильтрации из GET-параметров
        $this->addvars_to_filter();

        // Получение текущей персоны
        $currentperson = $this->dof->storage('persons')->get_bu(null, true);

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );

        // Заголовок формы фильтрации
        $mform->addElement(
            'header',
            'formtitle',
            $this->dof->get_string('contracts_filter_header', 'sel')
        );

        // Группа фильтров по договору
        $filter_contractgroup = [];

        // Фильтрация по статусам
        $statuses = $this->dof->workflow('contracts')->get_list();
        foreach( $statuses as $key => $status )
        {
            $statuses[$key] = $this->dof->get_string('status:' . $key, 'contracts', null, 'workflow');
        }
        $statuses = [
            '' => $this->dof->get_string('contracts_filter_statuses_all_statuses', 'sel')
        ] + $statuses;
        $filter_contractgroup[] = $mform->createElement(
            'select',
            'statuses',
            $this->dof->get_string('status', 'sel').':',
            $statuses
        );
        $mform->setdefault('statuses', 'all_statuses');
        if ( isset($this->filterdata->statuses) )
        {// Указан статус
            $status = current($this->filterdata->statuses);
            $mform->setdefault('statuses', $status);
        }

        // Фильтрация по принадлежности договора
        $owningtypes = [
            '' => $this->dof->get_string('contracts_filter_owning_all_owners', 'sel'),
            'my' => $this->dof->get_string('my_contracts', 'sel')
        ];
        $filter_contractgroup[] = $mform->createElement(
            'select',
            'owningtype',
            $this->dof->get_string('owningtype', 'sel').':',
            $owningtypes
        );
        $mform->setdefault('owningtype', '');
        if ( isset($this->filterdata->owners[(int)$currentperson->id]) )
        {// Указана принадлежность
            $mform->setdefault('owningtype', 'my');
        }

        $mform->addGroup($filter_contractgroup, 'filter_contractgroup', '', '', false);

        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('contracts_filter_submit', 'sel')
        );
        $group[] = $mform->createElement(
            'submit',
            'reset',
            $this->dof->get_string('contracts_filter_reset', 'sel')
        );
        $mform->addGroup($group, 'buttons', '', '', false);

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = parent::validation($data, $files);

        // Вернуть ошибки валидации элементов
        return $errors;
    }

    /**
     * Заполнение полей формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return void
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы

            if ( empty($this->errors) )
            {// Сформировать массив GET-параметров для редиректа

                // Получение текущей персоны
                $currentperson = $this->dof->storage('persons')->get_bu(null, true);

                // Получение URL перехода
                $url = $this->returnurl;

                // Получение GET-параметров ссылки возврата
                $addvars = [];
                $parseurl = parse_url($url);
                $returnurl_addvars = explode('&', $parseurl['query']);
                // Определение GET-параметров ссылки возврата
                foreach ( $returnurl_addvars as $addvar )
                {// Получение данных по параметру
                    $parameter = explode('=', $addvar);
                    if ( isset($parameter[0]) && isset($parameter[1]) && ! isset($filter_addvars[$parameter[0]]) )
                    {// Параметр валиден
                        $addvars[$parameter[0]] = $parameter[1];
                    }
                }

                if ( isset($formdata->submit) )
                {// Применение фильтра

                    // Получение критериев фильтра
                    $filter = [];
                    if ( ! empty($formdata->statuses) )
                    {
                        $filter['statuses'] = $formdata->statuses;
                    }
                    if ( ! empty($formdata->owningtype) && $formdata->owningtype == 'my' )
                    {// Добавление привязки к пользователю
                        $filter['owners'] = $currentperson->id;
                    }

                    // Конвертация критериев в GET-параметр фильтра
                    if ( ! empty($filter) )
                    {
                        $filterstring = [];
                        foreach ( $filter as $filtertype => $value )
                        {// Добавление критерия в общий параметр

                            // В критерии не указано значение
                            if ( empty($value) )
                            {
                                continue;
                            }
                            // Группировка множественных значений критерия
                            if ( is_array($value) )
                            {
                                $value = implode(',', $value);
                            }
                            // Формирование строкового представления критерия
                            $filterstring[] = $filtertype.':'.$value;
                        }
                        // Сборка критериев в общую строку
                        $filterstring = implode('|', $filterstring);
                        // Добавление фильтра в общий GET-параметр
                        $addvars['filter'] = $filterstring;
                    } else
                    {// Сброс фильтра
                        unset($addvars['filter']);
                    }
                } else
                {// Сброс фильтра
                    unset($addvars['filter']);
                }

                // Сборка URL перехода
                foreach ( $addvars as $name => &$value )
                {
                    $value = $name.'='.$value;
                }
                $addvars = implode('&', $addvars);
                $returnurl = $parseurl['path'].'?'.$addvars;

                redirect($returnurl);
            }
        }
    }

    /**
     * Получить данные для формы на основе GET-параметров
     *
     * @return void
     */
    protected function addvars_to_filter()
    {
        // Список критериев фильтрации
        $this->filterdata = new stdClass();
        $this->filterdata->statuses = [];
        $this->filterdata->owners = [];

        // СТАНДАРТНЫЙ ПАРСЕР ФИЛЬТРА ИЗ GET-ПАРАМЕТРА
        if ( ! empty($this->addvars['filter']) )
        {// Найден GET-параметр фильтра

            // Разбиение фильтра на отдельные критерии отбора
            $filters = (array)explode('|', $this->addvars['filter']);
            foreach ( $filters as $singlefilter )
            {// Обработка каждого критерия

                // Определение имени критерия и его значения
                $singlefilter = explode(':', $singlefilter, 2);
                if ( isset($singlefilter[0]) && isset($singlefilter[1]) )
                {// Критерий успешно определен

                    // Нормализация имени критерия
                    $filtername = trim($singlefilter[0]);

                    // Нормализация значения критерия
                    $filterdata = trim($singlefilter[1]);

                    // Значения критерия разделены запятой
                    $filterdata = (array)explode(',', $filterdata);
                    foreach ( (array)$filterdata as $singlevalue )
                    {// Выделение каждого из значений критерия

                        // Нормализация значения критерия
                        $singlevalue = trim($singlevalue);

                        // Добавление в итоговый набор фильтра
                        if ( $filtername == 'statuses' )
                        {
                            $this->filterdata->{$filtername}[(string)$singlevalue] = $singlevalue;
                        } else
                        {
                            $this->filterdata->{$filtername}[(int)$singlevalue] = $singlevalue;
                        }
                    }
                }
            }
        }

        // ДОПОЛНИТЕЛЬНЫЙ ПАРСЕР С ПОДДЕРЖКОЙ УСТАРЕВШИХ GET-ПАРАМЕТРОВ

        // Получение текущей персоны
        $currentperson = $this->dof->storage('persons')->get_bu(null, true);
        // Определение целевой персоны
        $targetpersonid = optional_param('personid', (int)$currentperson->id, PARAM_INT);

        // Фильтрация по статусу
        $status = optional_param('status', null, PARAM_ALPHA);
        if ( ! empty($status) )
        {// Устаревшая опция фильтрации
            $this->filterdata->statuses[$status] = $status;
        }

        // Фильтрация по принадлежности
        $searchoption = optional_param('searchoption', null, PARAM_TEXT);
        if ( $searchoption == 'my_contracts' )
        {// Устаревшая опция фильтрации
            $this->filterdata->owners[(int)$targetpersonid] = $targetpersonid;
        }

        // Фильтрация по менеджеру
        $byseller = optional_param('byseller', 0, PARAM_BOOL);
        if ( $byseller )
        {// Фильтация договоров, где целевой пользователь является менеджером
            $this->filterdata->owners[(int)$targetpersonid] = $targetpersonid;
        }

        // Фильтрация по персоне
        $personid = optional_param('personid', 0, PARAM_BOOL);
        if ( $personid )
        {// Фильтация договоров, где целевой пользователь является менеджером
            $this->filterdata->owners[(int)$targetpersonid] = $targetpersonid;
        }
    }

    /**
     * Получить идентификаторы договоров с учетом фильтра
     *
     * @return array - Массив идентификаторов договоров
     */
    public function get_contractsids()
    {
        // Получение текущей персоны
        $currentperson = $this->dof->storage('persons')->get_bu(null, true);

        // Формирование условий отбора договоров
        $conditions = [];

        // Фильтрация с учетом подразделения
        $departmentstatuses = (array)$this->dof->workflow('departments')->
            get_meta_list('active');
        $departments = (array)$this->dof->storage('departments')->
            get_departments(
                (int)$this->addvars['departmentid'],
                ['statuses' => array_keys($departmentstatuses)]
            );
        $conditions['departmentid'] = array_keys($departments);
        array_push($conditions['departmentid'], $this->addvars['departmentid']);

        // Фильтрация с учетом статусов договоров
        if ( isset($this->filterdata->statuses) )
        {// Указана фильтрация по статусам

            // Получение списка возможных статусов
            $validstatuses = $this->dof->workflow('contracts')->get_list();

            // Проверка указанных в фильтре статусов на валидность
            $statuses = [];
            foreach ( $this->filterdata->statuses as $status )
            {
                if ( isset($validstatuses[$status]) )
                {// Статус валиден
                    $statuses[] = $status;
                }
            }
            if ( ! empty($statuses) )
            {
                $conditions['status'] = $statuses;
            }
        } else
        {// Фильтрация по реальному мета-статусу
            $contractstatuses = (array)$this->dof->workflow('departments')->
                get_meta_list('real');
            $conditions['status'] = array_keys($contractstatuses);
        }

        // Получение идентификаторов договоров

        if ( ! empty($this->filterdata->owners) )
        {// Фильтрация с учетом владельца договоров
            // Получение договоров с учетом владельцев
            $contractids = $this->dof->storage('contracts')->get_contracts_by_personids(
                    $this->filterdata->owners,
                    $conditions,
                    'id'
                );
        } else
        {// Стандартная фильтрация
            $contractids = $this->dof->storage('contracts')->get_records(
                $conditions,
                '',
                'id'
            );
        }
        $contractids = (array)$contractids;
        // Проверка прав доступа
        foreach ( $contractids as $contractid => &$contract )
        {
            if ( ! $this->dof->storage('contracts')->is_access('view', $contractid, null, $this->addvars['departmentid']) )
            {
                unset($contractids[$contractid]);
            }
        }
        return array_keys($contractids);
    }

    /**
     * Получить идентификаторы договоров с учетом фильтра
     *
     * @return array - Массив идентификаторов договоров
     */
    public function get_contracts($conditions)
    {
        foreach ( $conditions as $field => $values )
        {
            if ( empty($values) )
            {// Указанное поле не имеет значений для фильтрации

                // Требуется вернуть пустой список
                return [];
            }
        }

        // Нормализация
        if ( ! empty($this->addvars['sort']) )
        {// Сортировка передана
            $sort = trim($this->addvars['sort']);
        } else
        {// Сортировка по умолчанию
            $sort = 'date';
        }

        // Корректировка сортировка по имени
        if ( $sort == 'fullname' )
        {
            $sort = 'sortname';
        }
        if ( ! empty($sort) )
        {// Указана сортировка

            $dir = 'ASC';
            if ( ! empty($this->addvars['dir']) && $this->addvars['dir'] == 'desc' )
            {
                $dir = 'DESC';
            }
            $sort = $sort.' '.$dir;
        }

        $limitfrom = $this->addvars['limitfrom'] - 1;
        if ( $limitfrom < 0 )
        {
            $limitfrom = 0;
        }

        // Получение списка договоров
        return $this->dof->storage('contracts')->get_listing(
            $conditions,
            $limitfrom,
            $this->addvars['limitnum'],
            $sort
        );
    }
}

?>