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
 * Сертификаты. Класс блока.
 *
 * @package    block
 * @subpackage simplecertificate
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ 

defined('MOODLE_INTERNAL') || die();

use block_simplecertificate\local\utilities;

require_once('locallib.php');

class block_simplecertificate extends block_base 
{
    /**
     * Инициализация блока
     */
    public function init() 
    {
        $this->title = get_string('table_my_certificates', 'block_simplecertificate');
    }

    /**
     * Вернуть контент блока
     *
     * @return stdClass contents of block
     */
    public function get_content() 
    {
        // Получение глобальных переменных
        global $USER, $CFG, $PAGE;
        
        // Подключение библиотек
        require_once($CFG->dirroot.'/user/profile/lib.php');
        
        $userid = $USER->id;
        if( $PAGE->pagetype == 'user-profile' )
        {
            $userid = optional_param('id', $USER->id, PARAM_INT);
        }

        // Предотвращение повторной генерации контента дублирующихся блоков
        if ( $this->content !== NULL ) 
        {
            return $this->content;
        }
        
        // Объявление контента блока
        $this->content = new stdClass();
        $this->content->text = '';
        
        // Контекст страницы
        $context = context_course::instance($PAGE->course->id);
        
        // Формирование блока сертификатов пользователя
        if ( empty($userid) || is_guest($context, $userid) ) 
        {// Пользователь не может видеть сертификаты
            $this->content->text .= html_writer::div(get_string('error_login_to_system', 'block_simplecertificate'));
        } else
        {// Отобразить краткую таблицу сертификатов
            
            // Получение сертификатов
            $certificates = utilities::get_certificates([
                'users' => $userid,
                'active' => true
            ]);
            // Отображение сертификатов
            $this->content->text .= utilities::get_certificates_short_view($certificates, 0, 0);
        }
        
        // Отображение ссылки на список сертификатов
        if ( has_capability('block/simplecertificate:viewsertificateslist', $PAGE->context) )
        {
            // Формирование URL для перехода 
            if ( get_site()->id == $PAGE->course->id )
            {// Страница просмотра без учета курса
                $url = new moodle_url(
                    '/blocks/simplecertificate/view.php'
                );
            } else 
            {// Страинца просмотра сертификатов в курсе
                $url = new moodle_url(
                    '/blocks/simplecertificate/view.php',
                    ['courseid' => $PAGE->course->id]
                );
            }
            
            // Отображение ссылки на просмотр всех сертификатов
            $this->content->text .= html_writer::link(
                $url, 
                get_string('to_certificate_panel', 'block_simplecertificate'),
                ['class' => 'btn btn-primary button']
            );
        }
        $this->content->footer = '';
        
        return $this->content;
    }

    /**
     * Поддержка блоком страницы конфигурации
     *
     * @return boolean
     */
    public function has_config() 
    {
        return true;
    }

    /**
     * Отображение блока на страницах
     *
     * @return array
     */
    public function applicable_formats() 
    {
        return array(
                'all' => true
        );
    }
}
