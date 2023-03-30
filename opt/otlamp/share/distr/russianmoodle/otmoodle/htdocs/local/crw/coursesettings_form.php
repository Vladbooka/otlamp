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
 * Витрина курсов. Класс формы дополнительных настроек курса
 *
 * Для добавления новых свойств курса необходимо:
 * - Объявить поле в методе definition
 * - Если поле сложное(значение поля нельзя сразу записать в БД),
 * то необходимо добавить логику сохранения поля и заполнения значения по умолчанию
 * - Если поле простое, то необходмо добавить его низвание в массив $configs обработчика
 * формы.
 * Сохраниение и заполнение поля в форме установленным значением произойдет автоматически.
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
// Подклчим библиотеки
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/local/crw/lib.php');

class coursesettings_form extends moodleform {
    
    // Свойства класса
    protected $course;
    protected $returnto;

    /**
     * Объявление формы
     */
    function definition() {
        global $CFG, $PAGE, $OUTPUT;
        
        // Получим данные
        $mform    = $this->_form;
        $course   = $this->_customdata['course'];
        $returnto = $this->_customdata['returnto'];
        // Свойства класса
        $this->course  = $course;
        $this->returnto = $returnto;

        // Заголовок формы
        $mform->addElement('header','coursesettings', get_string('coursesettings', 'local_crw'));
        // Скрытые поля
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
        
        
        // Шаблон отображения категорий
        $templates = ['inherit' => get_string('coursepage_template_inherit', 'local_crw')];
        $templates += local_crw_get_coursepage_templates();
        $mform->addElement('select', 'coursepage_template', get_string('coursepage_template', 'local_crw'), $templates);
        $mform->addHelpButton('coursepage_template', 'coursepage_template', 'local_crw');
        $mform->setDefault('coursepage_template', 'inherit');
        
        
        
        // Дополнительные категории
        $choices = [];
        if ( has_capability('local/crw:manage_additional_categories', context_system::instance()) )
        {// верхний уровень
            $choices[0] = get_string('top');
        }
        $choices += \core_course_category::make_categories_list('local/crw:manage_additional_categories');
        if( isset($choices[$this->course->category]) )
        {// основная категория курса не должна назначаться еще и в качестве дополнительной
            unset($choices[$this->course->category]);
        }
        if ( ! empty($choices) )
        {// Настройка отображается только если есть право на управление хотя бы одной категорией
            $mform->addElement(
                'autocomplete',
                'additional_categories',
                get_string('additional_categories','local_crw'),
                $choices,
                [
                    'multiple' => 'multiple'
                ]
            );
            $mform->addHelpButton('additional_categories', 'additional_categories', 'local_crw');
        }
        
        // поле для назначения курсу тегов из коллекции 1
        $mform->addElement('tags', 'tagcollection_custom1', get_string('tagcollection_custom1', 'local_crw'),
            ['itemtype' => 'crw_course_custom1', 'component' => 'local_crw']);
        
        // поле для назначения курсу тегов из коллекции 2
        $mform->addElement('tags', 'tagcollection_custom2', get_string('tagcollection_custom2', 'local_crw'),
            ['itemtype' => 'crw_course_custom2', 'component' => 'local_crw'], ['id' =>'myid']);
        
        // Необходимые знания
        $mform->addElement(
                'textarea',
                'required_knowledge',
                get_string('required_knowledge', 'local_crw')
        );
        $mform->addHelpButton('required_knowledge', 'required_knowledge', 'local_crw');
        
        // Стоимость курса
        $mform->addElement(
                'text',
                'course_price',
                get_string('course_price', 'local_crw')
        );
        $mform->addHelpButton('course_price', 'course_price', 'local_crw');
        $mform->setType('course_price', PARAM_TEXT);
        
        $difficult = [
                'none' => get_string('course_difficult_none', 'local_crw'),
                'easy' => get_string('course_difficult_easy', 'local_crw'),
                'medium' => get_string('course_difficult_medium', 'local_crw'),
                'hard' => get_string('course_difficult_hard', 'local_crw'),
        ];
        // Уровень сложности
        $mform->addElement(
                'select',
                'course_difficult',
                get_string('course_difficult', 'local_crw'),
                $difficult
        );
        
        // Дополнительное описание
        $mform->addElement(
                'editor',
                'additional_description',
                get_string('additional_description', 'local_crw')
        );
        
        
        // Где отображать краткое описание курса
        $options = array(
            1 => get_string('everywhere', 'local_crw'),
            0 => get_string('nowhere', 'local_crw'),
            2 => get_string('coursedesc', 'local_crw'),
            3 => get_string('courselink', 'local_crw')
        );
        $mform->addElement(
            'select',
            'additional_description_view',
            get_string('additional_description_view', 'local_crw'),
            $options
        );
        $mform->addHelpButton('additional_description_view', 'additional_description_view', 'local_crw');
        $mform->setDefault('additional_description_view', 1);
        
        
        //отображать дату начала курса
        $choices = [
            1 => get_string('everywhere','local_crw'),
            0 => get_string('nowhere','local_crw'),
            2 => get_string('coursedesc','local_crw'),
            3 => get_string('courselink','local_crw'),
        ];
        $mform->addElement(
            'select',
            'display_startdate',
            get_string('display_startdate', 'local_crw'),
            $choices
        );
        $mform->setDefault('display_startdate', 0);
        $mform->addHelpButton('display_startdate', 'display_startdate', 'local_crw');
        
        
        //Отображение иконок подписки
        $choices = [
            1 => get_string('everywhere','local_crw'),
            0 => get_string('nowhere','local_crw'),
            2 => get_string('coursedesc','local_crw'),
            3 => get_string('courselink','local_crw'),
        ];
        $mform->addElement(
            'select',
            'display_enrolicons',
            get_string('display_enrolicons', 'local_crw'),
            $choices
        );
        $mform->setDefault('display_enrolicons', 1);
        $mform->addHelpButton('display_enrolicons', 'display_enrolicons', 'local_crw');
        
        
        //Отображение цены
        $choices = [
            1 => get_string('everywhere','local_crw'),
            0 => get_string('nowhere','local_crw'),
            2 => get_string('coursedesc','local_crw'),
            3 => get_string('courselink','local_crw'),
        ];
        $mform->addElement(
            'select',
            'display_price',
            get_string('display_price', 'local_crw'),
            $choices
        );
        $mform->setDefault('display_price', 1);
        $mform->addHelpButton('display_price', 'display_price', 'local_crw');
        

        
        $yesno = array(
            0 => get_string('coursecat_view_hide', 'local_crw'),
            1 => get_string('coursecat_view_text', 'local_crw'),
            2 => get_string('coursecat_view_link', 'local_crw')
        );
        // Отображать категорию курса
        $mform->addElement(
                'select',
                'coursecat_view',
                get_string('coursecat_view', 'local_crw'),
                $yesno
        );
        $mform->addHelpButton('coursecat_view', 'coursecat_view', 'local_crw');
        
        $yesno = array(
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        );
        // Скрыть курс
        $mform->addElement(
                'select',
                'hide_course',
                get_string('hide_course', 'local_crw'),
                $yesno
        );
        $mform->addHelpButton('hide_course', 'hide_course', 'local_crw');
        
        // Отображать настраиваемые (кастомные) поля курса на странице описания
        $cfchoices = [
            'default' => get_string('custom_fields_view_default', 'local_crw'),
            '0' => get_string('custom_fields_view_hide', 'local_crw'),
            '1' => get_string('custom_fields_view_show', 'local_crw'),
        ];
        $mform->addElement('select', 'custom_fields_view', get_string('custom_fields_view', 'local_crw'), $cfchoices);
        $mform->setDefault('custom_fields_view', 'default');
        $mform->addHelpButton('custom_fields_view', 'custom_fields_view', 'local_crw');
        
        // Наклейка на курс
        $stickers = array_merge(
            [
            	0 => get_string('no', 'local_crw')
        	],
            (array)local_crw_get_stickers(null, ['langstrings' => true])
        );
        
        $mform->addElement(
                'select',
                'sticker',
                get_string('sticker', 'local_crw'),
                $stickers
                );
        $mform->addHelpButton('sticker', 'sticker', 'local_crw');
        
        $redirectenrolledusers = [
            0 => get_string('show_course_info_page_default', 'local_crw'),
            1 => get_string('show_course_info_page_for_all_users', 'local_crw'),
            2 => get_string('redirect_all_enrolled_users', 'local_crw'),
            3 => get_string('hide_course_info_page', 'local_crw')
        ];
        // Настройка отображения страницы дополнительной информации курса
        $mform->addElement(
            'select',
            'course_info_view',
            get_string('course_info_view', 'local_crw'),
            $redirectenrolledusers
            );
        $mform->setDefault('course_info_view', 0);
        
        $yesno = array(
            'default' => get_string('hide_course_contacts_default', 'local_crw'),
            0 => get_string('no', 'local_crw'),
            1 => get_string('yes', 'local_crw')
        );
        // Скрыть контакты курса
        $mform->addElement(
            'select',
            'hide_course_contacts',
            get_string('hide_course_contacts', 'local_crw'),
            $yesno
            );
        $mform->addHelpButton('hide_course_contacts', 'hide_course_contacts', 'local_crw');
        
        // Заголовок формы выбора изображений
        $mform->addElement('header','courseimgs', get_string('course_imgs', 'local_crw'));
        
        // Скрыть галерею курса
        $mform->addElement(
            'select',
            'hide_course_gallery',
            get_string('hide_course_gallery', 'local_crw'),
            $yesno
            );
        $mform->addHelpButton('hide_course_gallery', 'hide_course_gallery', 'local_crw');
        
        $descriptionfiles = [];
        $showcaseimgs = [];
        $data = $this->course_images_urls();
        if (isset($data['courseimgurls'])) {
            foreach ($data['courseimgurls'] as $fileid => $dat) {
                $html = '';
                $html .= html_writer::div($dat['filename'], 'crw-filename');
                $html .= html_writer::img($dat['murl'], $dat['filename']);
                $descriptionfiles[] =& $mform->createElement('checkbox', $fileid, null, $html);
                $showcaseimgs[] = $mform->createElement('radio', null, null, $html, $fileid);
            }
        }
        if (isset($data['coursefileurls'])) {
            foreach ($data['coursefileurls'] as $fileid => $dat) {
                $html = '';
                $html .= html_writer::div($dat['filename'], 'crw-filename');
                $html .= html_writer::img($dat['murl'], $dat['filename']);
                $descriptionfiles[] =& $mform->createElement('checkbox', $fileid, null, $html);
            }
        }
        if (!empty($descriptionfiles)) {
            $mform->addGroup($descriptionfiles, 'description_files', get_string('descriptionimgs', 'local_crw'));
        }
        if (!empty($showcaseimgs)) {
            $mform->addGroup($showcaseimgs, 'showcase_imgs', get_string('showcaseimgs', 'local_crw'));
        }

        // Кнопка сохранения
        $this->add_action_buttons();
        
        // Применим фильтр
        $mform->applyFilter('__ALL__', 'trim');
        
        // Установим данные формы
        $this->set_data($course);
    }

    /**
     * Значения по умолчанию для формы
     */
    function set_data($default_values) {
        
        global $DB, $CFG;
        
        $mform =& $this->_form;
        
        if ( is_object($default_values) )
        {// Конвертируем в массив данные из формы
            $default_values = (array)$default_values;
        }
        
        // Получить все свойства курса
        $coursedata = $DB->get_records(
                'crw_course_properties',
                array(
                    'courseid' => $this->course->id
                )
        );
        
        // Группируем свойства
        $required_knowledgedef = '';
        foreach ( $coursedata as $config )
        {
            // Каждое полученное свойство добавляем в автозаполнение
            $default_values[$config->name] = $config->value;
            
            // ОБРАБОТЧИКИ СЛОЖНЫХ СВОЙСТВ КУРСА
            // Формируем список необходимых знаний
            if ( $config->name == 'required_knowledge' )
            {// Текущая опция относится к списку необходимых знаний
                if ( empty($required_knowledgedef) )
                {// Запятая не нужна
                    $required_knowledgedef .= $config->svalue;
                } else
                {// Добавим значение с разделителем
                    $required_knowledgedef .= ', '.$config->svalue;
                }
            }

            if ( $config->name == 'additional_description' )
            {
                $default_values['additional_description'] = [
                    'text' => $config->value,
                    'format' => '1'
                ];
            }
            // установка значений по умолчанию для чекбоксов
            if ( $config->name == 'description_files' )
            {
                foreach (json_decode($default_values['description_files']) as $key => $val){
                    $mform->setDefault('description_files[' . $key . ']', 'checked');
                }
            }
        }

        // Дополнительные категории хранятся в отдельной таблице
        $default_values['additional_categories'] = $this->get_additional_categories();
        
        if ( isset($required_knowledgedef) )
        {// Добавляем необходимые знания к автозаполнению формы
            $default_values['required_knowledge'] = $required_knowledgedef;
        }
        
        // установка ранее назначенных курсу тегов из коллекции 1
        $default_values['tagcollection_custom1'] = core_tag_tag::get_item_tags_array('local_crw', 'crw_course_custom1', $this->course->id);
        
        // установка ранее назначенных курсу тегов из коллекции 2
        $default_values['tagcollection_custom2'] = core_tag_tag::get_item_tags_array('local_crw', 'crw_course_custom2', $this->course->id);

        
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
            switch ( $this->returnto )
            {// Куда вернуть пользователя
                default: // Вернем на страницу плагина
                    $url = new moodle_url(
                        $CFG->wwwroot.'/local/crw/course.php',
                        array( 'id' => $this->course->id )
                    );
                    break;
            }
            redirect($url);
        }
        
        if ($formdata = $this->get_data()) {// Форма отправлена и проверена
            // Конвертируем в массив объект формы
            if ( is_object($formdata) )
            {
                $formdata = (array)$formdata;
            }
            // сохранение значения из коллекции тегов 1
            core_tag_tag::set_item_tags('local_crw', 'crw_course_custom1', $this->course->id,
                context_course::instance($this->course->id), $formdata['tagcollection_custom1']);
            // сохранение значения из коллекции тегов 2
            core_tag_tag::set_item_tags('local_crw', 'crw_course_custom2', $this->course->id,
                context_course::instance($this->course->id), $formdata['tagcollection_custom2']);
            
            // ПРОСТЫЕ СВОЙСТВА
            // Массив свойств для сохранения без дополнительной обработки
            $configs = array(
                'coursepage_template',
                'course_price',
                'coursecat_view',
                'display_coursetags',
                'sticker',
                'course_difficult',
                'display_startdate',
                'display_enrolicons',
                'display_price',
                'hide_course',
                'custom_fields_view',
                'course_info_view',
                'hide_course_contacts',
                'hide_course_gallery',
                'additional_description_view',
                'timemodified',
                'showcase_imgs'
            );
            // сохраняем дату последнего редактирования доп.полей
            $formdata['timemodified'] = time();
            // Cохранение свойств
            $result = $this->save_config($formdata, $configs);

            // СОСТАВНЫЕ СВОЙСТВА
            // Сохранить требуемые знания для курса
            $result = ( $result AND $this->save_required_knowledge($formdata) );
                
            // Сохранить дополнительное описание курса
            $result = ( $result AND $this->save_additional_description($formdata) );
            
            // Сохранить дополнительное описание курса
            $result = ( $result AND $this->save_description_files_ids($formdata) );
                
            // Сохранить дополнительные категории, где должен отображаться курс
            $additionalcategories = [];
            if( ! empty($formdata['additional_categories']) )
            {
                $additionalcategories = $formdata['additional_categories'];
            }
            $result = ( $result AND $this->save_additional_categories($additionalcategories) );
            
            $url = new moodle_url(
                $CFG->wwwroot.'/local/crw/course.php',
                array( 'id' => $this->course->id )
            );
            redirect($url);
        }
    }
    
    /**
     * Обновить требуемые знания для курса
     *
     * @param array $formdata - данные формы
     * @return bool - результат сохранения
     */
    private function save_description_files_ids( $formdata )
    {
        global $DB;
        
        $result = true;
        
        // Получим старые значения свойства
        $oldimgsids = $DB->get_record(
            'crw_course_properties',
            array(
                'courseid' => $this->course->id,
                'name' => 'description_files'
            )
            );
        
        if ( isset($formdata['description_files']) )
        {// Свойство передано в форме
            if ( empty($oldimgsids) )
            {// Добавляем запись
                $insert = new stdClass;
                $insert->courseid = $this->course->id;
                $insert->name = 'description_files';
                $insert->svalue = '';
                $insert->value = json_encode($formdata['description_files']);
                $result = $DB->insert_record('crw_course_properties', $insert);
            } else
            {// Обновляем запись
                $update = new stdClass;
                $update->id = $oldimgsids->id;
                $update->courseid = $this->course->id;
                $update->name = 'description_files';
                $update->svalue = '';
                $update->value = json_encode($formdata['description_files']);
                $result = $DB->update_record('crw_course_properties', $update);
            }
        }else{
            if ( !empty($oldimgsids) ) {
                $result = $DB->delete_records(
                    'crw_course_properties',
                    ['id' => $oldimgsids->id, 'courseid' => $this->course->id]
                    );
            }
        }
        return $result;
    }

    /**
     * Получение дополнительных категорий для текущего курса
     *
     * @return array - массив идентификаторов дополнительных категорий для текущего курса
     */
    private function get_additional_categories()
    {
        global $DB;
        
        $additionalcategories = [];
        
        // Получение всех привязок курса к доп.категориям
        $records = $DB->get_records('crw_course_categories', [
            'courseid' => $this->course->id
        ], '', 'categoryid');
        
        if( ! empty($records) )
        {// Есть привязки
            foreach($records as $record)
            {
                $additionalcategories[] = $record->categoryid;
            }
        }
        
        return $additionalcategories;
    }
    
    /**
     * Сохранение дополнительных категорий для текущего курса
     *
     * @param array $additionalcategories - массив идентификаторов дополнительных категорий для текущего курса
     *
     * @return boolean
     */
    private function save_additional_categories($additionalcategories)
    {
        global $DB;
    
        foreach($additionalcategories as $ack=>$additionalcategory)
        {
            if (  $additionalcategory == 0 )
            {// Категория верхнего уровня
                $context = context_system::instance();
            } else
            {
                $context = context_coursecat::instance($additionalcategory);
            }
            
            if ( ! has_capability('local/crw:manage_additional_categories', $context) )
            {// Нет права управлять категорией
                unset($additionalcategories[$ack]);
            }
            
            if( $additionalcategory == $this->course->category )
            {// Категория является основной, нет смысла добавлять ее в качестве дополнительной
                unset($additionalcategories[$ack]);
            }
        }
        
        // список дополнительных категорий до изменения
        $oldadditionalcategories = $this->get_additional_categories();
        // список категорий, которые были удалены во время изменения
        $todelete = array_diff($oldadditionalcategories, $additionalcategories);
        // список категорий, которые были добавлены во время изменения
        $toinsert = array_diff($additionalcategories, $oldadditionalcategories);
        
        //результат изменения списка доп.категорий
        $result = true;
        
        if( ! empty($todelete) )
        {// Имеются категории, которые следует удалить
            // Подготовка данных для запроса
            list($sqlin, $sqlparams) = $DB->get_in_or_equal($todelete, SQL_PARAMS_NAMED);
            $sqlparams['courseid'] = $this->course->id;
            // Удаление категорий
            $result = $result && $DB->delete_records_select(
                'crw_course_categories',
                'courseid=:courseid AND categoryid '.$sqlin,
                $sqlparams
            );
        }
        if ( ! empty($toinsert) )
        {// Имеются категории для добавления
            foreach($toinsert as $additionalcategory)
            {
                //Создание новой привязки курса к категории
                $newrecord = new stdClass;
                $newrecord->courseid = $this->course->id;
                $newrecord->categoryid = $additionalcategory;
                $result = $result && $DB->insert_record('crw_course_categories', $newrecord);
            }
        }
        return $result;
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
                'crw_course_properties',
                array(
                    'courseid' => $this->course->id
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
                    $configobj->courseid = $this->course->id;
                    $configobj->svalue = $formdata[$config];
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->insert_record('crw_course_properties', $configobj) );
                } else
                {// Если свойство уже определено - обновим запись
                    $configobj = new stdClass;
                    $configobj->id = $key;
                    $configobj->name = $config;
                    $configobj->courseid = $this->course->id;
                    $configobj->svalue = $formdata[$config];
                    $configobj->value = $formdata[$config];
                    $result = ( $result AND $DB->update_record('crw_course_properties', $configobj) );
                }
            }
        }
        // Результат сохранения
        return $result;
    }
    
    /**
     * Обновить требуемые знания для курса
     *
     * @param array $formdata - данные формы
     * @return bool - результат сохранения
     */
    private function save_required_knowledge( $formdata )
    {
        global $DB;

        $result = true;
        if ( isset($formdata['required_knowledge']) )
        {// Свойство передано в форме
            
            // Получим старые значения свойства
            $oldknowledge = $DB->get_records(
                    'crw_course_properties',
                    array(
                            'courseid' => $this->course->id,
                            'name' => 'required_knowledge'
                    )
            );
            // Разобъем строку на элементы массива
            $items = explode(',', $formdata['required_knowledge']);
            // Формируем массив новых знаний
            $newknowledge = array();
            // Получим массив неповторяющихся значений
            foreach ( $items as $item )
            {
                // Удаляем пробелы по краям
                $item = trim($item);
                if ( ! in_array($item, $newknowledge) )
                {// Такого значения еще нет в таблице
                    if ( ! empty($item) )
                    {// Значение - не пустая строка
                        $newknowledge[] = $item;
                    }
                }
            }
    
            // Удалим старые неиспользуемые значения и получим массив значений для добавления
            foreach ( $oldknowledge as $item )
            {
                if ( ! in_array($item->value, $newknowledge) )
                {// Старого значения нет в новой таблице, удалим его из БД
                    $DB->delete_records(
                            'crw_course_properties',
                            array( 'id' => $item->id )
                    );
                } else
                {// Старое значение есть в новом массиве, уберем его оттуда
                    $key = array_search($item->value, $newknowledge);
                    unset($newknowledge[$key]);
                }
            }
            
            // Добавляем записи
            $insert = new stdClass;
            $insert->courseid = $this->course->id;
            $insert->name = 'required_knowledge';
            foreach ( $newknowledge as $item )
            {
                $insert->svalue = $item;
                $insert->value = $item;
                $result = ( $result AND $DB->insert_record('crw_course_properties', $insert) );
            }
        }
        return $result;
    }
    
    /**
     * Обновить требуемые знания для курса
     *
     * @param array $formdata - данные формы
     * @return bool - результат сохранения
     */
    private function save_additional_description( $formdata )
    {
        global $DB;
    
        $result = true;
        
        if ( isset($formdata['additional_description']) )
        {// Свойство передано в форме
            // Получим старые значения свойства
            $oldad = $DB->get_record(
                    'crw_course_properties',
                    array(
                            'courseid' => $this->course->id,
                            'name' => 'additional_description'
                    )
            );
            
            if ( empty($oldad) )
            {// Добавляем запись
                $insert = new stdClass;
                $insert->courseid = $this->course->id;
                $insert->name = 'additional_description';
                $insert->svalue = '';
                $insert->value = $formdata['additional_description']['text'];
                $result = $DB->insert_record('crw_course_properties', $insert);
            } else
            {// Обновляем запись
                $update = new stdClass;
                $update->id = $oldad->id;
                $update->courseid = $this->course->id;
                $update->name = 'additional_description';
                $update->svalue = '';
                $update->value = $formdata['additional_description']['text'];
                $result = $DB->update_record('crw_course_properties', $update);
            }
        }
        return $result;
    }
    /**
     * Получить url-адреса и имена фаилов и изображений, создать trumb
     *
     * @return array - courseimgurls => [fileid => [murl] -адрес изображеня для текущего курса
     *                                             [filename]
     *                 coursefileurls => [fileid => [murl] -адрес иконки фаила для текущего курса
     *                                              [filename]
     */
    protected function course_images_urls()
    {
        global $CFG, $OUTPUT;
        $data = [];
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/course/lib.php');
        // Получаем хранилище
        $fs = get_file_storage();
        // Получаем контекст
        $context = context_course::instance($this->course->id);
        // Получаем файлы
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        // Вывод первого файла
        if (count($files)) {
            // Формирование изменений между превью и исходным файлом
            $preview = new stdClass();
            $preview->component = 'local_crw';
            $preview->filearea = 'thumb';
            foreach ($files as $file) {
                //получаем ид фаила
                $fileid = $file->get_id();
                if ($file->is_valid_image()) { // файл является изображением
                    $data['courseimgurls'][$fileid]['murl'] = local_crw_get_preview($file, $preview, 100);
                    $data['courseimgurls'][$fileid]['filename'] = $file->get_filename();
                }else{
                    $data['coursefileurls'][$fileid]['murl'] = $OUTPUT->image_url(file_file_icon($file, 100));
                    $data['coursefileurls'][$fileid]['filename'] = $file->get_filename();
                }
            }
        }
        return $data;
    }
}