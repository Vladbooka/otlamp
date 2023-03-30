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
 * The linksocial block
 *
 * @package   block_linksocial
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Obviously required
require_once($CFG->dirroot . '/lib/datalib.php');
require_once($CFG->dirroot . '/blocks/linksocial/lib.php');

class block_linksocial extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_linksocial');
    }

    function specialization() 
    {
        $plugins = get_enabled_auth_plugins();
        
        // Добавляем настройки доступных типов авторизации 
        if (!isset($this->config)) {
            $this->config = new stdClass();
        }
        foreach ($plugins as $plugin) {
            $this->config->$plugin = get_config('block_linksocial', $plugin);
            
            if (is_null($this->config->$plugin)) {
                $this->config->$plugin = true;
            }
        }
    }
    
    /**
     * Доступность блока
     * 
     */
    function applicable_formats() 
    {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return false;
    }
    
    function has_config() {
        return true;
    }

    function get_content() 
    {
        global $PAGE, $DB, $USER;
        
        if ( $this->content !== NULL ) 
        {
            return $this->content;
        }
        if ( empty($this->instance) ) 
        {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->footer = '';
        /*$this->content->footer = html_writer::link(
                new moodle_url('/blocks/linksocial/updateprofile.php'), 
                get_string('page_updateprofile', 'block_linksocial')
        );*/
        
        $url = new moodle_url('/blocks/linksocial/script.js');
        $PAGE->requires->js($url);
        
        // Получить настройку
        $enable_notice = get_config('block_linksocial', 'enable_notice');
        
        $has_accounts = $DB->count_records('auth_otoauth', array('userid' => $USER->id, 'active' => 1));
        if ( 
                ! empty($enable_notice) && ! isset($_COOKIE['social_notice']) && 
                isloggedin() && ! isguestuser() && empty($has_accounts)
        ) 
        {// Отобразить блок напоминания
            $modal = '';
            $modal .= html_writer::start_div('linksocial_wrapper');
            $modal .= html_writer::start_div('linksocial_message_wrapper');
            $modal .= html_writer::div('', '',array('id' => 'linksocial_wrapper_bg'));
            $modal .= html_writer::start_div('linksocial_message');
            $modal .= html_writer::div('×', 'linksocial_message_close', array('id' => 'linksocial_message_close' ));
            $modal .= html_writer::tag('h2', get_string('noticemessage', 'block_linksocial'));
            $modal .= html_writer::div(show_linksocial_interface(['available' => true]), 'linksocial_message_links');
            $modal .= html_writer::end_div();
            $modal .= html_writer::end_div();
            $modal .= html_writer::end_div();
            $this->content->footer .= html_writer::div($modal, '', array('id' => 'linksocial_modal'));
            // Устанавливаем куки
            setcookie('social_notice', true, time() + 6638328000);
        }
        $this->content->text = show_account_status();
        // Проверяем тип авторизации ('auth') пользователя 
        if ( $this->check_user_auth() ) 
        {
            $this->content->text .= show_linksocial_interface();
        } else {
            $this->content->text .= get_string('linkdisabledbyadmin', 'block_linksocial');
        }
        return $this->content;
    }
    
    /**
     * Проверяет, имеет ли пользователь доступ к плагину авторизации
     * 
     * @return boolean
     *      - true если имеет
     *      - false если нет
     */
    private function check_user_auth() {
        global $USER;
        $plugins = get_enabled_auth_plugins();
        if ( ! isset($USER->auth) )
        {
            return false;
        }
        if (array_search($USER->auth, $plugins) !== false) {
            return true;
        }
        return false;
    }       
}