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
 * Блок "Поделиться ссылкой". Класс блока.
 *
 * @package    block_otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_otshare\fb;
use block_otshare\ok;
use block_otshare\tw;
use block_otshare\vk;
use block_otshare\gp;

class block_otshare extends block_base 
{

    /**
     * Initializes the block
     * Sets up the block title
     */
    public function init() 
    {
        $this->title = get_string('title', 'block_otshare');
    }
    
    public function get_content()
    {
        if ($this->content != null)
        {
            return $this->content;
        }
        
        $this->content = null;
        $html = '';
        
        // Получение URL, которым необходимо поделиться
        $url = $this->get_url();
        // Метатеги для красивого шаринга
        $this->set_meta_properties($url);
        
        if ( $url )
        {// Категория курса не скрыта
            //ВКонтакте
            if ( ! empty($this->config->vk) )
            {// необходимо отобразить
                $placeholderlinktext = '';
                if ( ! empty($this->config->authentic) )
                {//включен режим загрузки аутентичных кнопок через js
                    //формирование строки для ссылки до исполнения js
                    $placeholderlinktext = format_text(
                        get_string('vk_placeholder_link_text','block_otshare'),
                        FORMAT_HTML
                    );
                }
                //объект для формирования ссылки/кнопки
                $sharer = new vk(
                    $this->instance->id,
                    $url,
                    $placeholderlinktext
                );
                //получение ссылки/кнопки
                $html .= $sharer->get_share();
            }
            
            //Facebook
            if ( ! empty($this->config->fb) )
            {// необходимо отобразить
                $placeholderlinktext = '';
                if ( ! empty($this->config->authentic) )
                {//включен режим загрузки аутентичных кнопок через js
                    //формирование строки для ссылки до исполнения js
                    $placeholderlinktext = format_text(
                        get_string('fb_placeholder_link_text','block_otshare'), 
                        FORMAT_HTML
                    );
                }
                //объект для формирования ссылки/кнопки
                $sharer = new fb(
                    $this->instance->id,
                    $url,
                    $placeholderlinktext
                );
                //получение ссылки/кнопки
                $html .= $sharer->get_share();
            }
            
            //Twitter
            if ( ! empty($this->config->tw) )
            {// необходимо отобразить
                $placeholderlinktext = '';
                if ( ! empty($this->config->authentic) )
                {//включен режим загрузки аутентичных кнопок через js
                    //формирование строки для ссылки до исполнения js
                    $placeholderlinktext = format_text(
                        get_string('tw_placeholder_link_text','block_otshare'), 
                        FORMAT_HTML
                    );
                }
                //объект для формирования ссылки/кнопки
                $sharer = new tw(
                    $this->instance->id,
                    $url,
                    $placeholderlinktext
                );
                //получение ссылки/кнопки
                $html .= $sharer->get_share();
            }
            
            //Одноклассники
            if ( ! empty($this->config->ok) )
            {// необходимо отобразить
                $placeholderlinktext = '';
                if ( ! empty($this->config->authentic) )
                {//включен режим загрузки аутентичных кнопок через js
                    //формирование строки для ссылки до исполнения js
                    $placeholderlinktext = format_text(
                        get_string('ok_placeholder_link_text','block_otshare'), 
                        FORMAT_HTML
                    );
                }
                //объект для формирования ссылки/кнопки
                $sharer = new ok(
                    $this->instance->id,
                    $url,
                    $placeholderlinktext
                );
                //получение ссылки/кнопки
                $html .= $sharer->get_share();
            }
            
            //Google+
            if ( ! empty($this->config->gp) )
            {// необходимо отобразить
                $placeholderlinktext = '';
                if ( ! empty($this->config->authentic) )
                {//включен режим загрузки аутентичных кнопок через js
                    //формирование строки для ссылки до исполнения js
                    $placeholderlinktext = format_text(
                        get_string('gp_placeholder_link_text','block_otshare'), 
                        FORMAT_HTML
                    );
                }
                //объект для формирования ссылки/кнопки
                $sharer = new gp(
                    $this->instance->id,
                    $url,
                    $placeholderlinktext
                );
                //получение ссылки/кнопки
                $html .= $sharer->get_share();
            }
            
            if ( ! empty($html) )
            {//Есть что выводить
                //Добавление пояснения к ссылкам
                $html .= html_writer::div(
                    format_text($this->config->explain),
                    'otshare_explain'
                );
                $this->content = new stdClass();
                $this->content->text = $html;
            }
        }
    }
    
    /**
     * Do not allow multiple instances
     *
     * @return bool
     */
    protected function set_meta_properties($url)
    {
        GLOBAL $CFG, $DB;
        
        // Дефолтные параметры
        $description = '';
        $file_url = '';
        $url_image = '';
        $files = [];
        
        // Получим данные о курсе
        $course_data = $this->page->course;
        
        if ( $this->is_crw_installed() )
        {
            // Получить все свойства курса
            $course_settings_data = $DB->get_records(
                    'crw_course_properties',
                    [
                        'courseid' => $course_data->id
                    ]
                    );
        }
        
        // Получим краткое описание курса из доп настроек CRW
        if ( ! empty($course_settings_data) )
        {
            foreach ( $course_settings_data as $config )
            {
                if ( $config->name == 'additional_description' )
                {
                    $description = strip_tags($config->value);
                }
            }
        }
        
        // Получим первое попавшееся изображение из описания курса
        // Подключение менеджера файлов
        $fs = get_file_storage();

        if ( ! empty($this->config->block_image) )
        {
            // Получение файлов описания курса
            $files = $fs->get_area_files(
                    context_system::instance()->id,
                    'block_otshare',
                    'public',
                    $this->page->course->id
                    );
            
            if ( ! empty($files) )
            {
                foreach ( $files  as $file)
                {
                    if ( $file->is_valid_image() )
                    {
                        $url_image = moodle_url::make_pluginfile_url(
                                $file->get_contextid(),
                                $file->get_component(),
                                $file->get_filearea(),
                                $file->get_itemid(),
                                $file->get_filepath(),
                                $file->get_filename()
                                );
                        break;
                    }
                }
            }
        } else 
        {
            // Получение файлов описания курса
            $files = $fs->get_area_files(
                    $this->page->context->id,
                    'course',
                    'overviewfiles',
                    0
                    );
            
            if ( ! empty($files) )
            {
                foreach ( $files  as $file)
                {
                    if ( $file->is_valid_image() )
                    {
                        $url_image = moodle_url::make_pluginfile_url(
                                $file->get_contextid(),
                                $file->get_component(),
                                $file->get_filearea(),
                                '',
                                $file->get_filepath(),
                                $file->get_filename()
                                );
                        break;
                    }
                }
            }
        }
        
        // Установим разметку для красивого шаринга
        $CFG->additionalhtmlhead .= "
            <meta property=\"og:url\" content=\"$url\" />
            <meta property=\"og:title\" content=\"$course_data->fullname\" />
            <meta property=\"og:type\" content=\"website\" />
            <meta property=\"og:description\" content=\"$description\" />
            <meta property=\"og:image\" content=\"$url_image\" />
        ";
    }
    
    /**
     * Do not allow multiple instances
     *
     * @return bool
     */
    public function instance_allow_multiple() 
    {
        return false;
    }
    
    
    /**
     * Проверяет установлена ли витрина курсов
     * 
     * @return boolean
     */
    private function is_crw_installed()
    {
        $installlist = core_plugin_manager::instance()->get_installed_plugins('local');
        return array_key_exists('crw', $installlist);
    }
    
    
    /**
     * Returns url to set for social buttons based on the url scope configuration
     *
     * @return moodle_url
     */
    private function get_url() {
        global $CFG;
        
        if ( $this->page->context->contextlevel == CONTEXT_COURSE || 
             $this->page->context->contextlevel == CONTEXT_MODULE )
        {// Пользователь находится внутри курса
            $coursecontext = $this->page->context->get_course_context();
            
            // Флаг, что категория не скрыта
            $visible_category = true;
            try 
            {
                $category = core_course_category::get($this->page->course->category);
            } catch ( moodle_exception $e )
            {
                $visible_category = false;
            }
            
            if ( $visible_category )
            {// Категория не скрыта
                if ( $this->is_crw_installed() )
                {// Среди плагинов имеется витрина курсов
                    // URL на страницу описания курса в витрине
                    $url = new moodle_url('/local/crw/course.php', [
                        'id' => $coursecontext->instanceid
                    ]);
                } else
                {// Витрины нет, но вы держитесь там
                    $url = new moodle_url($coursecontext->get_url());
                }
            } else 
            {// Категория скрыта, скрываем кнопку поделиться описанием курса
                return false;
            }
        } else
        {// Ссылка на страницу, с которой пользователь делится
            $url = $this->page->url;
        }
    
        return $url;
    }
}