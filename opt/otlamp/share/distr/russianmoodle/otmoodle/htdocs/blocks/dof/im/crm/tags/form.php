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

/*
 * Форма указания класса тега
 */
class block_dof_im_crm_tag_form_select extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        $this->dof = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('form_tag_select_create','crm'));

        // Класс тега
        // Получаем все классы из папки storages/tags/classes
        $dir = scandir($this->dof->plugin_path('storage','tags','/classes'));
        $tagclasses = array( '' => $this->dof->get_string('select_tagclass', 'crm') );
        foreach ( $dir as $key => $value )
        {
            // Очищаем результат от мусора и формируем массив классов
            if ( ! in_array($value,array(".","..")) )
            {
                if ( is_dir($this->dof->plugin_path('storage','tags','/classes/'.$value)) )
                {
                    $tagclasses[$value] = $this->dof->get_string('tagclass_'.$value,'crm');
                }
            }
        }
        $mform->addElement('select', 'class', $this->dof->get_string('tagclass','crm'), $tagclasses);

        // Кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('save_tag','crm'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    function validation($data, $files)
    {
        $errors = array();

        // Если класс тега не выбран
        if ( empty($data['class']) )
        {
            $errors['class'] =
                $this->dof->get_string(
                    'error_class_not_selected','crm');
        }

        // возвращаем ошибки, если они возникли
        return $errors;
    }

    /**
     * Функци яобработки данных из формы создания/редактирования
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
		{//ввод данных отменен - возвращаем на страницу просмотра тегов
		    redirect($this->dof->url_im('crm','/tags/alltags.php',$addvars));
		}
		if ( $this->is_submitted() AND $data = $this->get_data() )
		{// Если получили данные и они подтверждены
		    $addvars['tagclass'] = $data->class;
		    redirect($this->dof->url_im('crm','/tags/action.php', $addvars));
		}
    }
}

/*
 * Форма добавления нового тега
 */
class block_dof_im_crm_tag_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Получаем параметры
        $this->dof = $this->_customdata->dof;
        $depid = $this->_customdata->departmentid;
        $tagid = $this->_customdata->tagid;
        $tagclass = $this->_customdata->tagclass;

        // Получаем пользователя
        $user = $this->dof->storage('persons')->get_bu();

        // Создаем заголовок формы
        $mform->addElement('header','formtitle',  $this->get_form_title($tagid));

        // Добавим подразделение тега
        $mform->addElement('hidden','depid');
        $mform->setType('depid', PARAM_INT);
        if ( ! empty($tagid) )
        {// Происходит редактироватние, подразделение тега сохраняем
            $tag = $this->dof->storage('tags')->get($tagid);
            $mform->setDefault('depid', $tag->departmentid);
        } else
        {// Происходит создание, подразделение тега - текущее
            $mform->setDefault('depid', $depid);
        }

        // Добавим класс тега
        $mform->addElement('hidden','class');
        $mform->setType('class', PARAM_TEXT);
        $mform->setDefault('class', $tagclass);
        $mform->addElement('html', '<div><b>'.$this->dof->get_string('tagclass_'.$tagclass,'crm').'</b></div>');

        // Код тега
        $mform->addElement('text', 'code', $this->dof->get_string('tagcode','crm'), 'size="12"');
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');

        // Алиас
        $mform->addElement('text', 'alias', $this->dof->get_string('tagalias','crm'));
        $mform->setType('alias', PARAM_TEXT);

        // Описание
        $mform->addElement('editor', 'about', $this->dof->get_string('tagabout','crm'));
        $mform->setType('about', PARAM_RAW);

        // ID родительского тега

        // Формируем фильтр для получения родительских тегов
        $filters = new stdClass();

        // Добавим статусы к фильтрации
        $filters->status = $this->dof->workflow('tags')->get_meta_list('real');

        // Сформируем подразделения
        $departments = $this->dof->storage('departments')->departments_list_subordinated($depid);
        $departments[$depid] = $depid;
        // Добавим подразделения к фильтрации
        $filters->departmentid = $departments;

        // Сформируем владельцев
        $owners = Array(0 => 0);
        $owners[$user->id] = $user->id;
        // Добавим владельцев к фильтрации
        $filters->ownerid = $owners;

        // Получаем отфильтрованные теги
        $tagclasses = $this->dof->storage('tags')->get_list_tags($filters);
        // Если мы редактируем тег, то удалим из масива его самого
        if ( ! empty($tagid) )
        {
           unset($tagclasses[$tagid]);
        }
        // Формируем массив для select
        $parentselect = array(0 => $this->dof->get_string('no_parent_tag','crm'));
        foreach ( $tagclasses as $key => $value )
        {
            if ( ! empty($value->alias) )
            {// Если есть алиас
                $parentselect[$key] = $value->alias;
            } else
            {// Если алиаса нет
                $parentselect[$key] = $value->code;
            }
        }
        // Добавляем выбор родительского тега
        $mform->addElement('select', 'parentid', $this->dof->get_string('tagparent','crm'), $parentselect);
        $mform->setType('parentid', PARAM_INT);

        // Добавляем уровень доступа к тегу

        // массив для выбора уровня доступа
        $ownerselect = array();
        // Добавим типы доступа
        $ownerselect[0] = $this->dof->get_string('tag_public','crm');
        $ownerselect[$user->id] = $this->dof->get_string('tag_private','crm');

        $mform->addElement('select', 'ownerid', $this->dof->get_string('tag_access_level','crm'), $ownerselect);
        $mform->setType('ownerid', PARAM_INT);

        // Крон
        // Переменная, контроллирующая выключение части формы
        $stoptions = array();
        $stoptions = array('disabled' => 'disabled');
        // Запуск крона не требуется
        $mform->addElement('radio', 'cron', $this->dof->get_string('cron','crm'), $this->dof->get_string('disable_cron','crm'), -1);
        // Выполнить при следующем запуске
        $mform->addElement('radio', 'cron', null, $this->dof->get_string('next_cron','crm'), 0);
        // Выполнить после указанного времени
        $mform->addElement('radio', 'cron', null, $this->dof->get_string('timestamp_cron','crm'), 1);
        $mform->setType('cron', PARAM_INT);
        $mform->setDefault('cron', 0);

        // Начальная дата крона
        $mform->addElement('date_time_selector', 'cronstart', '');

        // Период устаревания тега
        $mform->addElement('text', 'cronrepeate', $this->dof->get_string('cronrepeate','crm'));
        $mform->setType('cronrepeate', PARAM_INT);
        $mform->setDefault('cronrepeate', 0);

        // Опции отключения частей формы
        $mform->disabledIf('cronrepeate', 'cron', 'eq', -1 );
        $mform->disabledIf('cronstart', 'cron', 'eq', -1 );
        $mform->disabledIf('cronstart', 'cron', 'eq', 0 );

        // Опции тега
        $this->get_class_options($mform, $tagclass);

        // Кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('save_tag','crm'));

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Добавление дополнительльных полей формы и установка значений по умолчанию
     * после загрузки данных в форму (если происходит редактирование)
     *
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Получаем ID тега
        $tagid = $this->_customdata->tagid;

        // Получаем тег
        $tag = $this->dof->storage('tags')->get($tagid);

        if ( ! empty($tag) )
        {// Если получили тег
            // Крон
            if ( $tag->cron > 0 )
            {
                $mform->setDefault('cron', '1');
                $mform->setDefault('cronstart', $tag->cron);
            }
            // Описание
            $mform->setDefault('about', $tag->about );

            if (!empty($tag->options))
            {
                // Десериализуем опции
                $tagoptions = unserialize($tag->options);
                if ($tagoptions !== false) {
                    // Заполняем опции тега
                    foreach ($tagoptions as $option => $value)
                    {
                        $mform->setDefault($option, $value);
                    }
                }
            }
        }
    }

    /**
     * Функци яобработки данных из формы создания/редактирования
     * @return string
     */
    public function process($addvars)
    {
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра тегов
            redirect($this->dof->url_im('crm','/tags/alltags.php',$addvars));
        }
        if ( $this->is_submitted() AND $data = $this->get_data() )
        {// Если получили данные и они подтверждены

            // Если указана дата перелинковки тега
            if ( $data->cron > 0 )
            {
                $data->cron = $data->cronstart;
            }
            // Если не нужно пролинковывать тег
            if ( $data->cron < 0 )
            {
                // Устанавливаем повтор в 0
                $data->cronrepeate = 0;
            }

            // Формируем опции тега
            $data->options = new stdClass();
            foreach ( $this->dataoptions as $name )
            {
                $data->options->$name = $data->$name;
            }

            // Получаем ID тега
            $tagid = $this->_customdata->tagid;

            if ( $tagid > 0 )
            {// Тег редактируется - обновляем запись
                $result = $this->dof->storage('tags')->update_tag(
                    $tagid,
                    $data->class,
                    $data->code,
                    intval($data->depid),
                    $data->alias,
                    $data->about['text'],
                    intval($data->parentid),
                    intval($data->ownerid),
                    intval($data->cron),
                    intval($data->cronrepeate),
                    $data->options
                );
            }else
            {// Тег добавляется - добавляем запись
                $result = $this->dof->storage('tags')->add_tag(
                    $data->class,
                    $data->code,
                    intval($data->depid),
                    $data->alias,
                    $data->about['text'],
                    intval($data->parentid),
                    intval($data->ownerid),
                    intval($data->cron),
                    intval($data->cronrepeate),
                    $data->options
                );
            }

            if ( $result->errorstatus )
            {// Если произошли ошибки
                $this->dof->print_error($result->errortext, '', NULL, 'im', 'crm');
            } else
            {// Если все успешно
                $addvars['success'] = 1;
                redirect($this->dof->url_im('crm','/tags/alltags.php',$addvars));
            }
        }
    }

    /**
     * Возвращает строку заголовка формы
     * @param int $ageid
     * @return string
     */
    private function get_form_title($id)
    {
        if ( ! $id )
        {//заголовок создания формы
            return $this->dof->get_string('form_tag_create','crm');
        }else
        {//заголовок редактирования формы
            return $this->dof->get_string('form_tag_edit','crm');
        }

    }

    /**
     * Формируем поля опций для класса тега
     *
     * @param object $mform - объект формы
     * @param string $tagclass - клас тега
     *
     * @return array - массив опций класса тега
     */
    private function get_class_options($mform, $tagclass)
    {
        // Получаем экземпляр класса
        $options = new stdClass();
        $tagobject = $this->dof->storage('tags')->tagclass($tagclass, $options);
        // Получаем массив опций
        $options = $tagobject->get_tagoptions();

        // Создаем массив полей для понимания,какие из данных в форме - опции тега
        $dataoptions = array();

        // Начинаем печатать опции тега
        foreach ( $options as $name => $param )
        {
            if ( is_array($param) && ! empty($param) && isset($param['type']) )
            {
                // Если не установлены параметры, создаем их
                if ( ! isset($param['label']) )
                {
                    $param['label'] = '';
                }
                if ( ! isset($param['options']) )
                {
                    $param['options'] = array();
                }
                // Добавляем поле ввода
                $mform->addElement(
                        (string) $param['type'],
                        (string) $name,
                        (string) $param['label'],
                        (array) $param['options']
                );
                // Добавляем имя поля в массив
                $dataoptions[] = $name;

                // Добавляем дополнительные параметры к полю
                if ( isset($param['setType']) ) // Тип поля
                {
                    $mform->setType(
                            (string) $name,
                            (string) $param['setType']
                    );
                }
                if ( isset($param['setDefault']) ) // Дефолтные значения
                {
                    $mform->setDefault(
                            (string) $name,
                            $param['setDefault']
                    );
                }
                if ( isset($param['addRule']) ) // Валидация
                {
                    if ( ! isset($param['addRuleValidation']) )
                    {
                        $param['addRuleValidation'] = 'client';
                    }
                    $mform->addRule(
                            (string) $name,
                            $this->dof->modlib('ig')->igs('form_err_required'),
                            (string) $param['addRule'],
                            null,
                            (string) $param['addRuleValidation']
                    );
                }
            }
        }
        $this->dataoptions = $dataoptions;
    }
}

?>