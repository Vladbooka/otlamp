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
 * Подготовка данных для рендеринга темплейта с данными о курсе
 *
 * @package    local_crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_crw\output;
defined('MOODLE_INTERNAL') || die();

use core_course_category;
use moodle_url;
use renderer_base;
use templatable;
use core_auth\output\login;

require_once ($CFG->dirroot . '/lib/authlib.php');
require_once ($CFG->dirroot . '/local/crw/locallib.php');

class course implements templatable {

    protected $course;
    private $custom_fields = null;
    private $courseinlist = null;
    private $coursecatview = null;
    private $startdate = null;
    private $price = null;
    private $enrolicons = null;
    private $gallery = null;
    private $required_knowledges = null;
    private $coursecontacts = null;
    private $additional_description = null;
    private $description = null;
    private $context = null;
    private $enrols = null;
    private $enrolforms = null;
    private $options = [];
    private $categories = [];


    public function __construct($course, $options=[]) {

        $this->course = $course;
        $this->options = $options;

    }

    public function get_course_id()
    {
        return $this->course->id;
    }

    /**
     * {@inheritDoc}
     * @see templatable::export_for_template()
     */
    public function export_for_template(renderer_base $output) {

        $data = [];

        $data['course'] = $this->course;
        $data['course_url'] = (new moodle_url('/course/view.php', ['id' => $this->get_course_id()]))->out(false);

        $data['course_custom_fields'] = $this->get_course_custom_fields();
        $data['display_list_course_custom_fields'] = $this->get_setting_display_course_custom_fields();

        $data['category'] = $this->get_course_category();
        $data['category_parents'] = $this->get_course_category_parents();
        $data['display_categories'] = $this->get_setting_display_categories();
        $data['display_categories_as_text'] = $this->get_setting_display_categories_as_text();
        $data['display_categories_as_links'] = $this->get_setting_display_categories_as_links();

        $data['enrol_icons'] = $this->get_enrol_icons($output);
        $data['display_enrol_icons'] = $this->get_setting_display_enrol_icons();

        $data['startdate'] = $this->get_startdate();
        $data['display_startdate'] = $this->get_setting_display_startdate();

        $data['price'] = $this->get_price();
        $data['display_price'] = $this->get_setting_display_price();

        $data['gallery'] = $this->get_gallery();
        $data['display_gallery'] = $this->get_setting_display_gallery();

        $data['preview'] = $this->get_preview();

        $data['required_knowledges'] = $this->get_required_knowledges();
        $data['display_required_knowledges'] = $this->get_setting_display_required_knowledges();

        $data['course_contacts'] = $this->get_course_contacts();
        $data['display_course_contacts'] = $this->get_setting_display_course_contacts();

        $data['additional_description'] = $this->get_additional_description();
        $data['display_additional_description'] = $this->get_setting_display_additional_description();

        $data['description'] = $this->get_description();
        $data['display_description'] = $this->get_setting_display_description();

        $data['has_any_description'] = $data['display_additional_description'] || $data['display_description'];

        if (!empty($this->options['enrol_forms']))
        {
            $data['enrol_forms'] = $this->get_enrol_forms();
            $data['display_enrol_forms'] = $this->get_setting_display_enrol_forms();
        }

        $data['display_guest_access_to_course'] = $this->get_setting_display_guest_access_to_course();

        $data['user_have_access_to_course'] = $this->is_user_have_access_to_course();

        $data['display_login_url'] = $this->get_setting_display_login_url();

        $data['display_access_points'] = $this->get_setting_display_access_points();

        $data['logindata'] = (new login(get_enabled_auth_plugins(true)))->export_for_template($output);

        return $data;

    }

    public function get_preview()
    {
        $fs = get_file_storage();

        $preview = new \stdClass();
        $preview->component = 'local_crw';

        $context = \context_course::instance($this->get_course_id());
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        if ( count($files) )
        {
            foreach ($files as $file)
            {
                // Является ли файл изображением
                if (!$file->is_valid_image())
                {
                    continue;
                }

                return local_crw_get_preview($file, $preview);
            }
        }
    }

    private function get_setting_display_course_custom_fields()
    {
        // Настройка - следует ли отображать настраиваемые поля на странице описания курса
        $customfieldsview = local_crw_get_course_config($this->get_course_id(), 'custom_fields_view');

        // false - не установлено, если так, то используем значение по умолчанию - наследовать
        // default - наследовать из глобальной настройки витрины
        // 0 - не отображать
        // 1 - отображать
        if( $customfieldsview === false || (string)$customfieldsview === 'default' )
        {
            $customfieldsview = get_config('local_crw', 'custom_fields_view');
            if ($customfieldsview === false)
            {// значение глобальной настройки не установлено, а по умолчанию оно - 1
                $customfieldsview = 1;
            }
        }

        $customfields = $this->get_course_custom_fields();

        return $this->isset_not_null_customfields($customfields) && !empty($customfieldsview);
    }

    protected function isset_not_null_customfields($customfields)
    {
        if (is_array($customfields))
        {
            foreach($customfields as $customfield)
            {
                if (!empty($customfield['element']) &&
                    array_key_exists('is_null_value', $customfield) && !$customfield['is_null_value'])
                {
                    return true;
                }
                if (!empty($customfield['repeatgroup']) &&
                    array_key_exists('groups', $customfield) && is_array($customfield['groups'])
                    && $this->isset_not_null_customfields($customfield['groups']))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Получить список настраиваемых полей витрины со их значениями для текущего курса
     *
     * @return array - [fieldname => ['name' => fieldname, 'label' => label, 'value' => value], ...]
     */
    private function get_course_custom_fields()
    {
        if (is_null($this->custom_fields))
        {
            $customfields = get_config('local_crw', 'custom_course_fields');
            if (!empty($customfields))
            {
                $parseresult = \otcomponent_customclass\utils::parse($customfields);

                if ( $parseresult->is_form_exists() )
                {
                    // Форма
                    $customform = $parseresult->get_form();
                    // Кастомные поля формы
                    $cffields = $customform->get_fields();
                    // Сохраненные данные
                    $cfdata = custom_form_course_fields_get_data($this->get_course_id(), $cffields);
                    // получение сведений о повторяющихся группах полей (количество).
                    $repeatscount = custom_form_course_fields_count_repeats($cffields, $cfdata);
                    // инициализация формы
                    $customform->setForm(null, ['repeatscount' => $repeatscount]);
                    // Установка хранящихся в БД данных к форме
                    $customform->set_data($cfdata);

                    // Следующие типы полей не будем пытаться выводить, так как заранее известно, что пока не получится
                    $restrictedtypes = ['submit', 'filepicker', 'tags'];
                    $this->custom_fields = $customform->get_export_fields_for_template(null, $restrictedtypes);
                    $this->process_custom_fields($this->custom_fields);
                }
            }
        }

        return $this->custom_fields;
    }

    /**
     * Сбор информации по кастомным полям
     * на уровне категории может быть определено, что поле не должно отображаться - отметим это
     *
     * @param array $customfields
     */
    private function process_custom_fields(&$customfields)
    {
        foreach($customfields as &$customfield)
        {
            if (!empty($customfield['repeatgroup']) && !empty($customfield['groups']))
            {
                foreach($customfield['groups'] as &$group)
                {
                    if (!empty($group['elements']))
                    {
                        $this->process_custom_fields($group['elements']);
                    }
                }
                continue;
            }
            if (array_key_exists('element', $customfield) && !empty($customfield['element']) &&
                array_key_exists('basename', $customfield))
            {
                $fieldrole = local_crw_get_category_config(
                    $this->get_course_category()['id'],
                    'custom_field_'.$customfield['basename'].'_role'
                );
                if ($fieldrole != 'field_disabled')
                {
                    $customfield['display_in_list'] = true;
                }
            }
        }
    }

    /**
     * Требуется ли отображение доступные варианты получения доступа к курсу
     * @return boolean
     */
    private function get_setting_display_access_points()
    {
        return $this->is_user_have_access_to_course() ||
            $this->get_setting_display_login_url() ||
            $this->get_setting_display_guest_access_to_course() ||
            $this->get_setting_display_enrol_forms();
    }

    /**
     * Требуется ли отобразить ссылку на вход в курс в гостевом режиме доступа
     * @return boolean
     */
    private function get_setting_display_guest_access_to_course()
    {
        return !$this->is_user_have_access_to_course() && $this->is_user_have_guest_access_to_course();
    }

    /**
     * Требуется ли отобразить ссылку на страницу авторизации
     * @return boolean
     */
    private function get_setting_display_login_url()
    {
        // если среди форм для отображения есть хоть один отпэй
        // считаем, что кнопка для логина нам не нужна - она есть в отпэе, если потребуется
        // остальные способы записи, недоступные без авторизации - идут лесом
        $hasotpay = false;
        $enrolforms = $this->get_enrol_forms();
        foreach ($enrolforms as $enrolform) {
            $instance = $enrolform['instance'];
            if ($instance->enrol == 'otpay') {
                $hasotpay = true;
                break;
            }
        }
        return !$this->is_user_have_access_to_course() && (isguestuser() || !isloggedin()) && !$hasotpay;
    }

    /**
     * Требуется ли отобразить формы записи на курс
     * @return boolean
     */
    private function get_setting_display_enrol_forms()
    {
        $enrolforms = $this->get_enrol_forms();
        return !empty($enrolforms) && !$this->is_user_have_access_to_course();
    }

    /**
     * Получение контекста текущего курса
     * @return \context_course
     */
    private function get_context()
    {
        if (is_null($this->context))
        {
            $this->context = \context_course::instance($this->get_course_id(), MUST_EXIST);
        }
        return $this->context;
    }

    /**
     * Получение форм способов записи, доступных пользователю на странице описания курса
     * @return array - [html, ...]
     */
    private function get_enrol_forms()
    {
        global $DB, $USER;

        if (is_null($this->enrolforms))
        {
            $this->enrolforms = [];
            $enrols = $this->get_enrols();
            if (!is_null($enrols))
            {
                foreach($enrols as $enrolname => $enrol)
                {
                    foreach($enrol['instances'] as $instance)
                    {
                        $formhtml = $enrol['plugin']->enrol_page_hook($instance);
                        if ($formhtml)
                        {
                            $unauthorized = in_array($enrolname, ['sitecall', 'otpay']);

                            if (isloggedin() || $unauthorized)
                            {
                                $enrolform = [
                                    'instance' => $instance,
                                    'html' => $formhtml,
                                    'unauthorized' => $unauthorized
                                ];

                                if ($DB->get_record('user_enrolments', ['userid' => $USER->id, 'enrolid' => $instance->id]))
                                {
                                    $enrolform['enrolled'] = true;
                                    $enrolform['unenroll_url'] = $enrol['plugin']->get_unenrolself_link($instance);
                                    if (!empty($enrolform['unenroll_url']))
                                    {
                                        $enrolform['unenroll_url'] = $enrolform['unenroll_url']->out(false);
                                    }
                                }

                                $this->enrolforms[] = $enrolform;
                            }
                        }
                    }
                }
            }
        }

        return $this->enrolforms;
    }

    /**
     * Получение способов записи для отображения на описательной странице
     * @return array - [manual=>['plugin'=>enrol_manual_plugin, 'instances' => [stdClass, ...]], ...]
     */
    private function get_enrols()
    {
        if (is_null($this->enrols))
        {
            $this->enrols = [];
            $instances = enrol_get_instances($this->get_course_id(), true);
            foreach($instances as $instance)
            {
                $enrolplugin = enrol_get_plugin($instance->enrol);
                if (!is_null($enrolplugin))
                {
                    if (!array_key_exists($instance->enrol, $this->enrols))
                    {
                        $this->enrols[$instance->enrol] = [
                            'plugin' => $enrolplugin,
                            'instances' => []
                        ];
                    }
                    $this->enrols[$instance->enrol]['instances'][$instance->id] = $instance;
                    $this->enrols[$instance->enrol]['instances'][$instance->id]->{'enrol_'.$instance->enrol} = true;
                }
            }
        }
        return $this->enrols;
    }

    /**
     * Имеется ли в курсе рабочий гостевой доступ и может ли пользователь быть автоматически авторизован под гостем
     * @return boolean
     */
    private function is_user_have_guest_access_to_course()
    {
        global $CFG;

        $courseguestaccess = false;
        $enrols = $this->get_enrols();
        if (!is_null($enrols))
        {
            foreach($enrols as $enrol)
            {
                foreach($enrol['instances'] as $instance)
                {
                    $guestaccesstime = $enrol['plugin']->try_guestaccess($instance);
                    if ( $guestaccesstime !== false and $guestaccesstime > time() )
                    {
                        // в курсе есть способ записи с рабочим гостевым доступом
                        $courseguestaccess = true;
                        break 2;
                    }
                }
            }
        }

        // гостевой доступ включен и имеется автологин под гостем
        $guestautologin = !empty($CFG->guestloginbutton) && !empty($CFG->autologinguests);

        return ($courseguestaccess && (isloggedin() || $guestautologin));
    }

    /**
     * Имеется ли у пользователя доступ к курсу (админ или подписан на курс или имеет права)
     * @return boolean
     */
    private function is_user_have_access_to_course()
    {
        global $USER;

        if (!isloggedin())
        {
            return false;
        }

        return (
            local_crw_is_admin() ||
            is_enrolled($this->get_context(), $USER, '', true) ||
            has_capability('moodle/course:view', $this->get_context())
        );
    }

    /**
     * Получение описания курса (стандартное описание курса из настроек курса)
     * @return string
     */
    private function get_description()
    {
        if (is_null($this->description))
        {
            // Получим хелпер
            $chelper = new \coursecat_helper();
            $this->description = $chelper->get_course_formatted_summary($this->get_course_in_list(), [
                'overflowdiv' => false,
                'noclean' => true,
                'para' => false
            ]);
        }
        return $this->description;
    }

    /**
     * Требуется ли отобразить описание курса (стандартное описание курса из настроек курса)
     * @return boolean
     */
    private function get_setting_display_description()
    {
        return $this->get_course_in_list()->has_summary();
    }

    /**
     * Получение дополнительного описания (краткое, доп.настройка курса)
     * @return string - html
     */
    private function get_additional_description()
    {
        if (is_null($this->additional_description))
        {
            $this->additional_description = local_crw_get_course_config($this->get_course_id(), 'additional_description');
        }
        return $this->additional_description;
    }

    /**
     * Требуется ли отобразить дополнительное описание (краткое, доп.настройка курса)
     * @return boolean
     */
    private function get_setting_display_additional_description()
    {
        $descriptionview = local_crw_get_course_config($this->get_course_id(), 'additional_description_view');

        // false - не установлено, значит по умолчанию будем отображать
        // 0 - нигде
        // 1 - везде
        // 2 - только на странице описания курса
        // 3 - только на плитке (если поддерживается)
        return (($descriptionview === false || in_array((int)$descriptionview, [1,2])) && !empty($this->get_additional_description()));
    }

    /**
     * Получение объекта course_in_list для текущего курса
     * @return \core_course_list_element
     */
    private function get_course_in_list()
    {
        global $CFG;

        if (is_null($this->courseinlist))
        {
            $this->courseinlist = new \core_course_list_element($this->course);
        }

        return $this->courseinlist;
    }

    /**
     * Получение контактов курса
     * @return array - [[userid, username, rolename, userpic, url], ...]
     */
    private function get_course_contacts()
    {
        global $OUTPUT;

        if (is_null($this->coursecontacts))
        {
            $this->coursecontacts = [];

            foreach ( $this->get_course_in_list()->get_course_contacts() as $userid => $coursecontact )
            {
                // Получаем пользователя
                $user = get_complete_user_data('id', $userid);

                $this->coursecontacts[] = [
                    'userid' => $userid,
                    'username' => $coursecontact['username'],
                    'rolename' => $coursecontact['rolename'],
                    'userpic' => $OUTPUT->user_picture($user, ['size' => '50']),
                    'url' => (new moodle_url('/user/view.php', ['id' => $userid]))->out(false),
                    'messageurl' => (new moodle_url('/message/index.php', ['id' => $userid]))->out(false),
                ];
            }
        }

        return $this->coursecontacts;
    }

    /**
     * Требуется ли отобразить контакты курса
     * @return boolean
     */
    private function get_setting_display_course_contacts()
    {
        $hidecourescontacts = local_crw_get_course_config($this->get_course_id(), 'hide_course_contacts');

        // false - не установлено, значит по умолчанию будем наследовать
        // default - наследовать (использовать глобальную настройку)
        if( $hidecourescontacts === false || (string)$hidecourescontacts === 'default' )
        {
            $hidecourescontacts = get_config('local_crw', 'hide_course_contacts');
        }

        return $this->get_course_in_list()->has_course_contacts() && ! $hidecourescontacts;
    }

    /**
     * Получение стоимости курса
     * @return string - html
     */
    private function get_price()
    {
        if (is_null($this->price))
        {
            $this->price = local_crw_get_course_price($this->course);
        }
        return $this->price;
    }

    /**
     * Получение даты начала курса
     * @return int - timestamp
     */
    private function get_startdate()
    {
        if (is_null($this->startdate) && isset($this->course->startdate))
        {
            $this->startdate = $this->course->startdate;
        }
        return $this->startdate;
    }

    /**
     * Получение навыков, необходимых для изучения курса
     * @return array - [value, last]
     */
    private function get_required_knowledges()
    {
        if (is_null($this->required_knowledges))
        {
            $data = local_crw_get_course_config($this->get_course_id(), 'required_knowledge', true);
            if (!empty($data))
            {
                $this->required_knowledges = [];
                foreach($data as $requiredknowledge)
                {
                    $this->required_knowledges[] = [
                        'value' => $requiredknowledge->value
                    ];
                }
                if (!empty($this->required_knowledges))
                {
                    $this->required_knowledges[count($this->required_knowledges)-1]['last'] = true;
                }
            }
        }
        return $this->required_knowledges;
    }

    /**
     * Требуется ли отобразить необходимые для изучения курса навыки
     * @return boolean
     */
    private function get_setting_display_required_knowledges()
    {
        return !empty($this->get_required_knowledges());
    }

    /**
     * Получение данных для отображения галереи курса
     * @return array - [has_files, one_image, images=>[url, name, main], files=>[url, name, icon]]
     */
    private function get_gallery()
    {
        if (is_null($this->gallery))
        {
            $this->gallery = [
                'images' => [],
                'files' => []
            ];

            // файлы, настроенные на отображение в галерее
            $descriptionfiles = [];
            $data = local_crw_get_course_config($this->get_course_id(), 'description_files');
            if ($data && $descriptionfiles = json_decode($data, true)) {
                $descriptionfiles = array_keys($descriptionfiles);
            }

            foreach ( $this->get_course_in_list()->get_course_overviewfiles() as $file )
            {
                if (!$data || in_array($file->get_id(), $descriptionfiles))
                {// файл должен быть отображен в галерее

                    $filedata = [
                        'url' => moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            null,
                            $file->get_filepath(),
                            $file->get_filename()
                        )->out(false),
                        'name' => $file->get_filename(),
                    ];

                    if ($file->is_valid_image())
                    {// файл является изображением

                        $filedata['main'] = empty($this->gallery['images']);
                        $this->gallery['images'][] = $filedata;

                    } else
                    {// Другой тип файла

                        $filedata['icon'] = file_file_icon($file, 24);
                        $filedata['url']->param('forcedownload', true);
                        $this->gallery['files'][] = $filedata;
                    }
                }
            }

            $this->gallery['has_files'] = (count($this->gallery['files']) > 0);
            $this->gallery['one_image'] = (count($this->gallery['images']) == 1);
        }

        return $this->gallery;
    }

    /**
     * Требуется ли отобразить галерею
     * @return boolean
     */
    private function get_setting_display_gallery()
    {
        $hidecouresgallery = local_crw_get_course_config($this->get_course_id(), 'hide_course_gallery');
        // false - не установлено, значит по умолчанию будем наследовать
        // default - наследовать (использовать глобальную настройку)
        // 0 - нет, не скрывать, отображать
        // 1 - да, скрывать
        if ($hidecouresgallery === false || (string)$hidecouresgallery === 'default')
        {
            $hidecouresgallery = get_config('local_crw', 'hide_course_gallery');
        }
        return !$hidecouresgallery;
    }

    /**
     * Требуется ли отобразить стоимость курса
     * @return boolean
     */
    private function get_setting_display_price()
    {
        $displayprice = local_crw_get_course_config($this->get_course_id(), 'display_price');
        // false - не установлено, значит по умолчанию будем отображать
        // 0 - нигде
        // 1 - везде
        // 2 - только на странице описания курса
        // 3 - только на плитке (если поддерживается)
        return ( $displayprice === false || in_array((int)$displayprice, [1,2]) ) && !empty($this->get_price());
    }

    /**
     * Требуется ли отобразить дату начала курса
     * @return boolean
     */
    private function get_setting_display_startdate()
    {
        $displaydate = local_crw_get_course_config($this->get_course_id(), 'display_startdate');
        // false - не установлено, значит по умолчанию будем отображать
        // 0 - нигде
        // 1 - везде
        // 2 - только на странице описания курса
        // 3 - только на плитке (если поддерживается)
        return ( $displaydate !== false && in_array((int)$displaydate, [1,2]) ) && !is_null($this->get_startdate());
    }

    /**
     * Получение иконок способов записи на курс
     *
     * @param renderer_base $output
     *
     * @return array - массив иконок, содержащих данные для рендеринга пиксиконки
     */
    private function get_enrol_icons(renderer_base $output)
    {
        if (is_null($this->enrolicons))
        {
            $this->enrolicons = [];

            foreach (enrol_get_course_info_icons($this->course) as $pixicon)
            {
                $this->enrolicons[] = $pixicon->export_for_template($output);
            }
        }

        return $this->enrolicons;
    }

    /**
     * Требуется ли отобразить иконки способов записи на курс
     * @return boolean
     */
    private function get_setting_display_enrol_icons()
    {
        $displayicons = local_crw_get_course_config($this->get_course_id(), 'display_enrolicons');
        // false - не установлено, значит по умолчанию будем отображать
        // 0 - нигде
        // 1 - везде
        // 2 - только на странице описания курса
        // 3 - только на плитке (если поддерживается)
        return ( $displayicons === false || in_array((int)$displayicons, [1,2]) );
    }

    /**
     * Требуется ли отобразить категории
     * @return boolean
     */
    private function get_setting_display_categories()
    {
        return ($this->get_coursecat_view() > 0);
    }

    /**
     * Требуется ли отобразить категории в виде текста
     * @return boolean
     */
    private function get_setting_display_categories_as_text()
    {
        return ($this->get_coursecat_view() == 1);
    }

    /**
     * Требуется ли отобразить категории в виде ссылок
     * @return boolean
     */
    private function get_setting_display_categories_as_links()
    {
        return ($this->get_coursecat_view() == 2);
    }

    /**
     * Получении опции отображения категории
     * @return int
     */
    private function get_coursecat_view()
    {
        if (is_null($this->coursecatview))
        {
            // Получение настройки (новой)
            $this->coursecatview = local_crw_get_course_config($this->get_course_id(), 'coursecat_view');

            if (!$this->coursecatview)
            {// Новую настройку не задали, проверка наличия старых настроек

                if (local_crw_get_course_config($this->get_course_id(), 'display_coursecat'))
                {// Отображение категории в виде текста
                    $this->coursecatview = 1;
                }

                if (local_crw_get_course_config($this->get_course_id(), 'display_coursecat_link'))
                {// Отображение категории в виде ссылок
                    $this->coursecatview = 2;
                }
            }
        }

        return $this->coursecatview;
    }

    /**
     * Получение данных о категории курса
     * @return array - [id, name, url]
     */
    public function get_course_category()
    {
        $category = $this->get_category_data($this->course->category);
        unset($category['object']);
        return $category;
    }

    /**
     * Получение данных о родительских категориях курса (путь до текущей)
     * @return array - [[id, name, url], ...]
     */
    public function get_course_category_parents()
    {
        $parents = [];

        if ( $category = $this->get_category_data($this->course->category) )
        { // Категория найдена

            // Добавим всех родителей категории
            foreach($category['object']->get_parents() as $parentid)
            {
                $parent = $this->get_category_data($parentid);
                unset($parent['object']);
                $parents[] = $parent;
            }
        }

        return $parents;
    }

    /**
     * Получение данных об указанной категории
     * @param int $categoryid идентификатор категории
     *
     * @return array - [id, name, url, coursecat object]
     */
    private function get_category_data($categoryid)
    {
        if (!array_key_exists($categoryid, $this->categories) &&
            $category = core_course_category::get($categoryid, IGNORE_MISSING))
        {
            $categorydata = [
                'id' => $category->id,
                'name' => $category->get_formatted_name(),
                'url' => (new \moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false),
                'object' => $category
            ];
            $this->categories[$categoryid] = $categorydata;
        }
        return $this->categories[$categoryid];
    }
}