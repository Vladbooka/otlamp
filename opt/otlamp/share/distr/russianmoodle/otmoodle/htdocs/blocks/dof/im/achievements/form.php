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
 * Классы форм
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
 * Форма создания/редактирования разделов
 */
class dof_im_achievementcats_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID раздела
     */
    protected $id = 0;

    /**
     * @var $id - ID подразделения
     */
    protected $departmentid = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * Экземпляры классов полей formbuilder
     *
     * @var array
     */
    protected $fieldsinstances = [];
    /**
     * Наличие у текущего раздела настроек
     *
     * @var boolean
     */
    protected $hasconfig = false;

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->departmentid = $this->_customdata->departmentid;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);

        // Заголовок формы
        $mform->addElement(
                'header',
                'form_achievementcats_edit_title',
                $this->dof->get_string('form_achievementcats_edit_title', 'achievements')
        );

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
                'static',
                'hidden',
                ''
        );

        // Название
        $mform->addElement(
                'text',
                'name',
                $this->dof->get_string('form_achievementcats_edit_name', 'achievements')
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');

        // Родитель
        $list = [];
        $list[0] = $this->dof->get_string('form_achievementcats_edit_parentid_top', 'achievements');
        $list = $list + $this->get_parent_list();
        $mform->addElement(
                'select',
                'parentid',
                $this->dof->get_string('form_achievementcats_edit_parentid', 'achievements'),
                $list
        );
        $mform->setDefault('parentid', $this->addvars['parentcat']);

        // Влияние на рейтинг
        $mform->addElement(
            'advcheckbox',
            'affectrating',
            $this->dof->get_string('form_achievementcats_edit_affectrating', 'achievements'),
            $this->dof->get_string('form_achievementcats_edit_affectrating_desc', 'achievements')
        );
        $mform->setDefault('affectrating', '1');

        // Заголовок настроек по дополнительным полям в таблице панели управления пользователями портфолио
        $mform->addElement(
            'header',
            'form_fieldssettings_header',
            $this->dof->get_string('form_achievementcats_fieldssettings_header_title', 'achievements')
            );
        $mform->setExpanded('form_fieldssettings_header', true);

        if (!empty($this->id)) {
            // Получение настройки фильтра
            $conditions['plugintype'] = 'storage';
            $conditions['plugincode'] = 'achievementcats';
            $conditions['objectid'] = $this->id;
            $this->hasconfig = (bool)$this->dof->storage('cov')->get_records($conditions);
            // Добавим в форму поля настройки панели управления пользователями только если имеется значение насторйки у
            // текущей категории или ее родителей
            if ($this->dof->storage('achievementcats')->get_config_value(
                $this->id, 'confirmed') !== false) {
                // Поля настроек панели управления пользователями портфолио
                $this->define_custom_fields($mform);
            }
        }
        if ($this->hasconfig) {
            $mform->addElement('submit', 'submitdelete', $this->dof->get_string('form_achievementcats_edit_submit_delete', 'achievements'));
        } else {
            $mform->addElement('submit', 'submitadd', $this->dof->get_string('form_achievementcats_edit_submit_add', 'achievements'));
        }
        // Заголовок сохранения настроек
        $mform->addElement(
            'header',
            'form_save_header',
            $this->dof->get_string('form_achievementcats_save_title', 'achievements')
            );
        $mform->setExpanded('form_save_header', true);
        $group = [];
        $group[] = $mform->createElement('submit', 'submit', $this->dof->get_string('form_achievementcats_edit_submit', 'achievements'));
        $group[] = $mform->createElement('submit', 'submitclose', $this->dof->get_string('form_achievementcats_edit_submit_close', 'achievements'));
        $mform->addGroup($group, 'submit', '', '');

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Добавляет в форму дополнительные поля настроек панели управления пользователями портфолио
     *
     * @param MoodleQuickForm $mform
     */
    private function define_custom_fields(&$mform)
    {
        // Базовые настройки для всех полей formbuilder
        $customfieldgeneral = new stdClass();
        $customfieldgeneral->departmentid = $this->departmentid;
        $customfieldgeneral->linkpcode = 'achievementcats';
        $customfieldgeneral->name = '';

        if (!$this->hasconfig) {
            // Собщение о зафриженой форме которая отображает настройки вышестоящих категорий
            $mform->addElement(
                'static',
                'form_fieldssettings_description',
                '',
                $this->dof->get_string('table_moderation_description', 'achievements')
                );
        }
        // Количество подтвержденных достижений
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_confirmed', 'achievements');
        $customfield->code = 'confirmed';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Количество неподтвержденных достижений
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_unconfirmed', 'achievements');
        $customfield->code = 'unconfirmed';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Количество одобренных целей
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_approved', 'achievements');
        $customfield->code = 'approved';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Количество неодобренных целей
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_notapproved', 'achievements');
        $customfield->code = 'notapproved';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Дата последней активности
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_lastcreatedtime', 'achievements');
        $customfield->code = 'lastcreatedtime';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Дата последней проверки
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_lastchecktime', 'achievements');
        $customfield->code = 'lastchecktime';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Итого по всем разделам
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_sumalluserpoints', 'achievements');
        $customfield->code = 'sumalluserpoints';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Итого по выбранным разделам - все
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_sumalluserpointsselectedcats', 'achievements');
        $customfield->code = 'sumalluserpointsselectedcats';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Итого по выбранным разделам - в рейтинге
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_sumpointsselectedcats', 'achievements');
        $customfield->code = 'sumpointsselectedcats';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Выбор раздела
        $mform->addElement(
            'static',
            'categorieslist_title',
            '',
            dof_html_writer::tag(
                'h4',
                $this->dof->get_string('table_moderation_categorieslist', 'achievements')
                )
            . $this->dof->get_string('table_moderation_categorieslist_desc', 'achievements')
            );
        $customfield = clone $customfieldgeneral;
        $customfield->code = 'categorieslist';
        $customfield->type = 'autocomplete';
        $customfield->defaultvalue = '';
        $customfield->required = 0;
        $customfield->options = serialize([
            'autocompleteoptions' => $this->dof->storage('achievementcats')->get_categories_list(
                $this->id, 0, ['metalist' => 'active']
                ),
            'additional_options' => ['multiple' => 1]

        ]);
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);
        // Дополнительные поля отображаемые по разделам достижений
        $mform->addElement(
            'static',
            'fieldssettings_title',
            '',
            dof_html_writer::tag(
                'h4',
                $this->dof->get_string('form_achievementcats_fieldssettings_title', 'achievements')
                )
            );
        // Сумма баллов учитываемая в рейтинге
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_points', 'achievements');
        $customfield->code = 'fieldssettings_points';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Общая сумма баллов
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_alluserpoints', 'achievements');
        $customfield->code = 'fieldssettings_alluserpoints';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);

        // Учитывать баллы из дочерних категорий при расчете суммы по выбранным категориям
        $customfield = clone $customfieldgeneral;
        $customfield->text = $this->dof->get_string('table_moderation_childrenamount', 'achievements');
        $customfield->code = 'fieldssettings_childrenamount';
        $customfield->type = 'checkbox';
        $customfield->defaultvalue = 0;
        // Добавим поле в форму при помощи formbuilder
        $this->fieldsinstances[$customfield->code] = $this->dof->modlib('formbuilder')->add_customfield_to_form(
            $mform, $customfield, $this->id, !$this->hasconfig);
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

        if ( ! trim($data['name']) )
        {// Если пустое имя
            $errors['name'] = $this->dof->get_string('error_form_achievementcats_empty_name', 'achievements');
        }

        // Проверим существование родителя
        if ( isset($data['parentid']) && $data['parentid'] > 0 )
        {
            $parent = $this->dof->storage('achievementcats')->is_exists($data['parentid']);
            if ( empty($parent) )
            {// Родитель не найден
                $errors['parentid'] = $this->dof->get_string(
                    'error_form_achievementcats_edit_parent_not_found',
                    'achievements'
                );
            }
        }

        // Проверим существование подразделения
        if ( isset($data['departmentid']) )
        {
            $dep = $this->dof->storage('departments')->is_exists($data['departmentid']);
            if ( empty($dep) )
            {// Подразделение не найдено
                $errors['hidden'] = $this->dof->get_string(
                        'error_form_achievementcats_edit_department_not_found',
                        'achievements'
                );
            }
        }

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

        if (!empty($this->id)) {
            // Заполнение значениями
            $item = $this->dof->storage('achievementcats')->get($this->id);
            if (!empty($item)) {
                // Раздел найден
                $mform->setDefault('name', $item->name);
                $mform->setDefault('parentid', $item->parentid);
                $mform->setDefault('departmentid', $item->departmentid);
                $mform->setDefault('affectrating', $item->affectrating);
                if (!empty($this->id) && !$this->hasconfig) {
                    // Количество подтвержденных достижений
                    $mform->setDefault(
                        'confirmed',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'confirmed')
                        );
                    // Количество неподтвержденных достижений
                    $mform->setDefault(
                        'unconfirmed',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'unconfirmed')
                        );
                    // Количество одобренных целей
                    $mform->setDefault(
                        'approved',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'approved')
                        );
                    // Количество неодобренных целей
                    $mform->setDefault(
                        'notapproved',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'notapproved')
                        );
                    // Дата последней активности
                    $mform->setDefault(
                        'lastcreatedtime',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'lastcreatedtime')
                        );
                    // Дата последней проверки
                    $mform->setDefault(
                        'lastchecktime',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'lastchecktime')
                        );
                    // Итого по всем разделам
                    $mform->setDefault(
                        'sumalluserpoints',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'sumalluserpoints')
                        );
                    // Итого по выбранным разделам - все
                    $mform->setDefault(
                        'sumalluserpointsselectedcats',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'sumalluserpointsselectedcats')
                        );
                    // Итого по выбранным разделам - в рейтинге
                    $mform->setDefault(
                        'sumpointsselectedcats',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'sumpointsselectedcats')
                        );
                    // Сумма баллов учитываемая в рейтинге
                    $mform->setDefault(
                        'fieldssettings_points',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'fieldssettings_points')
                        );
                    // Общая сумма баллов
                    $mform->setDefault(
                        'fieldssettings_alluserpoints',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'fieldssettings_alluserpoints')
                        );
                    // Учитывать баллы из дочерних категорий при расчете суммы по выбранным категориям
                    $mform->setDefault(
                        'fieldssettings_childrenamount',
                        $this->dof->storage('achievementcats')->get_config_value(
                            $this->id, 'fieldssettings_childrenamount')
                        );
                    // Выбор раздела
                    if ($result = $this->dof->im('achievements')->filtering_config_categories($this->id)) {
                        $result = implode(', ', $result);
                        $mform->setDefault('categorieslist', $result);
                    }
                }
            } else {
                // Раздел не найден
                $mform->setDefault('id', 0);
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
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            if (isset($formdata->submitdelete)) {
                if (!empty($formdata->id)) {
                    $conditions['plugintype'] = 'storage';
                    $conditions['plugincode'] = 'achievementcats';
                    $conditions['objectid'] = $formdata->id;
                    $this->dof->storage('cov')->delete_records($conditions);
                } else {
                    $this->addvars['success'] = 0;
                }
                // Очистим кеш категорий
                $this->dof->storage('achievementcats')->del_categories_config_cache();
                redirect($this->dof->url_im('achievements', '/edit_category.php', $this->addvars));
            } else {
                $this->addvars['success'] = 1;
                // Сохраним компетенцию
                $this->id = $this->dof->storage('achievementcats')->save($formdata);
                if (!$this->id) {
                    // Ошибки
                    $this->addvars['success'] = 0;
                    redirect($this->dof->url_im('achievements', '/edit_category.php', $this->addvars));
                }
                // Если поля не определены в definitions определим из сейчас,
                // это требуется для установки значений по умолчанию
                if (empty($this->fieldsinstances)) {
                    $this->define_custom_fields($this->_form);
                }
                // Сохраним дополнительные настройки по умолчанию
                foreach ($this->fieldsinstances as $fieldname => $fieldinstance) {
                    if (isset($formdata->submitadd) || (
                        (isset($formdata->submit['submitclose']) || isset($formdata->submit['submit']))
                        && $this->hasconfig
                        )
                    ) {
                        // Если нажата кнопка "добавить настройки"
                        if (!$this->hasconfig) {
                            $fieldvalue = $this->get_field_def_value($fieldname);
                        } else {
                            $fieldvalue = $formdata->{$fieldname};
                        }
                        try {
                            // Сохраним дополнительные настройки
                            $fieldinstance->save_data($this->id, $fieldvalue);
                        } catch (dof_storage_customfields_exception $e) {
                            $this->addvars['success'] = 0;
                        }
                    }
                }
                // Очистим кеш категорий
                $this->dof->storage('achievementcats')->del_categories_config_cache();
                if (isset($formdata->submitadd)) {
                    $this->addvars['id'] = $this->id;
                    redirect($this->dof->url_im('achievements', '/edit_category.php', $this->addvars));
                } elseif ($this->id && ($this->addvars['success'] == 1)) {
                    // Успешное сохранение
                    if (isset($formdata->submit['submitclose'])) {
                        // Сохранить и выйти в раздел
                        $this->addvars['сatsavesuccess'] = 1;
                        redirect($this->dof->url_im('achievements', '/admin_panel.php', $this->addvars));
                    } else {// Сохранить и остаться на странице
                        $this->addvars['success'] = 1;
                        $this->addvars['id'] = $this->id;
                        redirect($this->dof->url_im('achievements', '/edit_category.php', $this->addvars));
                    }
                } else {// Ошибки
                    $this->addvars['success'] = 0;
                    redirect($this->dof->url_im('achievements', '/edit_category.php', $this->addvars));
                }
            }
        }
    }

    /**
     * Метод возвращает значение поля панели управления пользователями портфолио
     * которое используется в качестве значения по умолчанию
     *
     * @param string $fieldname - имя поля настроек раздела
     * @return number|array
     */
    private function get_field_def_value($fieldname)
    {
        // Эти поля получат значение "выбран" по умолчанию что приведет к отображению
        // соответствующих колонок в таблице модуля портфолио
        // ранее когда отсутствовала возможность управлять отображаемыми колонками отображались только эти колонки,
        // теперь они используются в качестве значений по умолчанию.
        // если требуется поменять значения по умолчанию, то новую колонку нужно добавить в этот список.
        $fieldsactivebydef = [
            'confirmed',// Количество подтвержденных достижений
            'unconfirmed',// Количество неподтвержденных достижений
            'approved',// Количество одобренных целей
            'notapproved',// Количество неодобренных целей
            'lastcreatedtime'// Дата последней активности
        ];
        // Выбор раздела
        if ($fieldname == 'categorieslist') {
            return array_keys($this->dof->im('achievements')->filtering_config_categories($this->id));
        }
        // Получаем значение настройки категории достижений
        // или ее ближайшего родителя у когорого настройка задана
        $fieldvalue = $this->dof->storage('achievementcats')->get_config_value(
            $this->id, $fieldname);
         // Установим значения по умолчанию для полей если у родителей настройки не заданы
        if (in_array($fieldname, $fieldsactivebydef)) {
            $fieldvalue = $fieldvalue === false ? 1 : $fieldvalue;
        } else {
            $fieldvalue = $fieldvalue === false ? 0 : $fieldvalue;
        }
        return $fieldvalue;
    }

    /**
     * Получить список родителей для поля
     *
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     *
     * @return array - Массив разделов
     */
    private function get_parent_list($id = 0, $level = 0)
    {
        $result = [];

        // Получим cписок дочерних элементов
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $parents = $this->dof->storage('achievementcats')->
            get_records(array('parentid' => $id, 'status' => $statuses), '', 'id, name');

        if ( ! empty($parents) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');
            foreach ( $parents as $skill )
            {
                if ( $skill->id == $this->id )
                {// Удалим текущий редактируемый элемент
                    continue;
                }
                // Сформируем элемент
                $result[$skill->id] = $shift.$skill->name;
                // Получим массид дочерних
                $childrens = $this->get_parent_list($skill->id, $level + 1);
                // Добавим к исходному
                $result = $result + $childrens;
            }
        }

        return $result;
    }
}

/**
 * Форма создания/редактирования шаблонов достижений
 */
class dof_im_achievements_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID раздела
     */
    protected $id = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var $id - ID подразделения
     */
    protected $departmentid = 0;

    protected function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        $this->departmentid = $this->_customdata->departmentid;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        $system_rating_enabled = $this->dof->storage('config')->
            get_config_value('system_rating_enabled', 'im', 'achievements', $this->departmentid);

        // Заголовок формы
        $mform->addElement(
                'header',
                'form_achievements_edit_title',
                $this->dof->get_string('form_achievements_edit_title', 'achievements')
        );

        // Название
        $mform->addElement(
                'text',
                'name',
                $this->dof->get_string('form_achievements_edit_name', 'achievements')
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');

        // Тип шаблона
        $list = $this->get_achievementtype_list();
        $mform->addElement(
                'select',
                'type',
                $this->dof->get_string('form_achievements_edit_type', 'achievements'),
                array_merge([0 => $this->dof->get_string('form_achievements_edit_type_none', 'achievements')], $list)
        );
        $mform->setType('type', PARAM_TEXT);
        $this->add_help('type', 'form_achievements_edit_type', 'achievements');

        // Раздел
        $list = (array)$this->dof->storage('achievementcats')->get_categories_list(0, 0, [
                'metalist' => 'real'
            ]);
        $mform->addElement(
                'select',
                'catid',
                $this->dof->get_string('form_achievements_edit_catid', 'achievements'),
                $list
        );
        $mform->setDefault('catid', $this->addvars['parentcat']);


        // Разрешено использовать в качестве цели
        $mform->addElement(
            'checkbox',
            'goalfirst',
            $this->dof->get_string('form_achievements_edit_goalfirst', 'achievements')//, '010'
        );


        // Цель требует одобрения
        $mform->addElement(
            'checkbox',
            'goalneedapproval',
            $this->dof->get_string('form_achievements_edit_goalneedapproval', 'achievements')//, '100'
        );
        // нельзя требовать одобрения цели, если шиблон нельзя использовать в качестве цели
        $mform->disabledIf(
            'goalneedapproval',
            'goalfirst',
            'notchecked'
        );

        // Резрешено добавлять достижение без предварительного формирования цели
        $mform->addElement(
            'checkbox',
            'achievementfirst',
            $this->dof->get_string('form_achievements_edit_achievementfirst', 'achievements')//, '001'
        );
        $mform->setDefault('achievementfirst', true);

        $this->add_help('achievementfirst', 'form_achievements_edit_achievementfirst', 'achievements');
        $mform->addElement('static', 'form_achievements_edit_confirmation_info', '',
                dof_html_writer::span(
                        $this->dof->get_string('form_achievements_edit_achievementfirst_confirmation_info', 'achievements'), '',
                        [
                            'style' => 'font-style:italic;'
                        ]));


        if ( empty($system_rating_enabled) )
        {// Подсистема рейтинга отключена
            // Баллы
            $mform->addElement(
                    'hidden',
                    'points',
                    0.0
                    );
            $mform->setType('points', PARAM_FLOAT);
        } else
        {// Подсистема рейтинга включена
            // Баллы
            $mform->addElement(
                    'text',
                    'points',
                    $this->dof->get_string('form_achievements_edit_points', 'achievements')
                    );
            $mform->setType('points', PARAM_FLOAT);
            $mform->setDefault('points', 0.0);
        }

        $group = [];
        $group[] = $mform->createElement('submit', 'submit', $this->dof->get_string('form_achievements_edit_submit', 'achievements'));
        $group[] = $mform->createElement('submit', 'submitclose', $this->dof->get_string('form_achievements_edit_submit_close', 'achievements'));
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
        $errors = [];

        if ( empty($data['type']) )
        {// неверный тип
            $errors['type'] = $this->dof->get_string('error_form_achievements_choose_type', 'achievements');
        }

        if ( ! trim($data['name']) )
        {// Если пустое имя
            $errors['name'] = $this->dof->get_string('error_form_achievements_empty_name', 'achievements');
        }

        // Проверим существование раздела
        if ( isset($data['catid']) && $data['catid'] > 0 )
        {
            $category = $this->dof->storage('achievementcats')->is_exists($data['catid']);
            if ( empty($category) )
            {// Раздел не найден
                $errors['catid'] = $this->dof->get_string(
                        'error_form_achievements_edit_category_not_found',
                        'achievements'
                );
            }
        } else
        {// Раздел не установлен
            $errors['catid'] = $this->dof->get_string(
                    'error_form_achievements_edit_category_not_set',
                    'achievements'
            );
        }

        $achievementfirst = ! empty($data['achievementfirst']);
        $goalfirst = ! empty($data['goalfirst']);
        $goalneedapproval = ! empty($data['goalneedapproval']);
        $knownscenario = $this->dof->storage('achievements')->is_known_scenario(
            $achievementfirst,
            $goalfirst,
            $goalneedapproval
        );
        if( ! $knownscenario )
        {
            $errors['goalfirst'] = $this->dof->get_string(
                'error_form_achievements_edit_unknown_scenario',
                'achievements'
            );
        }

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

        if ( ! empty($this->id) )
        {// Заполнение значениями
            $item = $this->dof->storage('achievements')->get($this->id);
            if ( ! empty($item) )
            {// Раздел найден
                $mform->setDefault('name', $item->name);
                $mform->setDefault('catid', $item->catid);
                $mform->setDefault('type', $item->type);
                $mform->setDefault('points', $item->points);

                $scenario = (int)$item->scenario;

                $mform->setDefault(
                    'achievementfirst',
                    $this->dof->storage('achievements')->is_achievement_add_allowed($scenario)
                );

                $statusgoaladd = $this->dof->storage('achievements')->is_goal_add_allowed($scenario);
                $mform->setDefault(
                    'goalfirst',
                    $statusgoaladd
                );
                $mform->setDefault(
                    'goalneedapproval',
                    $this->dof->storage('achievements')->is_approval_required($scenario)
                );

                $mform->disabledIf('type', 'id');
            } else
            {// Достижени не найдено
                $mform->setDefault('id', 0);
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
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            if ( ! empty($formdata->id) )
            {// Обновление объекта
                unset($formdata->type);
            }

            $achievementfirst = $goalfirst = $goalneedapproval = false;
            if( ! empty($formdata->achievementfirst) )
            {
                $achievementfirst = true;
                unset($formdata->achievementfirst);
            }
            if( ! empty($formdata->goalfirst) )
            {
                $goalfirst = true;
                unset($formdata->goalfirst);
            }
            if( ! empty($formdata->goalneedapproval) )
            {
                $goalneedapproval = true;
                unset($formdata->goalneedapproval);
            }
            $formdata->scenario = $this->dof->storage('achievements')->prepare_scenario_bitmask(
                $achievementfirst,
                $goalfirst,
                $goalneedapproval
            );

            // Сохранение
            $id = $this->dof->storage('achievements')->save($formdata);
            if ( $id )
            {// Успешное сохранение
                if ( isset($formdata->submit['submitclose']) )
                {// Сохранить и выйти в таблицу
                    $this->addvars['achsavesuccess'] = 1;
                    redirect($this->dof->url_im('achievements', '/admin_panel.php', $this->addvars));
                } else
                {// Сохранить и остаться на странице
                    $this->addvars['success'] = 1;
                    $this->addvars['id'] = $id;
                    redirect($this->dof->url_im('achievements', '/edit_achievement.php', $this->addvars));
                }
            } else
            {// Ошибки
                $this->addvars['success'] = 0;
                redirect($this->dof->url_im('achievements', '/edit_achievement.php', $this->addvars));
            }
        }
    }

    /**
     * Получить список типов шаблонов достижений для поля
     *
     * @return array - Массив типов
     */
    private function get_achievementtype_list()
    {
        $result = [];
        // Получим cписок типов
        $types = $this->dof->storage('achievements')->get_achievementtypes_list();
        if ( ! empty($types) )
        {
            foreach ( $types as $name => $type )
            {
                $result[$name] = $this->dof->get_string($type, 'achievements', null, 'storage');
            }
        }
        return $result;
    }

}

/**
 * Форма выбора шаблона достижения
 */
class dof_im_achievementins_select_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID текущего раздела
     */
    protected $id = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var $achievementownerid - идентификатор персоны, для которой планируется добавление достижения
     */
    protected $achievementownerid = 0;

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        $this->creatingachievementownerid = $this->_customdata->creatingachievementownerid;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        // Выбор шаблона достижения
        $options = new stdClass();
        $options->categories = [];
        $options->achievements = [];
        if( $this->dof->storage('achievementins')->is_access('create', $this->creatingachievementownerid) )
        {//нет права создавать цели вообще
            $options = $this->dof->storage('achievements')->get_achievementselect_list(
                0,
                0,
                [
                    'scenario' => [1,3,7]
                ]
            );
        }

        // Если доступных шаблонов нет - оставим форму пустой
        if (empty($options->achievements)) {
            return;
        }

        // заголовок формы
        $headertitle = $this->dof->get_string('form_achievementins_select_title', 'achievements');
        // Заголовок формы
        $mform->addElement(
            'header',
            'form_achievementins_select_title',
            $headertitle
            );
        $mform->setExpanded('form_achievementins_select_title', false);

        $select = $mform->addElement(
            'dof_hierselect',
            'form_achievementins_select',
            $this->dof->get_string('form_achievementins_select', 'achievements'),
            '',
            NULL,
            '<div class="col-12 px-0"></div>'
        );
        // Установка набора значений
        $categories = ['' => $this->dof->get_string('form_achievementins_select_not_set', 'achievements')] + array_reverse($options->categories, true);
        $achievements = ['' => ['' => $this->dof->get_string('form_achievementins_select_not_set', 'achievements')]] + array_reverse($options->achievements, true);
        $select->setOptions([$categories, $achievements]);

        $mform->addElement('submit', 'submit', $this->dof->get_string('form_achievementins_select_submit', 'achievements'));

        if ( empty($options->achievements) )
        {// если выбирать нечего, жестко очистим форму
            $elemname = get_class($this).'_title';
            if ( $mform->elementExists($elemname) )
            {
                $mform->removeElement($elemname);
            }
            $elemname = get_class($this).'_template';
            if ( $mform->elementExists($elemname) )
            {
                $mform->removeElement($elemname);
            }
            $elemname = get_class($this).'_submit';
            if ( $mform->elementExists($elemname) )
            {
                $mform->removeElement($elemname);
            }
        }

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
        $errors = [];

        // Проверим существование раздела
        if ( isset($data['form_achievementins_select'][0]) && $data['form_achievementins_select'][0] > 0 )
        {
            $exist = $this->dof->storage('achievementcats')->is_exists($data['form_achievementins_select'][0]);
            if ( empty($exist) )
            {// Раздел не найден
                $errors['form_achievementins_select'] = $this->dof->get_string(
                        'error_form_achievements_select_category_not_found',
                        'achievements'
                        );
            } else
            {
                if( ! $this->dof->storage('achievementcats')->is_access('use', $data['form_achievementins_select'][0]) )
                {
                    $errors['form_achievementins_select'] = $this->dof->get_string(
                            'error_form_achievements_select_category_access_denied',
                            'achievements'
                            );
                }
                // Проверим существование шаблона
                if ( isset($data['form_achievementins_select'][1]) && $data['form_achievementins_select'][1] > 0 )
                {
                    $achievement = $this->dof->storage('achievements')->is_exists($data['form_achievementins_select'][1]);
                    if ( empty($achievement) )
                    {// Шаблон не найден
                        $errors['form_achievementins_select'] = $this->dof->get_string(
                                'error_form_achievements_select_achievement_not_found',
                                'achievements'
                        );
                    } else
                    {// Шаблон найден

                        // Получение объекта достижения
                        $obj = $this->dof->storage('achievements')->object($data['form_achievementins_select'][1]);

                        // Проверка на возможность ручного добавления данного достижения
                        $manual_create_errors = $obj->
                        	manual_create($this->creatingachievementownerid);
                        if ( (bool)$manual_create_errors == true )
                        {// Ручное создание невозможно по причине указанных ошибок

                            $errors['form_achievementins_select'] = '';
                            // Выведем все ошибки
                            foreach ( $manual_create_errors as $error )
                            {
                                $errors['form_achievementins_select'] .= $error . '<br>';
                            }
                        }
                    }
                } else
                {// Раздел не установлен
                    $errors['form_achievementins_select'] = $this->dof->get_string(
                        'error_form_achievements_select_achievement_not_set',
                        'achievements'
                    );
                }
            }
        } else
        {// Раздел не установлен
            $errors['form_achievementins_select'] = $this->dof->get_string(
                    'error_form_achievements_select_category_not_set',
                    'achievements'
            );
        }

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

        if ( $mform->elementExists(get_class($this).'_template') )
        {
            if ( ! empty($this->id) )
            {// Заполнение значениями
                $category = $this->dof->storage('achievementcats')->is_exists($this->id);
                if ( ! empty($category) )
                {// Раздел найден
                    $mform->setDefault('form_achievementins_select', [$this->id, NULL]);
                }
            }

            // Блокаровка разделов по строковому ключу
            $categoriesselect = $mform->getElement('form_achievementins_select')->getElements()[0];
            if ( ! empty($categoriesselect->_options) )
            {// Опции определены
                foreach ( $categoriesselect->_options as &$option )
                {
                    if ( ! empty($option['attr']['value']) &&
                            substr($option['attr']['value'], 0, 1) == '0')
                    {// Раздел со строковым ключем - флаг блокировки
                        $option['attr']['disabled'] = 'disabled';
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
        if ( $this->is_submitted() AND confirm_sesskey() AND
             $this->is_validated() AND $formdata = $this->get_data()
           )
        {// Редирект на страницу добавления достижения
            $this->addvars['aid'] = $formdata->form_achievementins_select[1];
            unset($this->addvars['id']);
            redirect($this->dof->url_im('achievements', '/edit_achievementinst.php', $this->addvars));
        }
    }

}
/**
 * Форма выбора шаблона достижения
 */
class dof_im_achievements_goal_select_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID текущего раздела
     */
    protected $id = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var $achievementownerid - идентификатор персоны, для которой планируется добавление достижения
     */
    protected $achievementownerid = 0;

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;
        $this->creatingachievementownerid = $this->_customdata->creatingachievementownerid;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        // Выбор шаблона достижения для формирования цели
        $options = new stdClass();
        $options->categories = [];
        $options->achievements = [];

        $options = $this->dof->storage('achievements')->get_goalsselect_list(
            0,
            0,
            [
                'scenario' => [2,3,6,7],
            ],
            $this->creatingachievementownerid
        );

        // если доступных шаблонов нет - оставим форму пустой
        if (empty($options->achievements))
        {
            return;
        }


        // Заголовок формы
        $headertitle = $this->dof->get_string('form_achievementins_select_goal_title', 'achievements');
        $mform->addElement(
            'header',
            get_class($this).'_title',
            $headertitle
            );
        $mform->setExpanded(get_class($this).'_title', false);


        $select = $mform->addElement(
            'dof_hierselect',
            get_class($this).'_template',
            $this->dof->get_string(get_class($this).'_template', 'achievements'),
            '',
            NULL,
            '<div class="col-12 px-0"></div>'
        );
        // Установка набора значений
        $categories = [
                '' => $this->dof->get_string(get_class($this).'_category_not_set','achievements')
            ] + array_reverse($options->categories, true);
        $achievements = [
                '' => [
                    '' => $this->dof->get_string(get_class($this).'_goal_not_set', 'achievements')
                ]
            ] + array_reverse($options->achievements, true);
        $select->setOptions([$categories, $achievements]);

        $mform->addElement(
            'submit',
            get_class($this).'_submit',
            $this->dof->get_string(get_class($this).'_submit', 'achievements')
        );
        if ( empty($options->achievements) )
        {
            // если выбирать нечего, жестко очистим форму
            $mform->removeElement(get_class($this).'_title');
            $mform->removeElement(get_class($this).'_template');
            $mform->removeElement(get_class($this).'_submit');
        }

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
        $errors = [];

        // Проверим существование раздела
        if ( isset($data[get_class($this).'_template'][0]) && $data[get_class($this).'_template'][0] > 0 )
        {
            $exist = $this->dof->storage('achievementcats')->is_exists($data[get_class($this).'_template'][0]);
            if ( empty($exist) )
            {// Раздел не найден
                $errors[get_class($this).'_template'] = $this->dof->get_string(
                    'error_'.get_class($this).'_category_not_found',
                    'achievements'
                );
            } else
            {
                if( ! $this->dof->storage('achievementcats')->is_access('use', $data[get_class($this).'_template'][0]) )
                {
                    $errors[get_class($this).'_template'] = $this->dof->get_string(
                        'error_'.get_class($this).'_category_access_denied',
                        'achievements'
                    );
                }
                // Проверим существование шаблона
                if ( isset($data[get_class($this).'_template'][1]) && $data[get_class($this).'_template'][1] > 0 )
                {
                    $achievement = $this->dof->storage('achievements')->is_exists($data[get_class($this).'_template'][1]);
                    if ( empty($achievement) )
                    {// Шаблон не найден
                        $errors[get_class($this).'_template'] = $this->dof->get_string(
                            'error_'.get_class($this).'_achievement_not_found',
                            'achievements'
                        );
                    } else
                    {// Шаблон найден

                        // Получение объекта достижения
                        $obj = $this->dof->storage('achievements')->object($data[get_class($this).'_template'][1]);

                        // Проверка на возможность ручного добавления данного достижения
                        $manual_create_errors = $obj->manual_create(
                            $this->creatingachievementownerid
                        );

                        if ( (bool)$manual_create_errors == true )
                        {// Ручное создание невозможно по причине указанных ошибок

                            $errors[get_class($this).'_template'] = '';
                            // Выведем все ошибки
                            foreach ( $manual_create_errors as $error )
                            {
                                $errors[get_class($this).'_template'] .= $error . '<br>';
                            }
                        }
                    }
                } else
                {// Раздел не установлен
                    $errors[get_class($this).'_template'] = $this->dof->get_string(
                        'error_'.get_class($this).'_achievement_not_set',
                        'achievements'
                    );
                }
            }
        } else
        {// Раздел не установлен
            $errors[get_class($this).'_template'] = $this->dof->get_string(
                'error_'.get_class($this).'_category_not_set',
                'achievements'
            );
        }

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

        if ( $mform->elementExists(get_class($this).'_template') )
        {
            if ( ! empty($this->id) )
            {// Заполнение значениями
                $category = $this->dof->storage('achievementcats')->is_exists($this->id);
                if ( ! empty($category) )
                {// Раздел найден
                    $mform->setDefault(get_class($this).'_template', [$this->id, NULL]);
                }
            }

            // Блокаровка разделов по строковому ключу
            $categoriesselect = $mform->getElement(get_class($this).'_template')->getElements()[0];
            if ( ! empty($categoriesselect->_options) )
            {// Опции определены
                foreach ( $categoriesselect->_options as &$option )
                {
                    if ( ! empty($option['attr']['value']) &&
                            substr($option['attr']['value'], 0, 1) == '0')
                    {// Раздел со строковым ключем - флаг блокировки
                        $option['attr']['disabled'] = 'disabled';
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
        if ( $this->is_submitted() AND confirm_sesskey() AND
            $this->is_validated() AND $formdata = $this->get_data()
            )
        {// Редирект на страницу добавления достижения
            $this->addvars['aid'] = $formdata->{get_class($this).'_template'}[1];
            $this->addvars['create_goal'] = true;
            unset($this->addvars['id']);
            redirect($this->dof->url_im('achievements', '/edit_achievementinst.php', $this->addvars));
        }
    }

}


/**
 * Форма фильтрации
 */
class dof_im_achievementins_filter_form extends dof_modlib_widgets_form
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
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->filter = $this->_customdata->filter;
        $personid = $this->_customdata->personid;

        // Заголовок формы
        $mform->addElement(
            'header',
            'form_achievementins_filter_title',
            $this->dof->get_string('form_achievementins_filter_title', 'achievements')
        );
        $mform->setExpanded('form_achievementins_filter_title', ! empty($this->filter));

        // Шаблон достижения
        $options = $this->get_achievementselect_list();
        $select = $mform->addElement(
            'dof_hierselect',
            'form_achievementins_filter_ach',
            $this->dof->get_string('form_achievementins_filter_ach', 'achievements'),
            '',
            NULL,
            '<div class="col-12 px-0"></div>'
        );
        // Установка набора значений
        $select->setOptions([$options->categories, $options->achievements]);

        // Интервал баллов
        $points = [];
        $points[] = $mform->createElement('text', 'form_achievementins_filter_points_min', '');
        $points[] = $mform->createElement('text', 'form_achievementins_filter_points_max', '');
        $mform->addGroup(
            $points,
            'form_achievementins_filter_points',
            $this->dof->get_string('form_achievementins_filter_points', 'achievements'),
            ' - '
        );
        $mform->setType('form_achievementins_filter_points[form_achievementins_filter_points_min]', PARAM_FLOAT);
        $mform->setType('form_achievementins_filter_points[form_achievementins_filter_points_max]', PARAM_FLOAT);

        // Статус
        $statuses = [0 => $this->dof->get_string('filter_form_not_set', 'achievements')];
        if ( $this->dof->workflow('achievementins')->is_access('view:notavailable', $personid) )
        {// Пользователь может видеть неподтвержденные достижения
            $statuses = $statuses + $this->dof->workflow('achievementins')->get_meta_list('real');
        } else
        {// Пользователь может видеть только активные достижения
            $statuses = $statuses + $this->dof->workflow('achievementins')->get_meta_list('active');
        }

        $select = $mform->addElement(
            'select',
            'form_achievementins_filter_status',
            $this->dof->get_string('form_achievementins_filter_status', 'achievements'),
            $statuses
        );





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




        $button = [];
        $button[] = $mform->createElement(
                'submit',
                'form_achievementins_filter_submit',
                $this->dof->get_string('form_achievementins_filter_submit', 'achievements')
        );
        $button[] = $mform->createElement(
                'submit',
                'form_achievementins_filter_cancel',
                $this->dof->get_string('form_achievementins_filter_cancel', 'achievements')
        );
        $mform->addGroup($button, 'form_achievementins_filter_button', '', '');


        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');

        $this->set_data([]);
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
        global $DB, $CFG;

        $default_values = [];

        if ( ! empty($this->filter) )
        {// Передана строка поиска
            $filters = (array)json_decode($this->filter);
            foreach ( $filters as $filterkey => $filtervalue )
            {
                switch ( $filterkey )
                {
                    case 'achievement_category' :
                        $key = 'form_achievementins_filter_ach[0]';
                        break;
                    case 'achievement' :
                        $key = 'form_achievementins_filter_ach[1]';
                        break;
                    case 'pointsmin' :
                        $key = 'form_achievementins_filter_points[form_achievementins_filter_points_min]';
                        break;
                    case 'pointsmax' :
                        $key = 'form_achievementins_filter_points[form_achievementins_filter_points_max]';
                        break;
                    case 'status' :
                        $key = 'form_achievementins_filter_status';
                        break;
                    case 'achievement_createdate_from' :
                        $key = 'achievement_createdate[from]';
                        break;
                    case 'achievement_createdate_to' :
                        $key = 'achievement_createdate[to]';
                        break;
                    default:
                        $key = $filterkey;
                }

                if ( $key == 'form_achievementins_filter_ach[0]' )
                {
                    $key = 'form_achievementins_filter_ach';
                    if ( isset($default_values[$key]) )
                    {// Категория уже установлена
                        $cat = $default_values[$key];
                    } else
                    {
                        $cat = [];
                    }
                    $cat[0] = $filtervalue;
                    $default_values[$key] = $cat;
                    continue;
                }
                if ( $key == 'form_achievementins_filter_ach[1]' )
                {
                    $key = 'form_achievementins_filter_ach';
                    if ( isset($default_values[$key]) )
                    {// Категория уже установлена
                        $cat = $default_values[$key];
                    } else
                    {
                        $cat = [];
                    }
                    $cat[1] = $filtervalue;
                    $default_values[$key] = $cat;
                    continue;
                }
                $default_values[$key] = $filtervalue;
            }
        }
        // Заполняем форму данными
        parent::set_data($default_values);
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND
             $this->is_validated() AND $formdata = $this->get_data()
           )
        {// Редирект на страницу добавления достижения
            $filter = [];
            $filterstring = $this->get_filterstring($formdata);
            $addvars = $this->addvars;
            $addvars['filter'] = $filterstring;
            redirect($this->dof->url_im('achievements', '/my.php', $addvars));
        }

    }

    /**
     * Сформировать строку критериев фильтрации по массиву
     */
    protected function get_filterstring($filter)
    {
        if ( ! isset($filter->form_achievementins_filter_button) )
        {// Фильтр не подтвержден
            return '';
        }
        if ( isset($filter->form_achievementins_filter_button['form_achievementins_filter_cancel']) )
        {// Фильтр отменен
            return '';
        }

        // Подготовка к формированию строки
        $filterarr = [];
        if ( isset($filter->form_achievementins_filter_ach) && ! empty($filter->form_achievementins_filter_ach) )
        {
            if ( $filter->form_achievementins_filter_ach[0] > 0 )
            {// Передан раздел
                $filterarr['achievement_category'] = $filter->form_achievementins_filter_ach[0];
            }
            if ( $filter->form_achievementins_filter_ach[1] > 0 )
            {// Передан шаблон
                $filterarr['achievement'] = $filter->form_achievementins_filter_ach[1];
            }
        }
        if ( isset($filter->form_achievementins_filter_points) && ! empty($filter->form_achievementins_filter_points) )
        {
            if ( $filter->form_achievementins_filter_points['form_achievementins_filter_points_min'] > 0 )
            {// Передан минимальный балл
                $filterarr['pointsmin'] = $filter->form_achievementins_filter_points['form_achievementins_filter_points_min'];
            }
            if ( $filter->form_achievementins_filter_points['form_achievementins_filter_points_max'] > 0 )
            {// Передан максимальный балл
                $filterarr['pointsmax'] = $filter->form_achievementins_filter_points['form_achievementins_filter_points_max'];
            }
        }
        if ( isset($filter->form_achievementins_filter_status) && ! empty($filter->form_achievementins_filter_status) )
        {// Передан статус
            $filterarr['status'] = $filter->form_achievementins_filter_status;
        }

        if( ! empty($filter->achievement_createdate['from']) )
        {
            $filterarr['achievement_createdate_from'] = $filter->achievement_createdate['from'];
        }

        if( ! empty($filter->achievement_createdate['to']) )
        {
            $filterarr['achievement_createdate_to'] = $filter->achievement_createdate['to'];
        }

        return json_encode($filterarr);
    }

    /**
     * Получить список родителей для поля
     *
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     *
     * @return object - Объект с данными состава разделов
     */
    protected function get_achievementselect_list($id = 0, $level = 0)
    {
        $result = new stdClass();
        $result->categories = [];
        $result->achievements = [];
        $result->categories[0] = $this->dof->get_string('filter_form_not_set', 'achievements');

        // Получим cписок дочерних элементов
        $statuses = $this->dof->workflow('achievementcats')->get_meta_list('active');
        $statuses = array_keys($statuses);
        $parents = $this->dof->storage('achievementcats')->
        get_records(['status' => $statuses, 'parentid' => $id], ' sortorder ASC, id ASC ', 'id, name');

        if ( ! empty($parents) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');

            // Получим массив статусов шаблонов
            $astatuses = $this->dof->workflow('achievements')->get_meta_list('active');
            $astatuses = array_keys($astatuses);
            $result->achievements[0] = [0 => $this->dof->get_string('filter_form_not_set', 'achievements')];
            foreach ( $parents as $cat )
            {
                // Сформируем элемент раздела
                $result->categories[$cat->id] = $shift.$cat->name;
                // Получить шаблоны категории
                $catachievements = $this->dof->storage('achievements')->
                    get_records(['status' => $astatuses, 'catid' => $cat->id], ' sortorder ASC, id ASC ', 'id, name');
                if ( ! empty($catachievements) )
                {
                    foreach ( $catachievements as $id => &$item )
                    {
                        $item = $item->name;
                    }
                }
                $result->achievements[$cat->id] = [0 => $this->dof->get_string('filter_form_not_set', 'achievements')] + $catachievements;

                // Получим массив дочерних
                $childrens = $this->get_achievementselect_list($cat->id, $level + 1);
                // Добавим к исходному
                $result->categories = $result->categories + $childrens->categories;
                $result->achievements = $result->achievements + $childrens->achievements;
            }
        }

        return $result;
    }
}


/**
 * Форма фильтрации
 */
class dof_im_achievementins_default_achievementcat_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

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
        $this->addvars = $this->_customdata->addvars;

        // Заголовок формы
        $mform->addElement(
            'static',
            'form_rating_title',
            '',
            $this->dof->get_string('form_default_achievementcat_title', 'achievements')
        );

        // Выпадающий список с категориями
        $select = $mform->createElement(
            'select',
            'form_default_achievementcat_achievementcat',
            $this->dof->get_string('form_default_achievementcat_achievementcat', 'achievements'),
            []
        );
        // Активные категории
        $achievementcats = (array)$this->dof->storage('achievementcats')->get_categories_select_options(0,[
        //    'departmentid' => $this->addvars['departmentid']
        ]);
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
        $chooseachievementcatgroup[] = $select;

        //кнопка отправки формы
        $chooseachievementcatgroup[] = $mform->createElement(
            'submit',
            'form_default_achievementcat_submit',
            $this->dof->get_string('form_default_achievementcat_submit', 'achievements')
        );
        // Форма выбора раздела
        $mform->addGroup($chooseachievementcatgroup, 'form_default_achievementcat_group', '', '');


        // формирование кнопок для ближайшего нижестоящего уровня категорий
        $achievementcatbuttons = [];

        $category = 0;
        if( ! empty($this->addvars['filter']) )
        {
            $params = (array)json_decode($this->addvars['filter']);
            if( isset($params['achievement_category']) )
            {
                $category = $params['achievement_category'];
            }
        }


        // формирование списка ближайшего нижестоящего уровня категорий
        $achievementcats = (array) $this->dof->storage('achievementcats')->get_categories_list($category, 0, [
            'metalist' => 'active',
            'affectrating'=>'1',
            'maxdepth'=>'1',
        //    'departmentid'=>$this->addvars['departmentid'],
            'sortorder' => 'sortorder ASC, id ASC'
        ]);

        if ( ! empty($achievementcats) )
        {// нет активных категорий в ближайшем уровне - значит нет и кнопок, пусть выбирают из полного списка

            foreach($achievementcats as $achievementcatid=>$achievementcatname)
            {
                $achievementcatbuttons[] = $mform->createElement(
                    'submit',
                    'form_default_achievementcat_submit_'.$achievementcatid,
                    $achievementcatname
                );
            }
        }
        if ( ! empty($achievementcatbuttons) )
        {//кнопки есть - отобразим
            $mform->addGroup($achievementcatbuttons, 'form_default_achievementcat_buttonsgroup', '', '');
        }
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');

        $this->set_data([]);
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

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND
            $this->is_validated() AND $formdata = $this->get_data()
            )
        {// Редирект на страницу добавления достижения
            if( ! empty($this->addvars['filter']) )
            {
                $filtersearchparams = (array)json_decode($this->addvars['filter']);
            } else
            {
                $filtersearchparams = [];
            }
            if( ! empty($formdata->form_default_achievementcat_buttonsgroup) )
            {
                //получение ключа нажатой кнопки
                $achievementcatsubmit = current(array_keys($formdata->form_default_achievementcat_buttonsgroup));
                //var_dump($achievementcatsubmit);exit;
                //в ключе содержится идентификатор категории - извлечем его
                $filtersearchparams['achievement_category'] = explode('form_default_achievementcat_submit_', $achievementcatsubmit)[1];
            } else
            if ( ! empty($formdata->form_default_achievementcat_group) )
            {
                $filtersearchparams['achievement_category'] = $formdata->form_default_achievementcat_group['form_default_achievementcat_achievementcat'];
            }

            if( ! empty($filtersearchparams) )
            {
                $this->addvars['filter'] = json_encode($filtersearchparams);
            }

            $link = $this->dof->url_im('achievements', '/rating.php', $this->addvars);
            redirect($link);
        }

        return 0;
    }
}

/**
 * Форма одобрения цели
 */
class dof_im_achievements_approve_goal_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID текущего раздела
     */
    protected $id = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * {@inheritDoc}
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        $group = [];
        // одобрить
        $group[] = $mform->createElement(
            'submit',
            'approve_ok',
            $this->dof->get_string('form_approve_goal__approve_ok', 'achievements')
        );

        // отклонить
        $group[] = $mform->createElement(
                'submit',
                'approve_fail',
                $this->dof->get_string('form_approve_goal__approve_fail', 'achievements')
                );

        // вернуться назад
        $group[] = $mform->createElement(
            'submit',
            'go_back',
            $this->dof->get_string('form_approve_goal__go_back', 'achievements')
        );

        $mform->addGroup($group, 'desicion', '', ' ');
    }

    /**
     * @return bool
     */
    public function process()
    {
        if ( $formdata = $this->get_data() )
        {
            if ( array_key_exists('approve_ok', $formdata->desicion) )
            {
                // меняем статус у цели на одобренный (цель ожидает достижения)
                $approveresult = $this->dof->storage('achievementins')->approve_the_goal($formdata->id);
                // цель одобрена
                if( $approveresult )
                {
                    // Cтатус успешно изменен - подготовим радостную новость пользователю
                    $this->dof->messages->add(
                            $this->dof->get_string('form_approve_goal__message__change_status_approve_ok_success', 'achievements'),
                            'message'
                            );
                    // Вернем пользователя на страницу просмотра портфолио
                    redirect($this->dof->url_im('achievements', '/my.php', $this->addvars));
                }
                else
                {
                    // Не удалось изменить статус, необходимо вывести сообщение об ошибке
                    $this->dof->messages->add(
                            $this->dof->get_string('form_approve_goal__error__change_status_failed', 'achievements'),
                            'error'
                            );
                }
            } elseif (  array_key_exists('approve_fail', $formdata->desicion) )
            {
                // цель отклонена
                // меняем статус у цели на одобренный (цель ожидает достижения)
                $approveresult = $this->dof->storage('achievementins')->approve_the_goal($formdata->id, false);
                // цель одобрена
                if( $approveresult )
                {
                    // Cтатус успешно изменен - подготовим радостную новость пользователю
                    $this->dof->messages->add(
                            $this->dof->get_string('form_approve_goal__message__change_status_approve_fail_success', 'achievements'),
                            'message'
                            );
                    // Вернем пользователя на страницу просмотра портфолио
                    redirect($this->dof->url_im('achievements', '/my.php', $this->addvars));
                }
                else
                {
                    // Не удалось изменить статус, необходимо вывести сообщение об ошибке
                    $this->dof->messages->add(
                            $this->dof->get_string('form_approve_goal__error__change_status_failed', 'achievements'),
                            'error'
                            );
                }
            } else
            {
                // Вернем пользователя на страницу просмотра портфолио
                redirect($this->dof->url_im('achievements', '/my.php', $this->addvars));
            }
        }
    }
}

/**
 * Форма подтверждения достижения цели
 */
class dof_im_achievements_achieve_goal_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $id - ID текущего раздела
     */
    protected $id = 0;

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
        $this->id = $this->_customdata->id;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);

        $group = [];
        // подтвердить
        $group[] = $mform->createElement(
                'submit',
                'achieve_ok',
                $this->dof->get_string('form_achieve_goal__achieve', 'achievements')
                );

        // вернуться назад
        $group[] = $mform->createElement(
                'submit',
                'go_back',
                $this->dof->get_string('form_achieve_goal__achieve_close', 'achievements')
                );

        $mform->addGroup($group, 'desicion', '', ' ');
    }

    public function process()
    {
        if ( $formdata = $this->get_data() )
        {
            if ( array_key_exists('achieve_ok', $formdata->desicion) )
            {
                // меняем статус у цели на одобренный (цель ожидает достижения)
                $achieveresult = $this->dof->storage('achievementins')->achieve_the_goal($formdata->id);

                if( $achieveresult )
                {
                    // Cтатус успешно изменен - подготовим радостную новость пользователю
                    $this->dof->messages->add(
                        $this->dof->get_string('form_achieve_goal__message__change_status_success', 'achievements'),
                        'message'
                    );
                    // Вернем пользователя на страницу просмотра портфолио
                    redirect($this->dof->url_im('achievements', '/my.php', $this->addvars));
                }
                else
                {
                    // Не удалось изменить статус, необходимо вывести сообщение об ошибке
                    $this->dof->messages->add(
                        $this->dof->get_string('form_achieve_goal__error__change_status_failed', 'achievements'),
                        'error'
                    );
                }
                return $achieveresult;
            } else
            {
                // Вернем пользователя на страницу просмотра портфолио
                redirect($this->dof->url_im('achievements', '/my.php', $this->addvars));
            }
        }
    }
}

?>