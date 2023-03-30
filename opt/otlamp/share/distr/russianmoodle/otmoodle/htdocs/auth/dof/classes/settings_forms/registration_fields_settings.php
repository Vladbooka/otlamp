<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Формы настроек плагина auth_dof
 * Класс формы управление пользовательскими полями
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\settings_forms;

use moodleform;
use HTML_QuickForm;
use html_writer;
use core\notification;

require_once($CFG->dirroot .'/auth/dof/locallib.php');

class registration_fields_settings extends moodleform
{
    // Поля пользователя
    private $fields = [];
    // Поля внешнего источника
    private $srcfields = [];
    // url страницы настроек
    private $baseurl = '';
    // Фундаментальные настройки плагина которые не может изменять администратор (минимум настроек)
    private $fundamental_settings = [];
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition()
    {
        global $PAGE;
        $PAGE->requires->js_call_amd('auth_dof/form-sorter', 'init', [
            '.registration_fields_settings',
            'div.form-group.row.fitem:not(#fgroup_id_buttonar)',
            '.field_name, .col-element:first-child i.icon'
        ]);
        $dof = auth_dof_get_dof();
        if (!is_null($dof)) {
            $this->fields = $dof->modlib('ama')->user(false)->get_all_user_fields_list(['password']);
        } else {
            new \moodle_exception('Plugin dof requred');
        }
        // Фундаментальные настройки
        $this->fundamental_settings = auth_dof_fundamental_settings();
        // Поля источников из конфига
        $this->srcfields = auth_dof_get_src_config_fields('src_', ['type', 'connection', 'table']);
        // Базовый урл для редиректа
        $this->baseurl = $this->_customdata['baseurl'];
        // Получение модификаторов полей
        $this->modifiers = auth_dof_get_handlers('modifiers');
        $this->groupmodifiers = auth_dof_get_handlers('group_modifiers');
        
        $mform = &$this->_form;
        //Добавляет раздел пользовательских полей в форму
        $this->add_user_fields_to_form($mform);
        // Кнопки сохранения и отмены
        $this->add_action_buttons();
        // Установим данные формы
        $this->set_data([]);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::set_data()
     */
    function set_data($data) {
        $data = auth_dof_get_user_config_fields('fld_', ['display', 'order']);
        // Установим значения для полей источников и модификаторов
        foreach (auth_dof_get_user_config_fields('fld_', ['srcfld', 'mod']) as $fldname => $srcdata) {
            $srcdata = json_decode($srcdata, true);
            if (is_array($srcdata)) {
                foreach ($srcdata as $srcname => $srcval) {
                    $data[$fldname . '[' . $srcname . ']'] = $srcval;
                }
            }
        }
        // Установим значения для фундаментальных настроек
        foreach ($this->fundamental_settings as $fldname => $fldconf) {
            if (isset($fldconf['modifiers'])) {
                foreach ($fldconf['modifiers'] as $modname => $val) {
                    $data["fld_{$fldname}_mod[{$modname}]"] = $val;
                }
            }
        }
        // Заполняем форму данными
        parent::set_data($data);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    function validation($data, $files) {
        $errors = [];
        // Есть отображаемые поля для первого шага регистрации
        $visiblefieldsonstep1 = false;
        foreach ($data as $configfldname => $value) {
            if (stripos($configfldname, 'fld_') === 0) {
                $matches = [];
                preg_match('/fld_([A-Za-z0-9_]+)(_.+)/', $configfldname, $matches);
                list(, $fieldname, $settingtype) = $matches;
                if ($settingtype == '_mod') {
                    // Будем валидировать только включенные поля
                    if ($data['fld_' . $fieldname . '_display'] != 0) {
                        // Выполним валидацию для обычных модификаторов
                        foreach ($this->modifiers as $modname => $str) {
                            if (array_key_exists($modname, $value) && $value[$modname]) {
                                $classname = '\\auth_dof\\modifiers\\' . $modname;
                                $errors += $classname::settings_validation($data, $fieldname);
                            }
                        }
                        // Выполним валидацию для групповых модификаторов
                        foreach ($this->groupmodifiers as $modname => $str) {
                            if (array_key_exists($modname, $value) && $value[$modname]) {
                                $classname = '\\auth_dof\\group_modifiers\\' . $modname;
                                $errors += $classname::settings_validation($data, $fieldname);
                            }
                        }
                        // Если это не поисковый или транслируеный модификатор
                        // то ему не должно быть сопоставлено полей из источников
                        $srcfldcfg = false;
                        if (array_key_exists('fld_' . $fieldname . '_srcfld', $data)) {
                            $srcfldcfg = $data['fld_' . $fieldname . '_srcfld'];
                        }
                        if (! $value['search'] && ! $value['broadcast']) {
                            if (is_array($srcfldcfg)) {
                                foreach ($srcfldcfg as $srcfield) {
                                    if (! empty($srcfield)) {
                                        // Данному типу поля не должно быть сопоставлено полей из источников
                                        $errors['group_' . $fieldname]  = get_string(
                                            'no_need_use_in_source_fields', 'auth_dof');
                                    }
                                }
                            }
                        }
                        // Проверим отображается ли это поле на первом шаге регистрации
                        if ($data['fld_' . $fieldname . '_display'] == 1) {
                            $modshidefld = ['hidden', 'generated'];
                            $fieldishidden = false;
                            foreach ($modshidefld as $hidefld) {
                                if (!empty($value[$hidefld])) {
                                    $fieldishidden = true;
                                }
                            }
                            if(!$fieldishidden) {
                                $visiblefieldsonstep1 = true;
                            }
                        }
                    }
                }
            }
        }
        // Вывод сообщений в заголовке формы для большей наглядности
        if ($errors) {
            notification::error(get_string('form_has_errors', 'auth_dof'));
        } else {
            if (!$visiblefieldsonstep1) {
                $errors['error'] = 'error';
                notification::error(get_string('need_visible_field_on_step1', 'auth_dof'));
            } else {
                notification::success(get_string('form_save_success', 'auth_dof'));
            }
        }
        return $errors;
    }
    
    /**
     * Обработчик формы
     */
    public function process() {
        if ($this->is_cancelled()) {
            redirect($this->baseurl);
        }
        if ($data = $this->get_data()) {
            // Обрабатывает сохранение полей всей формы
            foreach ($data as $fieldname => $value) {
                if (stripos($fieldname, 'fld_') === 0) {
                    if (is_array($value)) {
                        // Активируем модификатор "скрытое поле" если выбран "генерируемое поле"
                        if (! empty($value['generated'])) {
                            $value['hidden'] = 1;
                        }
                        $value = json_encode($value);
                    }
                    set_config($fieldname, $value, 'auth_dof');
                }
            }
            redirect($this->baseurl);
        }
    }
    
    /**
     * Добавляет раздел пользовательских полей в форму
     *
     * @param HTML_QuickForm $mform
     */
    protected function add_user_fields_to_form(HTML_QuickForm $mform) {
        global $OUTPUT;
        // Поле соответствия не выбрано
        $notselected = [0 => get_string('not_selected', 'auth_dof')];
        // Режим отображения "Не отображать", "Отображать на шаге 1", "Отображать на шаге 2"
        $alldisplaytypes = [
            get_string('display_none', 'auth_dof'),
            get_string('display_on_step_1', 'auth_dof'),
            get_string('display_on_step_2', 'auth_dof')
        ];
        // Формирование выподающих списков выбора поля источника
        $sources = [];
        foreach ($this->srcfields as $id => $srcconfig) {
            if (! empty($srcconfig['type'])) {
                $classname = '\\auth_dof\\sourcetype\\'.$srcconfig['type'];
                if (class_exists($classname)) {
                    $srcinstance = new $classname();
                    // Поля внешнего источника
                    $srcfields = $srcinstance->get_external_fields(
                        $srcconfig['connection'], $srcconfig['table']);
                    // Выбор соответствующего поля из полей пользователя
                    $sources[$id] = $mform->createElement(
                        'select',
                        $id,
                        $srcinstance->get_cofig_name($srcconfig['connection']),
                        array_merge($notselected, $srcfields)
                        );
                }
            }
        }
        // Подготовим массив пользовательских полей  к сортировке
        $orderlist = auth_dof_prepare_fields(null, ['order']);
        if (! $orderlist) {
            // так-как список весов полей пуст - инициализируем дефолтные данные
            $orderlist = auth_dof_init_defaults_fields(
                // Поля как в форме настроек "Самостоятельная регистрация по электронной почте" + middlename
                ['username', 'password', 'email', 'firstname', 'middlename', 'lastname', 'city', 'country'],
                ['username', 'password', 'email', 'firstname', 'lastname', 'middlename']
                );
        }
        $fields = [];
        $numfields = count($orderlist);
        foreach ($this->fields as $fieldname => $fieldstring) {
            $fields[] = ['fieldname' => $fieldname, 'fieldstring' => $fieldstring];
            // Добавим новые поля в конец списка
            if (!isset($orderlist[$fieldname]['order'])) {
                $numfields++;
                $orderlist[$fieldname]['order'] = $numfields;
            }
        }
        uasort($fields, function ( $a, $b ) use ($orderlist) {
            if ( $orderlist[$a['fieldname']]['order'] == $orderlist[$b['fieldname']]['order']) return 0;
            return ($orderlist[$a['fieldname']]['order'] < $orderlist[$b['fieldname']]['order']) ? -1 : 1;
        });
        $bpclasses = 'col-xl-4 col-lg-6 col-sm-12';
        $i = 0;
        // Добавим строку заголовков
        $hedergroup = html_writer::div(
            // Поле проверки наличия изменений в форме, так-как после перетаскивания (изменения веса) полей
            // сообщение о том что форма изменена не возникает
            html_writer::span(
                get_string('form_has_chenges', 'auth_dof'),'form_has_chenges', ['style' => 'display:none']),
            $bpclasses);
        $hedergroup .= html_writer::div('', $bpclasses);
        if ($sources) {
            $hedergroup .= html_writer::div(get_string('ext_src_compare', 'auth_dof'), $bpclasses);
        }
        $mform->addElement('html', html_writer::div($hedergroup, 'row form-group heder_line'));
        foreach ($fields as $field) {
            $fieldname = $field['fieldname'];
            $group = [];
            
            // ПЕРВАЯ КОЛОНКА С ИМЕНЕМ ПОЛЯ И СЕЛЕКТОМ РЕЖИМА ОТОБРАЖЕНИЯ
            $colgroup = [];
            // Название элемента
            $colgroup[] = $mform->createElement('html',
                html_writer::div($field['fieldstring'], 'field_name'));
            // Стрелки для перитаскивания
            $colgroup[] = $mform->createElement('html',
                $OUTPUT->pix_icon('i/dragdrop', '', 'moodle', ['class' => 'iconsmall']));
            // Выпадающий список режима отображения поля
            $displaytype = [];
            if (array_key_exists($fieldname, $this->fundamental_settings)) {
                foreach ($this->fundamental_settings[$fieldname]['display'] as $step) {
                    $displaytype[$step] = $alldisplaytypes[$step];
                }
            }
            $colgroup[] = $mform->createElement(
                'select',
                'fld_' . $fieldname . '_display',
                get_string('fld_display', 'auth_dof'),
                $displaytype ? $displaytype : $alldisplaytypes
                );
            // Добавляем группу названием поля и местом отображения
            $group[] = $mform->createElement(
                'group', $fieldname . '_col1', '', $colgroup, '', false);
            
            // МОДИФИКАТОРЫ
            $modgroup = [];
            foreach ($this->modifiers as $modname => $modstr) {
                $classname = '\\auth_dof\\modifiers\\' . $modname;
                if ($classname::display_on_settings_form($fieldname, $this->srcfields)) {
                    $modgroup[] = $mform->createElement('advcheckbox', $modname, $modstr);
                    if (isset($this->fundamental_settings[$fieldname]['modifiers'][$modname])) {
                        $mform->disabledIf(
                            "fld_{$fieldname}_mod[{$modname}]", 'fld_' . $fieldname . '_display', 'neq', 0
                        );
                    }
                } else {
                    $modgroup[] = $mform->createElement('hidden', $modname, 0);
                }
            }
            // Групповые модификаторы
            foreach ($this->groupmodifiers as $modname => $modstr) {
                $classname = '\\auth_dof\\group_modifiers\\' . $modname;
                if ($classname::display_on_settings_form($fieldname, $this->srcfields)) {
                    $modgroup[] = $mform->createElement('advcheckbox', $modname, $modstr);
                } else {
                    $modgroup[] = $mform->createElement('hidden', $modname, 0);
                }
            }
            // Добавляем группу модификаторов
            $modgroupobj = $group[] = $mform->createElement(
                'group', 'fld_' . $fieldname . '_mod', '', $modgroup, '');
            
            // ВЫБОР СООТВЕТСТВИЯ ПО КАЖДОМУ ИЗ ИСТОЧНИКОВ
            // Ох сколько времени было потеряно пока понял что дефольные значения
            // устанавливаются для всех элементов группы по последнему
            if ($sources) {
                $clonable = [];
                foreach ($sources as $id => $source) {
                    $clonable[] = clone($source);
                    $mform->disabledIf(
                        "fld_{$fieldname}_srcfld[{$id}]", 'fld_' . $fieldname . '_display', 'eq', 0
                    );
                }
                // Добавляем группу с полями источника
                $group[] = $mform->createElement('group', 'fld_' . $fieldname . '_srcfld', '', $clonable, '');
            }
            // Добавим классы бутстраповских колонок
            $mform->updateElementAttr($group, ['class' => $bpclasses . ' col-element']);
            // Порядок отображения
            $group[] = $mform->createElement(
                'hidden', 'fld_' . $fieldname . '_order', '', ['class' => 'order_field']);
            $mform->setType('fld_' . $fieldname . '_order', PARAM_INT);
            // Отобразим группу по пользовательскому полю
            $mform->addGroup($group, 'group_' . $fieldname, '', '', false);
            // Заморозим элементы групп модификаторы и поля соответствия если выбрано не показывать поле
            $mform->disabledIf($modgroupobj, 'fld_' . $fieldname . '_display', 'eq', 0);
            // Установим дефолтные значения для веса
            $mform->setDefault('fld_' . $fieldname . '_order', $i);
            // Заблокируем модификатор "скрытое поле" если выбран "генерируемое поле"
            $mform->disabledIf(
                "fld_{$fieldname}_mod[hidden]", "fld_{$fieldname}_mod[generated]", 'eq', 1
            );
            $i++;
        }
    }
}