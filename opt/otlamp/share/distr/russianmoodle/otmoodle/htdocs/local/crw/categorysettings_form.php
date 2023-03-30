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
 *  Класс формы дополнительных настроек категории
 *
 *  Для добавления новых свойств курса необходимо:
 *  - Объявить поле в методе definition
 *  - Если поле сложное(значение поля нельзя сразу записать в БД),
 *  то необходимо добавить логику сохранения поля и заполнения значения по умолчанию
 *  - Если поле простое, то необходмо добавить его низвание в массив $configs обработчика
 *  формы.
 *  Сохраниение и заполнение поля в форме установленным значением произойдет автоматически.
 */
defined('MOODLE_INTERNAL') || die;
// Подклчим библиотеки
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/local/crw/lib.php');
require_once($CFG->dirroot.'/local/crw/locallib.php');
require_once($CFG->dirroot.'/local/crw/plugins/system_search/lib.php');
require_once($CFG->dirroot.'/local/crw/plugins/courses_list_universal/lib.php');

class categorysettings_form extends moodleform {
    
    // Свойства класса
    protected $course;
    protected $returnto;
    protected $context;

    /**
     * Объявление формы
     */
    function definition()
    {
        global $CFG, $PAGE;
        
        // Получим данные
        $mform    = $this->_form;
        $category = $this->_customdata['category'];
        $returnto = $this->_customdata['returnto'];
        $context  = context_coursecat::instance($category->id);
        
        // Свойства класса
        $this->category  = $category;
        $this->returnto = $returnto;
        $this->context = $context;
        
        // Заголовок формы
        $mform->addElement('header','categorysettings', get_string('categorysettings', 'local_crw'));
        
        // Скрытые поля
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
        // Файл иконки
        $mform->addElement('filemanager', 'icon', get_string('category_icon', 'local_crw'), null, ['maxfiles' => 1]);
        // Получаем ID зоны пользовательского файлпикера
        $draftitemid = file_get_submitted_draft_itemid('icon');
        // Подгружаем в зону пользовательского файлпикера файлы из зоны с иконкой категории
        file_prepare_draft_area($draftitemid, $this->context->id, 'local_crw', 'categoryicon', $this->category->id);
        $category->icon = $draftitemid;
        
        // Скрыть категорию
        $yesno = [
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        ];
        $mform->addElement('select', 'hide_category', get_string('hide_category', 'local_crw'), $yesno);
        $mform->addHelpButton('hide_category', 'hide_category', 'local_crw');
        
        
        
        // Шаблон отображения страницы описания курса, принадлежащего категории
        $templates = ['inherit' => get_string('coursepage_template_inherit', 'local_crw')];
        $templates += local_crw_get_coursepage_templates();
        $mform->addElement('select', 'coursepage_template', get_string('category_coursepage_template', 'local_crw'), $templates);
        $mform->addHelpButton('coursepage_template', 'category_coursepage_template', 'local_crw');
        $mform->setDefault('coursepage_template', 'inherit');
        
        
        
        // Шаблон отображения универсального списка курсов, используется при отображении списка в указанной категории
        $plugin = new crw_courses_list_universal('courses_list_universal');
        $templates = ['inherit' => get_string('courselist_template_inherit', 'local_crw')];
        $templates += $plugin->get_courselist_templates();
        $mform->addElement('select', 'courselist_template', get_string('category_courselist_template', 'local_crw'), $templates);
        $mform->addHelpButton('courselist_template', 'category_courselist_template', 'local_crw');
        $mform->setDefault('courselist_template', 'inherit');
        
        
        
        
        $cffields = local_crw_get_custom_fields();
        if (!empty($cffields))
        {
            // Заголовок формы
            $mform->addElement('header','category_custom_fields_roles', get_string('category_custom_fields_roles', 'local_crw'));
            $mform->addHelpButton('category_custom_fields_roles', 'category_custom_fields_roles', 'local_crw');
            
            
            foreach($cffields as $fieldname => $cffield)
            {
                if ($cffield['type'] == 'submit')
                {
                    continue;
                }
                
                
                // Настройки ниже распространяют своё влияние на форму редактирования кастомных полей курса и форму поиска.
                // Опосредованно влияют и на формирование данных для отображения через шаблоны, но исключительно
                // для сохранения логики (если поле нельзя отредактировать, значит оно не заполнено - нет смысла отображать).
                // Тем не менее, цели регулировать отображение у этой настройки нет, отображение регулируется только шаблонами
                $roles = [
                    // по умолчанию всё наследуется из настроек плагина: поле всегда доступно для редактирования и отображения
                    // будет ли поле отображаться в форме поиска - определяется в плагине поиска
                    'inherit' => get_string('category_custom_field_role_inherit', 'local_crw'),
                    // поле отключено для категории,
                    // так оно не будет отображаться на форме редактирования,
                    // не будет отображаться в форме поиска (не редактировалось - не заполнено - нет смысла искать)
                    // не будет помечаться как поле для отображения в списке, чтобы по умолчанию не отображалось в интерфейсах
                    'field_disabled' => get_string('category_custom_field_role_field_disabled', 'local_crw')
                ];
                
                if (crw_system_search_is_custom_field_searchable($cffield))
                {
                    // поиск отключен - не зависимо от того, что настроено в плагине,
                    // находясь в текущей категории пользователь увидит форму поиска без фильтра по этому полю
                    $roles['search_disabled'] = get_string('category_custom_field_role_search_disabled', 'local_crw');
                    // поиск отключен - не зависимо от того, что настроено в плагине,
                    // находясь в текущей категории пользователь увидит форму поиска без фильтра по этому полю
                    // однако, это поле будет доступно для сортировки курсов
                    $roles['search_disabled_sort_enabled'] = get_string('category_custom_field_role_search_disabled_sort_enabled', 'local_crw');
                    // поиск включен - не зависимо от того, что настроено в плагине,
                    // находясь в текущей категории пользователь увидит форму поиска с фильтром по этому полю
                    $roles['search_enabled'] = get_string('category_custom_field_role_search_enabled', 'local_crw');
                    // поиск включен - не зависимо от того, что настроено в плагине,
                    // находясь в текущей категории пользователь увидит форму поиска с фильтром по этому полю
                    // кроме того, если настроено отображение сортировки в форме, то поле можно будет выбрать еще и для сортировки
                    $roles['search_enabled_sort_enabled'] = get_string('category_custom_field_role_search_enabled_sort_enabled', 'local_crw');
                }
                
                
                $yamlfield = \otcomponent_yaml\Yaml::dump([$fieldname => $cffield]);
                
                $mform->addElement('select', 'custom_field_'.$fieldname.'_role', $cffield['label'], $roles);
                $mform->setDefault('custom_field_'.$fieldname.'_role', 'inherit');
                $mform->addHelpButton('custom_field_'.$fieldname.'_role', 'category_custom_field_role', 'local_crw');
                
                $mform->addElement('static', 'custom_field_'.$fieldname.'_role_desc', '', html_writer::tag('PRE', $yamlfield));
                
                
            }
        }
        
        
        
        // Кнопка сохранения
        $this->add_action_buttons();
        
        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');
        
        // Установим данные формы
        $this->set_data($category);
    }
    
    /**
     * Значения по умолчанию для формы
     */
    function set_data($default_values)
    {
        
        global $DB, $CFG;
        
        if ( is_object($default_values) )
        {// Конвертируем в массив данные из формы
            $default_values = (array)$default_values;
        }
        
        // Получить все свойства категории
        $categorydata = $DB->get_records('crw_category_properties', ['categoryid' => $this->category->id]);
        
        // Группируем свойства
        foreach ( $categorydata as $config )
        {
            if ( $config->name == 'icon' )
            {
                continue;
            }
            // Каждое полученное свойство добавляем в автозаполнение
            $default_values[$config->name] = $config->value;
            
            // ОБРАБОТЧИКИ СЛОЖНЫХ СВОЙСТВ КУРСА
        }
        
        // Заполняем форму данными
        parent::set_data($default_values);
    }
    
    /**
     * Обработчик формы
     */
    function process()
    {
        global $DB, $CFG;

        if ( $this->is_cancelled() )
        {// Отменили форму
            if (empty($this->returnto))
            {// Куда вернуть пользователя
                $url = new moodle_url($CFG->wwwroot.'/local/crw/category.php', ['cid' => $this->category->id]);
                redirect($url);
            } else {
                redirect($this->returnto);
            }
        }
        
        if ( $this->is_submitted() AND confirm_sesskey() AND
             $this->is_validated() AND $formdata = $this->get_data()
           )
        {// Форма отправлена и проверена
            // Конвертируем в массив объект формы
            if ( is_object($formdata) )
            {
                $formdata = (array)$formdata;
            }
            
            // ПРОСТЫЕ СВОЙСТВА
            // Массив свойств для сохранения без дополнительной обработки
            $configs = [
                'hide_category',
                'coursepage_template',
                'courselist_template'
            ];
            
            $cffields = local_crw_get_custom_fields();
            if (!empty($cffields))
            {
                foreach(array_keys($cffields) as $fieldname)
                {
                    $configs[] = 'custom_field_'.$fieldname.'_role';
                }
            }
            // Cохранение свойств
            $result = $this->save_config($formdata, $configs);

            // СОСТАВНЫЕ СВОЙСТВА
            // Сохранить файл иконки
            $result = $result && $this->save_category_icon($formdata);
            
            if ($result && !empty($this->returnto))
            {
                redirect($this->returnto);
            } else {
                $url = new moodle_url($CFG->wwwroot.'/local/crw/categorysettings.php', ['id' => $this->category->id]);
                redirect($url);
            }
        }
    }
    
    /**
     * Сохранить простые опции для курса
     *
     * Метод для сохранения нетекстовых опций, состоящих из одной записи в БД
     *
     * @param array $formdata - данные формы
     * @param array $configs - массив имен свойств
     *
     * @return bool - результат сохранения
     */
    private function save_config($formdata, $configs)
    {
        global $DB;
    
        // Получим все свойства текущего курса
        $courseconfigs = $DB->get_records_menu(
                'crw_category_properties',
                array(
                    'categoryid' => $this->category->id
                ),
                '',
                'id, name'
        );
        $result = true;
        // Сохраним свойства
        foreach ( $configs as $config )
        {
            if ( isset($formdata[$config]) )
            {// Если текущее свойство имеется среди данных формы
                // Ищем конфиг
                $key = array_search($config, $courseconfigs);
                if ( empty($key) )
                {// Свойство новое - добавим его
                    $configobj = new stdClass;
                    $configobj->name = $config;
                    $configobj->categoryid = $this->category->id;
                    $configobj->svalue = $formdata[$config];
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->insert_record('crw_category_properties', $configobj) );
                } else
                {// Если свойство уже определено - обновим запись
                    $configobj = new stdClass;
                    $configobj->id = $key;
                    $configobj->name = $config;
                    $configobj->categoryid = $this->category->id;
                    $configobj->svalue = $formdata[$config];
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->update_record('crw_category_properties', $configobj) );
                }
            }
        }
        // Результат сохранения
        return $result;
    }
    
    /**
     * Сохранить иконку категории
     *
     * @param array $formdata - данные формы
     * @return bool - результат сохранения
     */
    private function save_category_icon($formdata)
    {
        global $DB;
        
        // Сохраним файлы из пользовательской зоны в область категории
        file_save_draft_area_files($formdata['icon'], $this->context->id, 'local_crw', 'categoryicon', $this->category->id);
        
        // Хранилище
        $fs = get_file_storage();
        
        $files = $fs->get_area_files($this->context->id, 'local_crw', 'categoryicon');
        $fileid = 0;
        foreach ( $files as $file )
        {
            $mimetype = $file->get_mimetype();
            if ( $mimetype == 'image/jpeg' )
            {
                $fileid = $file->get_id();
            }
        }
        
        $result = true;
        
        // Получим старое значение свойства
        $dbicon = $DB->get_record(
                'crw_category_properties',
                array(
                        'categoryid' => $this->category->id,
                        'name' => 'icon'
                )
        );
            
        if ( empty($dbicon) )
        {// Добавить свойство
            $insert = new stdClass;
            $insert->categoryid = $this->category->id;
            $insert->name = 'icon';
            $insert->svalue = $fileid;
            $insert->value = $fileid;
            $result = $DB->insert_record('crw_category_properties', $insert);
        } else
        {// Обновить свойство
            $update = new stdClass;
            $update->id = $dbicon->id;
            $update->categoryid = $this->category->id;
            $update->name = 'icon';
            $update->svalue = $fileid;
            $update->value = $fileid;
            $result = $DB->update_record('crw_category_properties', $update);
        }
        return $result;
    }
}