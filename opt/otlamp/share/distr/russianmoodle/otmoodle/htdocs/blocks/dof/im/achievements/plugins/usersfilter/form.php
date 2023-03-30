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
 * Плагин фильтра пользователей. Классы форм.
 *
 * @package    im
 * @subpackage achievements
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * Форма настройки отображения фильтра
 */
class dof_im_achievements_usersfilter_settingsform extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID подразделения
     */
    protected $departmentid = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->departmentid = $this->_customdata->departmentid;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);

        // Заголовок формы
        $mform->addElement(
                'header',
                'form_header',
                $this->dof->get_string('userfilter_settingsform_header_title', 'achievements')
        );

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
                'static',
                'hidden',
                ''
        );

        $userprofilefieldsgroup = [];

        // Набор пользовательских полей
        $mform->addElement(
            'static',
            'userprofilefields_title',
            $this->dof->get_string('userfilter_settingsform_userprofilefields_title', 'achievements')
        );

        $userprofilefieldsgroup[] = $mform->createElement(
            'advcheckbox',
            'lastname',
            '',
            $this->dof->get_string('userprofilefield_lastname', 'achievements'),
            ['group' => 3],
            [0, 1]
            );
        $userprofilefieldsgroup[] = $mform->createElement(
            'advcheckbox',
            'firstname',
            '',
            $this->dof->get_string('userprofilefield_firstname', 'achievements'),
            ['group' => 3],
            [0, 1]
        );
        $userprofilefieldsgroup[] = $mform->createElement(
            'advcheckbox',
            'middlename',
            '',
            $this->dof->get_string('userprofilefield_middlename', 'achievements'),
            ['group' => 3],
            [0, 1]
        );

        if( ! empty($userprofilefieldsgroup) )
        {
            $mform->addGroup($userprofilefieldsgroup, 'userprofilefields', '', '<br/>');
            $this->add_checkbox_controller(3);
        }

        // Получение дополнительный полей пользователя
        $customfields = $this->dof->modlib('ama')->user(false)->get_user_custom_fields();
        if ( ! empty($customfields) )
        {// Есть дополнительные поля пользователя
            $customfieldsgroup = [];
            // Набор пользовательских полей
            $mform->addElement(
                'static',
                'customfields_title',
                $this->dof->get_string('userfilter_settingsform_customfields_title', 'achievements')
            );
            foreach ( $customfields as $customfield )
            {
                // Добавление в настройки в зависимости от типа поля
                switch ( $customfield->datatype )
                {
                    case 'menu':
                    case 'checkbox':
                    case 'text':
                        // Добавить поле выбора значения
                        $customfieldsgroup[] = $mform->createElement(
                            'advcheckbox',
                            $customfield->shortname,
                            '',
                            $customfield->name,
                            ['group' => 1],
                            [0, 1]
                        );
                        break;
                    default :
                        break;
                }


            }
            if( ! empty($customfieldsgroup) )
            {
                $mform->addGroup($customfieldsgroup, 'customfields', '', '<br/>');
                $this->add_checkbox_controller(1);
            }
        }

        $achievementfieldsgroup = [];
        // Набор полей портфолио
        $mform->addElement(
                'static',
                'achievementfields_title',
                $this->dof->get_string('userfilter_settingsform_achievementfields_title', 'achievements')
        );
        // Дата создания
        $achievementfieldsgroup[] = $mform->createElement(
                'advcheckbox',
                'createdate',
                '',
                $this->dof->get_string('createdate', 'achievements'),
                ['group' => 2],
                [0, 1]
        );
        $mform->addGroup($achievementfieldsgroup, 'achievementfields', '', '<br/>');
        $this->add_checkbox_controller(2);

        $groupfieldsgroup = [];
        // Набор полей портфолио
        $mform->addElement(
            'static',
            'groupfields_title',
            $this->dof->get_string('userfilter_settingsform_groupfields_title', 'achievements')
        );
        $groupfieldsgroup[] = $mform->createElement(
            'advcheckbox',
            'agroup',
            '',
            $this->dof->get_string('agroup', 'achievements'),
            ['group' => 3],
            [0, 1]
        );
        $mform->addGroup($groupfieldsgroup, 'groupfields', '', '<br/>');

        $group = [];
        $group[] = $mform->createElement('submit', 'submit', $this->dof->get_string('form_achievementcats_edit_submit', 'achievements'));
        $group[] = $mform->createElement('submit', 'submitclose', $this->dof->get_string('form_achievementcats_edit_submit_close', 'achievements'));
        $mform->addGroup($group, 'submit', '', '');

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = array();

        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Заполнение формы данными
     */
    function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Получение конфигурации фильтра
        $params = [
                        'departmentid' => $this->departmentid,
                        'code' => 'usersfilter_fields',
                        'plugintype' => 'im',
                        'plugincode' => 'achievements'
        ];
        // Получение настройки фильтра
        $configrecords = $this->dof->storage('config')->get_records($params);
        if ( ! empty($configrecords) )
        {
            // Получение значения конфигурации
            $configvalue = array_pop($configrecords)->value;
            $configvalue = unserialize($configvalue);
            if ( ! empty($configvalue) && is_array($configvalue) )
            {// Указаны группы полей
                foreach ( $configvalue as $groupname => $fields )
                {// Обработка каждой группы
                    if ( ! empty($fields) && is_array($fields) )
                    {// У группы указаны поля
                        foreach ( $fields as $fieldname => $field )
                        {// Добавление поля
                            $mform->setDefault($groupname.'['.$fieldname.']', 1);
                        }
                    }
                }
            }
        }
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $formdata = $this->get_data() )
        {
            $fields = [];
            if ( isset($formdata->userprofilefields) )
            {// Есть пользовательские поля
                $fields['customfields'] = [];
                if ( ! empty($formdata->userprofilefields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->userprofilefields as $userprofilefieldname => $status )
                    {
                        if ( $status == 1 )
                        {// Поле активно
                            $fields['userprofilefields'][$userprofilefieldname] = $userprofilefieldname;
                        }
                    }
                }
            }
            if ( isset($formdata->customfields) )
            {// Есть пользовательские поля
                $fields['customfields'] = [];
                if ( ! empty($formdata->customfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->customfields as $customfieldname => $status )
                    {
                        if ( $status == 1 )
                        {// Поле активно
                            $fields['customfields'][$customfieldname] = $customfieldname;
                        }
                    }
                }
            }
            if ( isset($formdata->achievementfields) )
            {// Есть пользовательские поля
                $fields['achievementfields'] = [];
                if ( ! empty($formdata->achievementfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->achievementfields as $achievementfieldname => $status )
                    {
                        if ( $status == 1 )
                        {// Поле активно
                            $fields['achievementfields'][$achievementfieldname] = $achievementfieldname;
                        }
                    }
                }
            }
            if( isset($formdata->groupfields) )
            {
                $fields['groupfields'] = [];
                if ( ! empty($formdata->groupfields) )
                {
                    // Добавить каждое активное поле
                    foreach ( $formdata->groupfields as $groupfieldname => $status )
                    {
                        if ( $status == 1 )
                        {// Поле активно
                            $fields['groupfields'][$groupfieldname] = $groupfieldname;
                        }
                    }
                }
            }

            // Проверка на существование конфигурации фильтра
            $params = [
                            'departmentid' => $formdata->departmentid,
                            'code' => 'usersfilter_fields',
                            'plugintype' => 'im',
                            'plugincode' => 'achievements'
            ];
            $config = new stdClass();
            $config->departmentid = $formdata->departmentid;
            $config->code = 'usersfilter_fields';
            $config->plugintype = 'im';
            $config->plugincode = 'achievements';
            $config->type = 'text';
            $config->value = serialize($fields);

            $configrecords = $this->dof->storage('config')->get_records($params);
            if ( empty($configrecords) )
            {// Конфигурация не найдена
                // Добавить значение
                $result = $this->dof->storage('config')->insert($config);
            } else
            {
                // Получение ID конфигурации
                $id = array_pop($configrecords)->id;
                if ( count($configrecords) > 1 )
                {// Найдены дубли
                    foreach ( $configrecords as $record )
                    {// Удаление дубля
                        $this->dof->storage('config')->delete($record->id);
                    }
                }
                $result = $this->dof->storage('config')->update($config, $id);
            }
            if ( ! empty($result) )
            {
                if ( isset($formdata->submit['submit']) )
                {// Сохранение без перехода на страницу верхнего уровня
                    $this->addvars['settingssavesuccess'] = '1';
                    redirect($this->dof->url_im('achievements', '/plugins/usersfilter/settings.php', $this->addvars));
                }
                if ( isset($formdata->submit['submitclose']) )
                {// Сохранение с переходом на страницу верхнего уровня
                    $this->addvars['settingssavesuccess'] = '1';
                    redirect($this->dof->url_im('achievements', '/plugins/index.php', $this->addvars));
                }
            } else
            {
                $this->errors[] = $this->dof->get_string('error_im_achievements_usersfilter_settingsform_save', 'achievements');
            }
        }
    }
}

/**
 * Форма фильтрации пользователей и достижений
 *
 * Организует фильтрацию по полям достижений и дополнительным полям пользователя
 */
class dof_im_achievements_usersfilter_userform extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var
     */
    protected $filter = NULL;

    /**
     * Массив полей фильтра
     *
     * @var array
     */
    protected $fields = [];

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    protected $departmentid;

    /**
     * Настройки фильтрации
     *
     * @var array
     */
    protected $filtersearchparams = [];

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->departmentid = $this->_customdata->departmentid;

        $filtersearchparams = optional_param('filter', NULL, PARAM_RAW);
        $this->filtersearchparams = (array)json_decode($filtersearchparams);

        // Активные категории
        $achievementcats = (array)$this->dof->storage('achievementcats')->get_categories_select_options();
        if( empty($this->addvars['defaultachievementcat']) ) {
            reset($achievementcats);
            $this->addvars['defaultachievementcat'] = key($achievementcats);
        }
        if (!isset($this->filtersearchparams['achievement_category'])) {
            $this->filtersearchparams['achievement_category'] = $this->addvars['defaultachievementcat'];
        }

        // Получение настроек полей
        $configvalue = $this->dof->storage('config')->get_config_value(
            'usersfilter_fields',
            'im',
            'achievements',
            $this->departmentid
            );
        if (!empty($configvalue)) {
            $this->fields = (array)unserialize($configvalue);
        }
        // Обязательные категории фильтра
        if (!isset($this->fields['achievementfields'])) {
            $this->fields = ['achievementfields' => []];
        }
        // Обязательные поля фильтра
        if (!array_key_exists('category', $this->fields['achievementfields'])) {
            $this->fields['achievementfields']['category'] = 1;
        }
        // Добавление GET-параметров в виде hidden-полей
        foreach ( $this->addvars as $name => $value )
        {
            $mform->addElement(
                    'hidden',
                    $name,
                    $value
            );
            $mform->setType($name, PARAM_TEXT);
        }
        $countfieldname = 0;
        $displayuserheader = true;
        $displayachievementheader = true;
        $arsortgroups = ['userprofilefields','customfields','achievementfields','groupfields'];
        if ( ! empty($this->fields) )
        {// Указаны группы полей
            $mform->addElement(
                'header',
                'form_filter_title',
                $this->dof->get_string('achievements_usersfilter_rating_title', 'achievements')
            );
            foreach ( $arsortgroups as $groupname )
            {// Обработка каждой группы
                $fields=[];
                if( ! empty($this->fields[$groupname]) )
                {
                    $fields = $this->fields[$groupname];
                }
                if ( ! empty($fields) && is_array($fields) )
                {// У группы указаны поля
                    foreach ( $fields as $fieldname => $field )
                    {// Добавление поля
                        if ($fieldname == 'category' && $groupname == 'achievementfields') {
                            //Это для того чтобы не показывть заголовок
                            if (count($achievementcats) <= 1) {
                                continue;
                            }
                        }
                        $countfieldname++;
                        switch ( $groupname )
                        {
                            // Поле из группы фильтра достижений
                            case 'achievementfields' :
                                if ( $displayachievementheader )
                                {
                                    $mform->addElement(
                                        'static',
                                        'static_1',
                                        '',
                                        dof_html_writer::tag(
                                            'h4',
                                            $this->dof->get_string('form_achievementins_filter_title', 'achievements')
                                        )
                                    );
                                    $displayachievementheader = false;
                                }

                                switch ( $fieldname )
                                {
                                    // Фильтрация разделов
                                    case 'category' :
                                        if (count($achievementcats) > 1 && !$mform->elementExists('achievement_category')) {
                                            // Выпадающий список с категориями добавляем, если категорий больше 1
                                            $select = $mform->addElement(
                                                'select',
                                                'achievement_category',
                                                $this->dof->get_string('achievements_usersfilter_filter_by_category', 'achievements'),
                                                []
                                            );

                                            foreach ( $achievementcats as $key => $cat )
                                            {
                                                $attributes = [];
                                                if ( is_string($key) )
                                                {// Отключенная опция списка
                                                    $key = (int)$key;
                                                    $attributes['disabled'] = 'disabled';
                                                }
                                                $select->addOption($cat, $key, $attributes);
                                            }
                                            // Интересно зачем это тут?
                                            $chooseachievemenstcatgroup[] = $select;
                                        }
                                        break;
                                    // Фильтр по дате создания
                                    case 'createdate' :
                                        $group = [];

                                        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
                                        $opts = [];
                                        $opts['timezone'] = $usertimezone;
                                        $opts['startyear'] = 2000;
                                        $opts['stopyear'] = 2050;
                                        $opts['optional'] = true;
                                        $opts['onlytimestamp'] = true;
                                        $opts['hours'] = 00;
                                        $opts['minutes'] = 00;

                                        $group[] = $mform->createElement(
                                            'dof_date_selector',
                                            'from',
                                            $this->dof->get_string('achievements_usersfilter_createdate_from', 'achievements').'&nbsp;',
                                            $opts
                                        );
                                        $mform->disabledIf('achievement_createdate[from][day]', 'achievement_createdate[from][enabled]', 'notchecked');
                                        $mform->disabledIf('achievement_createdate[from][month]', 'achievement_createdate[from][enabled]', 'notchecked');
                                        $mform->disabledIf('achievement_createdate[from][year]', 'achievement_createdate[from][enabled]', 'notchecked');

                                        $opts['hours'] = 23;
                                        $opts['minutes'] = 55;
                                        $group[] = $mform->createElement(
                                            'dof_date_selector',
                                            'to',
                                            $this->dof->get_string('achievements_usersfilter_createdate_to', 'achievements').'&nbsp;',
                                            $opts
                                        );
                                        $mform->disabledIf('achievement_createdate[to][day]', 'achievement_createdate[to][enabled]', 'notchecked');
                                        $mform->disabledIf('achievement_createdate[to][month]', 'achievement_createdate[to][enabled]', 'notchecked');
                                        $mform->disabledIf('achievement_createdate[to][year]', 'achievement_createdate[to][enabled]', 'notchecked');

                                        $mform->addGroup(
                                            $group,
                                            'achievement_createdate',
                                            $this->dof->get_string('achievements_usersfilter_filter_by_createdate', 'achievements'),
                                            '<div class="col-12 px-0"></div>'
                                        );

                                        break;
                                }
                                break;
                            case 'userprofilefields' :
                                if ( $displayuserheader )
                                {
                                    $mform->addElement(
                                        'static',
                                        'static_3',
                                        '',
                                        dof_html_writer::tag(
                                            'h4',
                                            $this->dof->get_string('plugin_userfilter', 'achievements')
                                        )
                                    );
                                    $displayuserheader = false;
                                }
                                // Добавить в фильтр дополнительное поле пользователя
                                $this->get_profileuserfield_field($fieldname);
                                break;
                            case 'customfields' :
                                if ( $displayuserheader )
                                {
                                    $mform->addElement(
                                        'static',
                                        'static_1',
                                        '',
                                        dof_html_writer::tag(
                                            'h4',
                                            $this->dof->get_string('plugin_userfilter', 'achievements')
                                        )
                                    );
                                    $displayuserheader = false;
                                }
                                // Добавить в фильтр дополнительное поле пользователя
                                $this->get_customfield_field($fieldname);
                                break;
                            case 'groupfields':
                                if ( $displayuserheader )
                                {
                                    $mform->addElement(
                                        'static',
                                        'static_1',
                                        '',
                                        dof_html_writer::tag(
                                            'h4',
                                            $this->dof->get_string('plugin_userfilter', 'achievements')
                                            )
                                        );
                                    $displayuserheader = false;
                                }
                                $this->get_group_field($fieldname);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            if ($countfieldname > 0 ) {
                $button = [];
                $button[] = $mform->createElement(
                    'submit',
                    'form_plugin_userfilter_submit',
                    $this->dof->get_string('form_plugin_userfilter_submit', 'achievements')
                );

                $button[] = $mform->createElement(
                    'cancel',
                    'form_plugin_userfilter_cancel',
                    $this->dof->get_string('form_plugin_userfilter_cancel', 'achievements')
                );
                $mform->addGroup($button, 'form_plugin_userfilter_button', '', '', false);
            }
        }

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    public function hook_definition($func)
    {
        $func($this->_form);
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = [];

        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    function set_data($default_values)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        $isemptyfilter = true;

        if( ! empty($this->addvars['defaultachievementcat']) )
        {
            $mform->setDefault('achievement_category', $this->addvars['defaultachievementcat']);
            $isemptyfilter = false;
        }
        $filtersearchparams = $this->filtersearchparams;

        if ( ! empty($this->fields) &&  ! empty($filtersearchparams) )
        {// Указаны пользовательские поля
            foreach ( $this->fields as $group => $groupfields )
            {// Обработка каждой группы полей
                switch ( $group )
                {
                    // Группа полей профиля
                    case 'userprofilefields':
                        if ( ! empty($groupfields) )
                        {
                            // Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $mform->setDefault($groupfieldname, $filtersearchparams[$groupfieldname]);
                                    $isemptyfilter = false;
                                }
                            }
                        }
                        break;
                    // Группа пользовательских полей
                    case 'customfields' :
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $mform->setDefault($groupfieldname, $filtersearchparams[$groupfieldname]);
                                    $isemptyfilter = false;
                                }
                            }
                        }
                        break;
                        // Поля достижения
                    case 'achievementfields' :
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                $fieldname = 'achievement_'.$groupfieldname;
                                if ( isset($filtersearchparams[$fieldname]) )
                                {// Есть данные по фильтру
                                    $mform->setDefault($fieldname, $filtersearchparams[$fieldname]);
                                    $isemptyfilter = false;
                                } else
                                {// Поиск полей
                                    foreach ( $filtersearchparams as $fullfieldname => $data )
                                    {
                                        $name = $fullfieldname;
                                        if ( preg_match('/'.$fieldname.'/', $name) )
                                        {// Поле найдено
                                            // Получение значения
                                            $name = preg_replace('/'.$fieldname.'_/', '', $name);
                                            if( isset($filtersearchparams[$fullfieldname]) )
                                            {
                                                $value = $filtersearchparams[$fullfieldname];
                                            } else
                                            {
                                                $value = NULL;
                                            }
                                            $mform->setDefault($fieldname.'['.$name.']', $value);
                                            $isemptyfilter = false;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'groupfields':
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $mform->setDefault($groupfieldname, $filtersearchparams[$groupfieldname]);
                                    $isemptyfilter = false;
                                }
                            }
                        }
                        break;
                }
            }
            if ( $isemptyfilter )
            {// Фильтр не заполнен
                $mform->setExpanded('form_filter_title', false);
            } else
            {// Фильтр заполнен
                $mform->setExpanded('form_filter_title', true);
            }
        }

        // Заполняем форму данными
        parent::set_data($default_values);
    }

    /**
     * Сформировать блок с чекбоксами по пользовательскому полю
     *
     * @param $customfieldshortname - Идентификатор поля
     *
     * @return void
     */
    protected function get_customfield_checkblock($customfieldshortname)
    {
        $mform =& $this->_form;

        $userfield = $this->dof->modlib('ama')->user(false)->get_user_custom_field($customfieldshortname);
        $group = [];
        if ( ! empty($userfield) )
        {
            $options = $this->dof->modlib('ama')->user(false)->get_user_custom_field_options($customfieldshortname);
            if ( ! empty($options) )
            {
                foreach ( $options as $id => $name )
                {
                    if ( $name == '' )
                    {
                        continue;
                    }
                    $group[] = $mform->createElement(
                            'checkbox',
                            $id,
                            '',
                            $name
                    );
                }
            }
        }

        // Заголовок формы
        $mform->addElement(
                'header',
                $customfieldshortname.'_header',
                $userfield->name
        );
        $mform->addGroup($group, $customfieldshortname, '', '<br/>');
    }

    /**
     * Сформировать фильтр дополнительного поля пользователя
     *
     * @param $customfieldshortname - Идентификатор поля
     *
     * @return void
     */
    protected function get_profileuserfield_field($profileuserfieldname)
    {
        $mform =& $this->_form;

        switch($profileuserfieldname)
        {
            case 'lastname':
                $mform->addElement(
                    'text',
                    'lastname',
                    $this->dof->get_string('userprofilefield_lastname', 'achievements')
                );
                $mform->setType('lastname', PARAM_TEXT);
                break;
            case 'firstname':
                $mform->addElement(
                    'text',
                    'firstname',
                    $this->dof->get_string('userprofilefield_firstname', 'achievements')
                );
                $mform->setType('firstname', PARAM_TEXT);
                break;
            case 'middlename':
                $mform->addElement(
                    'text',
                    'middlename',
                    $this->dof->get_string('userprofilefield_middlename', 'achievements')
                );
                $mform->setType('middlename', PARAM_TEXT);
                break;
            default: break;
        }

    }

    /**
     * Сформировать фильтр дополнительного поля пользователя
     *
     * @param $customfieldshortname - Идентификатор поля
     *
     * @return void
     */
    protected function get_customfield_field($customfieldshortname)
    {
        $mform =& $this->_form;
        $field = $this->dof->modlib('ama')->user(false)->get_user_custom_field($customfieldshortname);
        switch ( $field->datatype )
        {
            // Выпадающий список
            case 'menu' :
                if ( isset($field->param1) )
                {
                    $options = explode("\n", $field->param1);
                } else {
                    $options = [];
                }
                $selectoptions = ['' => $this->dof->get_string('filter_form_not_set', 'achievements')];
                foreach ( $options as $key => $option )
                {
                    $selectoptions[$key] = format_string($option);
                }
                $mform->addElement('select', $field->shortname, format_string($field->name), $selectoptions);
                break;
            // Переключатель
            case 'checkbox' :
                $selectoptions = [
                    '' => $this->dof->get_string('filter_form_not_set', 'achievements'),
                    1 => $this->dof->get_string('filter_form_yes', 'achievements'),
                    0 => $this->dof->get_string('filter_form_no', 'achievements'),
                ];
                $mform->addElement('select', $field->shortname, format_string($field->name), $selectoptions);
                break;
            // Текстовое поле
            case 'text' :
                $mform->addElement('text', $field->shortname, format_string($field->name));
                $mform->setType($field->shortname, PARAM_TEXT);
                break;
        }
    }

    /**
     * Добавляет в форму поля для фильтрации по группам
     * @param unknown $groupname
     */
    protected function get_group_field($groupname)
    {
        $mform =& $this->_form;
        $agroups = $agroupsselect = [];
        switch($groupname)
        {
            // Для фильтрации по академичским группам деканата добавим поле типа autocomplete
            case 'agroup':
                // В выорку попадут группы в статусе "Обучается" из текущего подразделения
                $filter = ['status' => 'active', 'departmentid' => $this->departmentid];
                $agroups = $this->dof->storage('agroups')->get_agroups_by_filter($filter, ['strict_filter' => true]);
                if( ! empty($agroups) )
                {
                    foreach($agroups as $agroup)
                    {
                        $agroupsselect[$agroup->id] = $agroup->name;
                    }
                }
                $mform->addElement(
                    'autocomplete',
                    'agroup',
                    $this->dof->get_string('filter_form_agroup', 'achievements'),
                    $agroupsselect,
                    [
                        'multiple' => 'multiple',
                        'noselectionstring' => $this->dof->get_string('for_all_agroups', 'achievements')
                    ]
                );
                break;
            default:
                break;
        }
    }


    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_cancelled() )
        {// Отмена фильтра
            // Формирование url
            $url = $_SERVER['PHP_SELF'];
            $url = preg_replace("/.*achievements/", '', $url);
            $link = $this->dof->url_im('achievements', $url, $this->addvars);
            redirect($link);
        }

        if ( $formdata = $this->get_data() )
        {// Генерация URL фильтра
            if ( ! empty($this->fields) )
            {// Указаны поля фильтрации
                $filtersearch = [];
                foreach ( $this->fields as $group => $groupfields )
                {// Обработка каждой группы полей
                    switch ( $group )
                    {
                        // Группа полей профиля
                        case 'userprofilefields' :
                            if ( ! empty($groupfields) )
                            {// Есть поля в группе
                                foreach ( $groupfields as $groupfieldname => $groupfielddata )
                                {// Обработка каждого поля
                                    if ( isset($formdata->$groupfieldname) && $formdata->$groupfieldname !== '' )
                                    {// Указана фильтрация по полю
                                        // Добавление GET параметров
                                        $filtersearch[$groupfieldname] = (string)$formdata->$groupfieldname;
                                    }
                                }
                            }
                            break;
                        // Группа пользовательских полей
                        case 'customfields' :
                            if ( ! empty($groupfields) )
                            {// Есть поля в группе
                                foreach ( $groupfields as $groupfieldname => $groupfielddata )
                                {// Обработка каждого поля
                                    if ( isset($formdata->$groupfieldname) && $formdata->$groupfieldname !== '' )
                                    {// Указана фильтрация по полю
                                        // Добавление GET параметров
                                        $filtersearch[$groupfieldname] = (string)$formdata->$groupfieldname;
                                    }
                                }
                            }
                            break;
                        // Поля достижения
                        case 'achievementfields' :
                            if ( ! empty($groupfields) )
                            {// Есть поля в группе
                                foreach ( $groupfields as $groupfieldname => $groupfielddata )
                                {// Обработка каждого поля
                                    $formfieldname = 'achievement_'.$groupfieldname;
                                    if ( isset($formdata->$formfieldname) && $formdata->$formfieldname !== '' )
                                    {// Указана фильтрация по полю
                                        $advanced = false;
                                        if ( is_array($formdata->$formfieldname) )
                                        {
                                            foreach ( $formdata->$formfieldname as $name => $value )
                                            {
                                                if ( $value != 1 || ! is_int($name) )
                                                {// Не чек-бокс
                                                    $advanced = true;
                                                }
                                            }
                                        } else
                                        {// Добавление
                                            $advanced = true;
                                        }

                                        if ( $advanced )
                                        {// Формирование каждого значения отдельно
                                            if ( is_array($formdata->$formfieldname) )
                                            {
                                                foreach ( $formdata->$formfieldname as $name => $value )
                                                {
                                                    if ( empty($value) )
                                                    {
                                                        continue;
                                                    }
                                                    $keyname = $formfieldname.'_'.$name;
                                                    $filtersearch[$keyname] = $value;
                                                }
                                            } else
                                            {
                                                $filtersearch[$formfieldname] = $formdata->$formfieldname;
                                            }
                                        } else
                                        {// Стандартное формирование
                                            // Добавление GET параметров
                                            $data = array_keys($formdata->$formfieldname);
                                            $filtersearch[$formfieldname] = implode(',',$data);
                                        }
                                    }
                                }
                            }
                            break;
                        case 'groupfields' :
                            if ( ! empty($groupfields) )
                            {// Есть поля в группе
                                foreach ( $groupfields as $groupfieldname => $groupfielddata )
                                {// Обработка каждого поля
                                    if ( isset($formdata->$groupfieldname) && ! empty($formdata->$groupfieldname) )
                                    {// Указана фильтрация по полю
                                        // Добавление GET параметров
                                        $filtersearch[$groupfieldname] = implode(',', $formdata->$groupfieldname);
                                    }
                                }
                            }
                            break;
                    }
                }
            }
            if( ! empty($filtersearch) )
            {
                $this->addvars['filter'] = json_encode($filtersearch);
            }
            if( ! empty($formdata->form_plugin_userfilter_export) )
            {
                $this->addvars['format'] = 'xls';
            }
            // Формирование url
            $url = $_SERVER['PHP_SELF'];
            $url = preg_replace("/.*achievements/", '', $url);
            $link = $this->dof->url_im('achievements', $url, $this->addvars);
            redirect($link);
        } else
        {// Обработка фильтрации
            $this->set_data([]);
        }
    }

    public function get_filter()
    {
        global $DB;
        $filter = [];
        $filtersearchparams = $this->filtersearchparams;

        if ( ! empty($this->fields) && ! empty($filtersearchparams) )
        {// Указаны пользовательские поля
            foreach ( $this->fields as $group => $groupfields )
            {// Обработка каждой группы полей
                switch ( $group )
                {
                    // Группа полей профиля
                    case 'userprofilefields' :
                        $filter['user_userprofilefields'] = [];
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $filter['user_userprofilefields'][$groupfieldname] = $filtersearchparams[$groupfieldname];
                                }
                            }
                        }
                        break;
                    // Группа пользовательских полей
                    case 'customfields' :
                        $filter['user_customfields'] = [];
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $values = explode(',', $filtersearchparams[$groupfieldname]);
                                    $filter['user_customfields'][$groupfieldname] = $values;
                                }
                            }
                        }
                        break;
                        // Поля достижения
                    case 'achievementfields' :
                        $filter['achievementins'] = [];
                        if(!empty($this->addvars['defaultachievementcat']))
                        {
                            $filter['achievementins']['category'] = [$this->addvars['defaultachievementcat']];
                        }
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                $fieldname = 'achievement_'.$groupfieldname;
                                if ( isset($filtersearchparams[$fieldname]) )
                                {// Есть данные по фильтру
                                    $values = explode(',', $filtersearchparams[$fieldname]);
                                    $filter['achievementins'][$groupfieldname] = $values;
                                } else
                                {// Поиск полей
                                    foreach ( $filtersearchparams as $fullfieldname => $data )
                                    {
                                        $name = $fullfieldname;
                                        if ( preg_match('/'.$fieldname.'/', $name) )
                                        {// Поле найдено
                                            // Получение значения
                                            $name = preg_replace('/'.$fieldname.'_/', '', $name);
                                            $name = $groupfieldname.'_'.$name;
                                            if( isset($filtersearchparams[$fullfieldname]) )
                                            {
                                                $value = $filtersearchparams[$fullfieldname];
                                            } else
                                            {
                                                $value = NULL;
                                            }
                                            $filter['achievementins'][$name] = $value;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'groupfields' :
                        $filter['user_groupfields'] = [];
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $values = explode(',', $filtersearchparams[$groupfieldname]);
                                    $filter['user_groupfields'][$groupfieldname] = $values;
                                }
                            }
                        }
                        break;
                }
            }
        }
        $filterdata = [];

        $userids = NULL;
        if ( ! empty($filter['user_userprofilefields']) )
        {// Поиск пользователей по полям профиля
            $searchobj = new stdClass();
            foreach ( $filter['user_userprofilefields'] as $userprofilefieldname => $userprofilefieldvalue )
            {// Формирование массива значений, по которым производится фильтрация
                switch($userprofilefieldname)
                {
                    case 'lastname':
                        $searchobj->lastname = $userprofilefieldvalue;
                        break;
                    case 'firstname':
                        $searchobj->firstname = $userprofilefieldvalue;
                        break;
                    case 'middlename':
                        $searchobj->middlename = $userprofilefieldvalue;
                        break;
                    default: break;
                }
            }
            $searchedusers = $this->dof->modlib('ama')->user(false)->search($searchobj);

            if( $searchedusers == false )
            {
                $searchedusers = [];
            }

            if ( is_null($userids) )
            {// Перенос всех студентов
                $userids = array_keys($searchedusers);
            } else
            {
                if ( ! empty($userids) )
                {// Пользователи еще есть, продолжить фильтрацию
                    $userids = array_intersect($userids, array_keys($searchedusers));
                }
            }
        }

        if ( isset($filter['user_customfields']) )
        {
            if ( ! empty($filter['user_customfields']) )
            {// Есть поля, по которым указана фильтрация
                foreach ( $filter['user_customfields'] as $customfieldshortname => $custonfieldvaluesids )
                {// Формирование массива значений, по которым производится фильтрация
                    if ( is_array($custonfieldvaluesids) && ! empty($custonfieldvaluesids) )
                    {// Есть значения по полю
                        $fieldusers = [];
                        // Добавление пользователей в соответствии с переданными значениями
                        foreach ( $custonfieldvaluesids as $custonfieldvalueid )
                        {
                            // Получение пользователей, которые имеют такое же значние поля
                            $addusers = (array)$this->dof->modlib('ama')->user(false)->
                                get_userids_by_customfield_value($customfieldshortname, $custonfieldvalueid);
                            $fieldusers = array_merge($fieldusers, $addusers);
                        }
                        // Получение уникальных значений
                        $fieldusers = array_unique($fieldusers);
                        if ( is_null($userids) )
                        {// Перенос всех студентов
                            $userids = $fieldusers;
                        } else
                        {
                            if ( ! empty($userids) )
                            {// Пользователи еще есть, продолжить фильтрацию
                                $userids = array_intersect($userids, $fieldusers);
                            }
                        }
                    }
                }
            }
        }

        if ( ! is_null($userids) )
        {
            // Получение пользователей по ID пользоватееля
            $persons = $this->dof->storage('persons')->get_persons_by_userids($userids);
            $filterdata['persons'] = $persons;
        }

        if( isset($filter['user_groupfields']) )
        {// Если требуется фильтрация по группам
            foreach($filter['user_groupfields'] as $groupfield => $values)
            {
                switch($groupfield)
                {
                    // Фильтрация по академическим группам деканата
                    case 'agroup':
                        // Получим подписки по переданным группам
                        $psbcs = $this->dof->storage('programmsbcs')->get_programmsbcs_by_options(['agroupids' => $values]);
                        if( ! empty($psbcs) )
                        {// Если подписки нашлись
                            list($insql, $params) = $DB->get_in_or_equal(array_keys($psbcs));
                            $sql = 'SELECT p.*
                                    FROM {block_dof_s_persons} p
                                    JOIN {block_dof_s_contracts} c
                                    ON p.id=c.studentid
                                    JOIN {block_dof_s_programmsbcs} pbcs
                                    ON c.id=pbcs.contractid
                                    WHERE pbcs.id ' . $insql;
                            // Получим персоны этих подписок
                            $agrouppersons = $this->dof->storage('persons')->get_records_sql($sql, $params);
                            if( ! empty($agrouppersons) )
                            {// Если персоны нашлись
                                if( ! isset($filterdata['persons']) )
                                {// Если другой фильтрации не требовалось - передадим полученный список дальше
                                    $filterdata['persons'] = $agrouppersons;
                                } else
                                {// Если применялись другие фильтры и есть сформированный список персон
                                    if( count($filterdata['persons']) > count($agrouppersons) )
                                    {// Смотрим какой массив больше
                                        foreach($agrouppersons as $agroupperson)
                                        {// Находим пересечение, все остальное удаляем
                                            if( ! array_key_exists($agroupperson->id, $filterdata['persons']) )
                                            {
                                                unset($filterdata['persons'][$agroupperson->id]);
                                            }
                                        }
                                    } else
                                    {// Аналогичная процедура с пробегом по другому массиву
                                        foreach($filterdata['persons'] as $person)
                                        {
                                            if( ! array_key_exists($person->id, $agrouppersons) )
                                            {
                                                unset($filterdata['persons'][$person->id]);
                                            }
                                        }
                                    }
                                }
                            } else
                            {// Если не нашлись персоны
                                if( isset($filterdata['persons']) )
                                {// И были отобраны персоны по другим фильтрам - значит пересечение пустое
                                    $filterdata['persons'] = [];
                                }
                            }
                        } else
                        {// Если подписки не нашлись
                            if( isset($filterdata['persons']) )
                            {// И были отобраны персоны по другим фильтрам - значит пересечение пустое
                                $filterdata['persons'] = [];
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        if ( isset($filter['achievementins']) )
        {
            $achievements = NULL;
            if ( ! empty($filter['achievementins']) )
            {// Есть поля, по которым указана фильтрация
                $additional_filter = [];
                if ( isset($filterdata['persons']) )
                {
                    $filter['achievementins']['personids'] = array_keys($filterdata['persons']);
                }
                $statuses_real = $this->dof->workflow('achievementins')->get_meta_list('real');
                $filter['achievementins']['statuses'] = array_keys($statuses_real);
                $achievements = $this->dof->storage('achievementins')->get_filtered_data($filter['achievementins']);
//                 if ( isset($filter['achievementins']['createdate_from']) ||
//                         isset($filter['achievementins']['createdate_to']) ||
//                         isset($filter['achievementins']['category']) )
//                 {
//                     $filterdata['persons'] = [];
//                     if ( ! empty($achievements) )
//                     {
//                         foreach ( $achievements as $row )
//                         {
//                             $filterdata['persons'][$row->userid] = $row->userid;
//                         }
//                     }
//                 }
            }
            if ( ! is_null($achievements) )
            {
                $filterdata['achievementins'] = $achievements;
            }
        }
        return $filterdata;
    }

    public function add_get_params(&$addvars)
    {
        $filtersearchparams = $this->filtersearchparams;

        $filtersearch = [];

        if ( ! empty($this->fields) && ! empty($filtersearchparams))
        {// Указаны пользовательские поля
            foreach ( $this->fields as $group => $groupfields )
            {// Обработка каждой группы полей
                switch ( $group )
                {
                    // Группа полей профиля
                    case 'userprofilefields' :
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $filtersearch[$groupfieldname] = $filtersearchparams[$groupfieldname];
                                }
                            }
                        }
                        break;
                        // Группа пользовательских полей
                    case 'customfields' :
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $filtersearch[$groupfieldname] = $filtersearchparams[$groupfieldname];
                                }
                            }
                        }
                        break;
                        // Поля достижения
                    case 'achievementfields' :
                        $filter['achievementins'] = [];
                        if( ! empty($this->addvars['defaultachievementcat']) )
                        {
                            $filtersearch['category'] = $this->addvars['defaultachievementcat'];
                        }
                        if (!array_key_exists('category', $groupfields))
                        {// если нет поля категории
                            $filtersearch['achievement_category'] = $filtersearchparams['achievement_category'];
                        }
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                $fieldname = 'achievement_'.$groupfieldname;
                                if ( isset($filtersearchparams[$fieldname]) )
                                {// Есть данные по фильтру
                                    $filtersearch[$fieldname] = $filtersearchparams[$fieldname];
                                } else
                                {// Поиск полей
                                    foreach ( $filtersearchparams as $fullfieldname => $data )
                                    {
                                        $name = $fullfieldname;
                                        if ( preg_match('/'.$fieldname.'/', $name) )
                                        {// Поле найдено
                                            // Получение значения
                                            $name = preg_replace('/'.$fieldname.'_/', '', $name);
                                            $name = $groupfieldname.'_'.$name;
                                            if( isset($filtersearchparams[$fullfieldname]) )
                                            {
                                                $value = $filtersearchparams[$fullfieldname];
                                            } else
                                            {
                                                $value = NULL;
                                            }
                                            $filtersearch[$fullfieldname] = $value;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'groupfields' :
                        if ( ! empty($groupfields) )
                        {// Есть поля в группе
                            foreach ( $groupfields as $groupfieldname => $groupfielddata )
                            {// Обработка каждого поля
                                if ( isset($filtersearchparams[$groupfieldname]) )
                                {// Есть данные по фильтру
                                    $filtersearch[$groupfieldname] = $filtersearchparams[$groupfieldname];
                                }
                            }
                        }
                        break;
                }
            }
            $addvars['filter'] = json_encode($filtersearch);
        }
    }
}
?>