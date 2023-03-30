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
 * Класс формы управления внешними источниками
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof\settings_forms;

use moodleform;
use HTML_QuickForm;
use html_writer;

require_once($CFG->dirroot .'/auth/dof/locallib.php');

class external_sources_settings extends moodleform
{
    // Поля внешнего источника
    private $srcfields = [];
    // значение поля селекта выбора ресурса
    private $selectedsource = '';
    // Кнопка получить поля источника
    private $getsrcfieldsbtn = false;
    //Кнопка добавить источник
    private $addsourcebtn = false;
    // Источники
    private $sources = [];
    // url страницы настроек
    private $baseurl = '';
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition()
    {
        // Поля источников из конфига
        $this->srcfields = auth_dof_get_src_config_fields('src_', ['type', 'connection', 'table']);
        // Доступные классы источников
        $this->sources = auth_dof_get_handlers('sourcetype');
        // Выбранный источник из селекта или скрытого поля
        $this->selectedsource = $this->_customdata['select_source'];
        // Кнопки формы
        $this->getsrcfieldsbtn = $this->_customdata['get_src_fields_btn'];
        $this->addsourcebtn = $this->_customdata['add_source_btn'];
        // Базовый урл для редиректа
        $this->baseurl = $this->_customdata['baseurl'];
        // Проверим сушествование внешнего источника
        if (! empty($this->_customdata['select_source']) &&
            ! array_key_exists($this->_customdata['select_source'], auth_dof_get_handlers('sourcetype')))
        {
            new \moodle_exception('Not supported source passed');
        }
        
        $mform = &$this->_form;
        
        // Добавляет сохраненные источники в форму, также реализует добавление нового источника
        $this->add_list_of_sources_to_form($mform);
    }
    
    /**
     *
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    function validation($data, $files) {
        $errors = [];
        // Проверим наличие полей во внешней базе данных
        if ($this->getsrcfieldsbtn) {
            $classname = '\\auth_dof\\sourcetype\\' . $this->selectedsource;
            if (class_exists($classname)) {
                $srcinstance = new $classname();
                $errors += $srcinstance->validation();
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
            // Отлавливает нажатие кнопки удаления с последующим удаленим ресурса, смешением индексов и редиректом.
            $this->catch_clicking_and_del_src(
                $data,
                'src_',
                ['type', 'connection', 'table'],
                'delete_source_'
                );
            // Сохраняет ресурс и его поля в конфиг индексом + 1, значение селекта соответствующего поля
            // пользователя остается пустым
            if ($this->getsrcfieldsbtn) {
                $classname = '\\auth_dof\\sourcetype\\' . $this->selectedsource;
                if (class_exists($classname)) {
                    $srcinstance = new $classname();
                    $srcformdata = [];
                    foreach ($srcinstance->definition($this->_form) as $formobject) {
                        $srcfieldname = $formobject->getName();
                        if ($srcfieldname) {
                            $srcformdata[$srcfieldname] = required_param($srcfieldname, PARAM_RAW);
                        }
                    }
                    list($connection, $table) = $srcinstance->process($srcformdata);
                    $i = count(auth_dof_get_src_config_fields('src_', ['type']));
                    $i++;
                    set_config("src_{$i}_type", $this->selectedsource, 'auth_dof');
                    set_config("src_{$i}_connection", $connection, 'auth_dof');
                    set_config("src_{$i}_table", $table, 'auth_dof');
                }
                redirect($this->baseurl);
            }
        }
    }
    
    /**
     * Отлавливает нажатие кнопки удаления с последующим удаленим ресурса, смешением индексов и редиректом.
     *
     * @param array $data
     * @param string $prefix
     * @param array $fldnames
     * @param string $delbtnprefix
     */
    private function catch_clicking_and_del_src($data, string $prefix, array $fldnames, string $delbtnprefix) {
        if (! empty($prefix) && ! empty($fldnames) && ! empty($data) && ! empty($delbtnprefix)) {
            $srcid = false;
            // Получим индекс ресурса из атрибута name кнопки удаления
            foreach($data as $index => $str) {
                if (strpos($index, $delbtnprefix) !== FALSE) {
                    $srcid = substr($index, 14);
                    break;
                }
            }
            // Если нажата кнопка удаления то сместим все записи на -1
            // начиная с указанного идентификатора и удалим последнюю
            if ($srcid) {
                // Получим настройки полей пользователя по выбранным ресурсам
                $srcdata = [];
                foreach (auth_dof_get_user_config_fields('fld_', ['srcfld']) as $fldname => $srccfg) {
                    $srccfg = json_decode($srccfg, true);
                    if (is_array($srccfg)) $srcdata[$fldname] = $srccfg; 
                }
                $firstname = array_shift($fldnames);
                while (($val = get_config('auth_dof', $prefix . ($srcid + 1) . '_' . $firstname)) !== false) {
                    set_config(
                        $prefix . $srcid . '_' . $firstname,
                        $val,
                        'auth_dof'
                        );
                    foreach ($fldnames as $name) {
                        set_config(
                            $prefix . $srcid . '_' . $name,
                            get_config('auth_dof', $prefix . ($srcid + 1) . '_' . $name),
                            'auth_dof'
                            );
                    }
                    // Сместим записи источников для всех полей пользователя
                    foreach ($srcdata as $fldname => $srccfg) {
                        $srcdata[$fldname][$srcid] = $srcdata[$fldname][$srcid + 1];
                    }
                    $srcid++;
                }
                unset_config($prefix . $srcid . '_' . $firstname, 'auth_dof');
                foreach ($fldnames as $name) {
                    unset_config($prefix . $srcid . '_' . $name, 'auth_dof');
                }
                // Удалим последнюю запись и запишем в конфиг
                foreach ($srcdata as $fldname => $srccfg) {
                    unset ($srcdata[$fldname][$srcid]);
                    set_config($fldname, json_encode($srcdata[$fldname]), 'auth_dof');
                }
                redirect($this->baseurl);
            }
        }
    }
    
    /**
     * Добавляет сохраненные источники в форму, также реализует добавление нового источника
     *
     * @param HTML_QuickForm $mform
     */
    protected function add_list_of_sources_to_form(HTML_QuickForm &$mform) {
        if(! empty($this->srcfields)) {
            foreach ($this->srcfields as $id => $srcconfig) {
                if (! empty($srcconfig['type']) || array_key_exists($srcconfig['type'], $this['sources'])) {
                    $classname = '\\auth_dof\\sourcetype\\'.$srcconfig['type'];
                    if (class_exists($classname)) {
                        $srcinstance = new $classname();
                        $cofignamestr = $srcinstance->get_cofig_name($srcconfig['connection']);
                        $mform->addElement(
                            'header',
                            'src_config_header',
                            get_string('src_config_header', 'auth_dof',
                                ['src_name' => $srcinstance->get_name_string(), 'cfg_name' => $cofignamestr])
                            );
                        // Имя подключения к внешнему источнику
                        $mform->addElement(
                            'static',
                            "src_{$id}_connection",
                            get_string('src_connection', 'auth_dof')
                        );
                        $mform->setDefault(
                            "src_{$id}_connection",
                            $cofignamestr
                        );
                        // Таблица внешнего источника
                        $mform->addElement(
                            'static',
                            "src_{$id}_table",
                            get_string('src_table', 'auth_dof')
                        );
                        $mform->setDefault("src_{$id}_table", $srcconfig['table']);
                        // Поля внешнего источника
                        $mform->addElement('static', "fields_{$id}", get_string('src_fields', 'auth_dof'));
                        try {
                            $srcfields = $srcinstance->get_external_fields(
                                $srcconfig['connection'], $srcconfig['table']);
                            if (is_array($srcfields)) {
                                $srcfieldsstr = implode(', ', $srcfields);
                            } else {
                                $srcfieldsstr = get_string('error_get_src_fields', 'auth_dof');
                            }
                        } catch (\Exception $e) {
                            $srcfieldsstr = \html_writer::span($e->getMessage(), '', ['style' => 'color:red']);
                        }
                        $mform->setDefault("fields_{$id}", $srcfieldsstr);
                        // Кнопка удалить внешний источник
                        $mform->addElement(
                            'submit',
                            'delete_source_' . $id,
                            get_string('delete'),
                            ['onClick' => 'javascript:return confirm("'.get_string('delete_src', 'auth_dof').'");']
                            );
                    }
                }
            }
        }
        if ($this->selectedsource) {
            $mform->addElement(
                'header',
                'add_source_header',
                get_string('add_source_header', 'auth_dof') . ': ' . $this->sources[$this->selectedsource]
                );
            $classname = '\\auth_dof\\sourcetype\\' . $this->selectedsource;
            if (class_exists($classname)) {
                // Добавил поля полученные из класса источника
                $srcinstance = new $classname();
                foreach ($srcinstance->definition($mform) as $formelement) {
                    $mform->addElement($formelement);
                }
                $mform->addElement(
                    'submit',
                    'get_src_fields_btn',
                    get_string('get_src_fields_btn', 'auth_dof')
                    );
                // Это требуется чтобы process знал с каким классом работать
                $mform->addElement(
                    'hidden',
                    'select_source',
                    $this->selectedsource
                    );
                $mform->setType('select_source', PARAM_TEXT);
            }
            // Добавление источника
        } else {
            $mform->addElement(
                'header',
                'add_source_header',
                get_string('add_source_header', 'auth_dof')
                );
            // Выбор источника из существующих классов источников
            $mform->addElement(
                'select',
                'select_source',
                get_string('select_source', 'auth_dof'),
                $this->sources
                );
            $mform->addElement(
                'submit',
                'add_source_btn',
                get_string('add_source_btn', 'auth_dof')
                );
        }
    }
}