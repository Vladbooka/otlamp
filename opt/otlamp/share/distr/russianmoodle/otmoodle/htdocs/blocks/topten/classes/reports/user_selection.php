<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Блок топ 10. Класс "пользователи".
 *
 * @package    block
 * @subpackage topten
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_topten\reports;

require_once($CFG->dirroot . '/blocks/topten/lib.php');

use MoodleQuickForm;
use block_topten\base as base;
use stdClass;
use \block_topten\output\object_user_universal;
use \core\notification as notification;

class user_selection extends base {
    /**
     * Ид пользовователей уже показанных согласно выборке
     *
     * @var array
     */
    private $shownusers = [];
    /**
     * Преподавателей по умолчанию
     *
     * @var integer
     */
    const RATING_NUMBER = 3;
    /**
     * Генерация отображения слайда
     *
     * @return string - HTML-код слайда
     */
    public function get_html($data)
    {
        global $PAGE;
        $html = '';
        $selectedusersids = $data['selectedusersids'];
        $dat = $data['data'];
        sort($selectedusersids);
        $output = $PAGE->get_renderer('block_topten');
        if (!empty($dat->selecttemplate)) {
            // проверим на существование шаблона
            if (array_key_exists($dat->selecttemplate, $this->get_templates())) {
                $main = new object_user_universal($selectedusersids, $dat);
                $html = $output->render_object_user($main, $dat->selecttemplate);
            } else {
                $html = get_string('none_template', 'block_topten');
                notification::error(get_string('none_template', 'block_topten'));
            }
        }
        if (!empty($selectedusersids)) {
            return $html;
        } else {
            return '';
        }
    }
    
    /**
     * Получение id преподователей согласно выборке
     *
     * @param array $userconditions - условия выборки пользователей
     * @param number $numusers - необходимое количество пользователей
     * @param boolean $addnotenough - дополнить, если в выборку попало меньше, чем требовалось (кончились пользователи)
     * @return array id пользователей
     */
    protected function user_mapping_logic($userconditions=[], $usercondslogic='AND', $numusers=self::RATING_NUMBER, $addnotenough=true) {
        // Выбока пользовательских ид по параметрам
        $ids = $this->get_users_ids_by_conditions($userconditions, $usercondslogic);
        // если выборка меньше чем нужно делаем петлю функции
        if (count($ids) < $numusers) {
            if (!$addnotenough){
                return $ids;
            }
            // очистим и запишем оставшихся пользователей
            $this->shownusers = $ids;
            
            $numusers = $numusers - count($ids);
            $oldids = $this->user_mapping_logic($userconditions, $usercondslogic, $numusers, false);
            $randids = array_merge($oldids, $ids);
        } else {
            // выбираем случайные id пользователей
            $randkeys = array_rand($ids, $numusers);
            
            $randids = [];
            if (count($randkeys) == 1) {
                $this->shownusers[] = $ids[$randkeys];
                $randids[] = $ids[$randkeys];
            } else {
                foreach ($randkeys as $randkey) {
                    $this->shownusers[] = $ids[$randkey];
                    $randids[] = $ids[$randkey];
                }
            }
        }
        return $randids;
    }
    /**
     * Выбока пользовательских ид по параметрам
     *
     * @param array $conditionsarray - массив условий
     * @param string $objectname - имя бьекта user_preferance
     * @return array ids of users
     */
    protected function get_users_ids_by_conditions($conditionsarray=[], $usercondslogic='AND') {
        global $DB;
        
        $conditions = [
            'u.deleted = 0',
            'u.username != \'guest\''
        ];
        $params = [];
        
        if (!empty($conditionsarray))
        {
            $userfieldsconds = [];
            foreach ($conditionsarray as $cond) {
                
                $paramname = 'ufc'.count($userfieldsconds);
                $isprofilefield = (stripos($cond->conditionfield, 'user_profilefield_') !== false);
                
                if ($isprofilefield) {
                    // Кастомное поле профиля пользователя
                    $fieldname = substr($cond->conditionfield, strlen('user_profilefield_'));
                    $column = 'uid.data';
                } else {
                    // Обычное поле пользователя
                    $fieldname = substr($cond->conditionfield, strlen('user_field_'));
                    $column = 'u.'.$fieldname;
                }
                
                if ($cond->conditionfieldsoftmatch) {
                    // содержит значение
                    $userfieldscond = $DB->sql_like($column, ':'.$paramname, false, false);
                    $params[$paramname] = '%'.$cond->conditionfieldval.'%';
                } else {
                    // точное соответствие
                    $userfieldscond = $column . "=:".$paramname;
                    $params[$paramname] = $cond->conditionfieldval;
                }
                
                if ($isprofilefield) {
                    $userfieldscond = "EXISTS (SELECT 1
                                                 FROM {user_info_field} uif
                                                 JOIN {user_info_data} uid ON uid.fieldid = uif.id
                                                WHERE uid.userid = u.id
                                                  AND uif.shortname = '" . $fieldname . "'
                                                  AND ". $userfieldscond . ")";
                }
                
                $userfieldsconds[] = $userfieldscond;
            }
            
            $conditions[] = '('.implode(' '.$usercondslogic.' ', $userfieldsconds).')';
        }
        
        if (!empty($this->shownusers)) {
            // IN запрос показанных пользователей
            list($shownuserssql, $shownusersparams) = $DB->get_in_or_equal($this->shownusers,
                SQL_PARAMS_NAMED, 'param', false);
            $params = array_merge($params, $shownusersparams);
            $conditions[] = 'u.id '.$shownuserssql;
        }
        
        // Выберем все id пользователей согласно условиям
        $sql = "SELECT u.id
                  FROM {user} u
                 WHERE ". implode(' AND ', $conditions) . "
              GROUP BY u.id
              ORDER BY u.id";
        return $DB->get_fieldset_sql($sql, $params);
    }
    /**
     * Добавление полей в форму сохранения слайда
     *
     * @param $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     *
     * @return void
     */
    public function definition(&$mform, $formsave = null) {
        // Получаем запись, если есть.
        if (!empty($formsave->block->config->selecttemplate)) {
            $objecttype = $formsave->block->config->selecttemplate;
        } else {
            $objecttype = null;
        }
        // удаляем не используемые поля из настройки фильтрации
        if (isset($formsave->block->config->conditionfield)) {
            $newkey = 0;
            foreach ($formsave->block->config->conditionfield as $key => $value) {
                if (!empty($value)) {
                    $formsave->block->config->conditionfield[$newkey] = $formsave->block->config->conditionfield[$key];
                    $formsave->block->config->conditionfieldval[$newkey] = $formsave->block->config->conditionfieldval[$key];
                    $formsave->block->config->conditionfieldsoftmatch[$newkey] = $formsave->block->config->conditionfieldsoftmatch[$key];
                    $newkey++;
                }
            }
            while ($key >= $newkey) {
                unset(
                    $formsave->block->config->conditionfield[$newkey],
                    $formsave->block->config->conditionfieldval[$newkey],
                    $formsave->block->config->conditionfieldsoftmatch[$newkey]
                    );
                $newkey++;
            }
        }
        // удаляем не используемые настраиваемые поля
        if (isset($formsave->block->config->field)) {
            $newkey = 0;
            foreach ($formsave->block->config->field as $key => $value) {
                if (!empty($value)) {
                    $formsave->block->config->field[$newkey] = $formsave->block->config->field[$key];
                    $formsave->block->config->text_field[$newkey] = $formsave->block->config->text_field[$key];
                    $newkey++;
                }
            }
            while ($key >= $newkey) {
                unset(
                    $formsave->block->config->field[$newkey],
                    $formsave->block->config->text_field[$newkey]
                    );
                $newkey++;
            }
        }
        // Выбор типа блока
        $objecttypes = $this->get_templates();
        $select = [];
        foreach ($objecttypes as $key => $value) {
            $select[$key] = $value['name'];
        }
        $selectelement = $mform->addElement(
            'select',
            'config_selecttemplate',
            get_string('slide_object_formsave_selecttemplate_label', 'block_topten'),
            $select
            );
        $selectelement->setSelected($objecttype);
        // Описания для выбранного шаблона
        foreach ($objecttypes as $key => $value) {
            $descstr = get_string('slide_object_formsave_template_desc', 'block_topten', $value['name']);
            $mform->addElement('textarea', $key . '_desc', $descstr, ['rows'=>5, 'cols'=>100]); // для textarea работает hideif
            $mform->setType($key . '_desc', PARAM_RAW); // для данного поля обязательно указывать тип данных
            $mform->setDefault($key . '_desc', $value['description']); // значение поля - описание шаблона
            $mform->hideIf($key . '_desc', 'config_selecttemplate', 'ne', $key); // прячем описание, если выбран другой шаблон
            $mform->freeze([$key . '_desc']); // фризим, чтобы отображался не поле для редактирования, а просто значение
        }
        $mform->addElement('header', 'config_custom_template_fields', get_string('custom_template_fields', 'block_topten'));
        $mform->setExpanded('config_custom_template_fields');
        $mform->addElement(
            'static',
            'config_custom_template_fields_desc',
            '',
            get_string('custom_template_fields_desc', 'block_topten')
            );
        // Поля для фильтрации
        $fields =  $this->get_all_fields();
        // Настраиваемые поля шаблонов
        $repeatarray = [];
        //select поля
        $repeatarray[] = $mform->createElement(
            'select',
            'config_field',
            get_string('object_user_select_field', 'block_topten', '{no}'),
            $fields
        );
        //текстовые поля
        $repeatarray[] = $mform->createElement(
            'text',
            'config_text_field',
            get_string('object_user_text_field', 'block_topten', '{no}')
        );
        // количество сохраненных групп элементов условий
        if ( ! empty($formsave->block->config->field) ) {
            $repeatno = count($formsave->block->config->field);
        } else {
            // по умолчанию предоставляем одну группу элементов для заполнения
            $repeatno = 1;
        }
        // настройки полей
        $repeateloptions = [];
        $repeateloptions['config_text_field']['type'] = PARAM_TEXT;
        // повторение элементов
        $formsave->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_adition_repeats',
            'option_add_adition_fields',
            1,
            null,
            true
            );
        // добавляем секцию с кондициями
        $this->add_filtering_section($mform, $formsave);
        
        $condlogics = [
            'AND' => get_string('config_condition_logic_and', 'block_topten'),
            'OR' => get_string('config_condition_logic_or', 'block_topten'),
        ];
        $mform->addElement('select', 'config_condition_logic', get_string('config_condition_logic', 'block_topten'), $condlogics);
        $mform->setDefault('config_condition_logic', 'and');
    }
    

    
    /**
     * Добавление фильтра по пользовательским полям
     *
     * @param $formsave - Объект формы
     * @param MoodleQuickForm $mform - Объект конструктора формы
     */
    protected function add_filtering_section(&$mform, $formsave) {
        $prefix = 'config';
        $mform->addElement('header', $prefix.'_filtersection', get_string('filtering', 'block_topten'));
        $mform->setExpanded($prefix.'_filtersection');
        // Поля для фильтрации
        $fields = $this->get_all_fields();
        // условия подписки в курс, количество настраивается клиентом
        $repeatarray = [];
        // враппер для условия
        $repeatarray[] = $mform->createElement('html','<div class="fields_filter_setting">');
        // поле для проверки условия
        $repeatarray[] = $mform->createElement(
            'select',
            $prefix.'_conditionfield',
            get_string('groupon', 'block_topten'),
            $fields
            );
        // значение для сравнения с полем профиля
        $repeatarray[] = $mform->createElement(
            'text',
            $prefix.'_conditionfieldval',
            get_string('filter', 'block_topten')
            );
        // использовать ли строгое соответствие
        $repeatarray[] = $mform->createElement(
            'selectyesno',
            $prefix.'_conditionfieldsoftmatch',
            get_string('softmatch', 'block_topten')
            );
        // /враппер для условия
        $repeatarray[] = $mform->createElement('html','</div>');
        
        // количество сохраненных групп элементов условий
        if ( ! empty($formsave->block->config->conditionfield) ) {
            $repeatno = count($formsave->block->config->conditionfield);
        } else {
            // по умолчанию предоставляем одну группу элементов для заполнения
            $repeatno = 1;
        }
        // настройки полей
        $repeateloptions = [];
        $repeateloptions[$prefix.'_conditionfield']['type'] = PARAM_TEXT;
        $repeateloptions[$prefix.'_conditionfield']['helpbutton'] = ['groupon', 'block_topten'];
        $repeateloptions[$prefix.'_conditionfieldval']['disabledif'] = [$prefix.'_conditionfield', 'eq', 0];
        $repeateloptions[$prefix.'_conditionfieldval']['type'] = PARAM_TEXT;
        $repeateloptions[$prefix.'_conditionfieldval']['helpbutton'] = ['filter', 'block_topten'];
        $repeateloptions[$prefix.'_conditionfieldsoftmatch']['type'] = PARAM_BOOL;
        $repeateloptions[$prefix.'_conditionfieldsoftmatch']['disabledif'] = [$prefix.'_conditionfield', 'eq', 0];
        $repeateloptions[$prefix.'_conditionfieldsoftmatch']['helpbutton'] = ['softmatch', 'block_topten'];
        // повторение элементов
        $formsave->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            1,
            null,
            true
            );
    }
    
    /**
     * Получение срока не выбрано + стандартных полей + кастомных полей
     *
     * @return array
     */
    public static function get_all_fields() {
        $fields = [];
        $dof = block_topten_get_dof();
        if (!is_null($dof)) {
            $fields = $dof->modlib('ama')->user(false)->get_all_user_fields_list(
                ['middlename', 'description']);
        }
        return array_merge([get_string('g_none', 'block_topten')], $fields);
    }
    /**
     * Хедер по умолчанию
     *
     * {@inheritDoc}
     * @see \block_topten\base::get_default_header()
     */
    public static function get_default_header($small = false)
    {
        return get_string($small ? 'user_selection_header' : 'user_selection', 'block_topten');
    }
    /**
     * Получение даты для кеширования
     *
     * {@inheritDoc}
     * @see \block_topten\base::get_cache_data()
     */
    public function get_cache_data($oldcache = false)
    {
        if (!empty($oldcache) && !empty($oldcache['shownusers'])) {
            $this->shownusers = $oldcache['shownusers'];
        }
        // дата из конфига
        $data = $this->config;
        // Проверка и приведение к нормальному виду кондиций
        if (isset($data->conditionfield) && is_array($data->conditionfield)) {
            foreach ($data->conditionfield as $k=>$field) {
                if (!empty($field) && isset($data->conditionfieldval[$k])) {
                    $data->conditions[$k] = new stdClass();
                    $data->conditions[$k]->conditionfield = $field;
                    $data->conditions[$k]->conditionfieldval = $data->conditionfieldval[$k];
                    $data->conditions[$k]->conditionfieldsoftmatch = ! empty($data->conditionfieldsoftmatch[$k]);
                }
            }
        } else {
            $data->conditions = [];
        }
        
        // Проверка и приведение к нормальному виду настраиваемых полей
        if (isset($data->field) && is_array($data->field)) {
            foreach ($data->field as $k=>$field) {
                if (!empty($field)) {
                    $data->additionfields[$k] = new stdClass();
                    $data->additionfields[$k]->field = $field;
                    $data->additionfields[$k]->text_field = $data->text_field[$k];
                }
            }
        } else {
            $data->additionfields = [];
        }
        
        $userconditions = $data->conditions ?? [];
        $usercondslogic = $data->condition_logic ?? 'AND';
        $numusers = empty($data->rating_number) ? self::RATING_NUMBER : $data->rating_number;
        $selectedusersids = $this->user_mapping_logic($userconditions, $usercondslogic, $numusers, true);
        return [
            'shownusers' => $this->shownusers,
            'selectedusersids' => $selectedusersids,
            'data' => $data
        ];
    }
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_ready()
     */
    public function is_ready()
    {
        return true;
    }
    /**
     * {@inheritDoc}
     * @see \block_topten\base::is_cached()
     */
    public function is_cached()
    {
        return true;
    }
    /**
     * Получение всех шаблонов
     *
     * @return array
     */
    private function get_templates()
    {
        global $CFG;
        
        $templates = [];
        // Директория с шаблонами
        $classesdir = $CFG->dirroot.'/blocks/topten/templates/';
        // Интерфейс для просмотра содержимого каталогов
        $dir = new \DirectoryIterator($classesdir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile()) {
                $file = $fileinfo->getBasename('.mustache');
                if ( stripos($file, 'object_user') === 0)
                {// это шаблон отчета пользователи
                    if (get_string_manager()->string_exists($file . '_name', 'block_topten')) {
                        $templates[$file]['name'] = get_string($file . '_name', 'block_topten');
                    } else {
                        $templates[$file]['name'] = $file;
                    }
                    if (get_string_manager()->string_exists($file . '_description', 'block_topten')) {
                        $templates[$file]['description'] = get_string($file . '_description', 'block_topten');
                    } else {
                        $templates[$file]['description'] = get_string('none_description', 'block_topten');
                    }
                }
            }
        }
        return $templates;
    }
}