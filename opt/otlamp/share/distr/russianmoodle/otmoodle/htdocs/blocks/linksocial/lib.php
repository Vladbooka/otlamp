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
 * The linksocial block helper functions and callbacks
 *
 * @package   block_linksocial
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/auth/otoauth/auth.php');
require_once($CFG->dirroot . '/auth/otoauth/lib.php');
require_once($CFG->libdir . '/outputcomponents.php');

/** Получить список социальных кнопок
 * 
 * @return array - каждая кнопка содержит следующие поля:
 * 'url'  => moodle_url
 * 'icon' => pix_icon
 * 'name' => string
 */
function get_social_buttons() {
    $otoauth = new auth_plugin_otoauth();
    if ($socialbuttons = $otoauth->loginpage_idp_list('none')) {
        return $socialbuttons;
    }
    return array();
}

/** 
 * Показать интерфейс привязки социальных сетей к аккаунту
 * 
 * @return string - HTML-код для отображения в блоке
 */
function show_linksocial_interface($opt = array()) 
{
    global $CFG, $DB, $USER, $OUTPUT;
    
    $socialbuttons = get_social_buttons();
    $conds = array('userid' => $USER->id, 'active' => 1);
    $linked = array();
    if ($linkedsocials = $DB->get_records('auth_otoauth', $conds)) {
        foreach ($linkedsocials as $sociallink) {
            $linked[$sociallink->service] = $sociallink;
        }
    }
    if (!isloggedin()) {
        return get_string('noguests', 'block_linksocial');
    }
    if ($socialbuttons === false OR empty($socialbuttons)) {
        return get_string('nosocial', 'block_linksocial');
    }
    if (!is_array($socialbuttons)) {
        debugging('$socialbuttons is not an array!', DEBUG_DEVELOPER);
    }

    $contentslinked = '';
    $contentsavailable = '';
    $attributeslist = array('class' => 'potentialidplist');
    $attributesbutton = array('class' => 'potentialidp');
    // Выводим подключённые социалки
    $attributesbutton['class'] .= ' linked';
    foreach ($socialbuttons as $service => $button) {
        if (array_key_exists($service, $linked)) {
            $href = $button['url']->out();
            $attributeslinklist = array('title'=> $button['name'],
                                        'class'=> 'list');
            $attributeslink = array('href' => $href,
                                    'title'=> $button['name']);
            $renderedbutton = $OUTPUT->render($button['icon'], $button['name']);
            $row = html_writer::tag('a', $renderedbutton, $attributeslinklist);
            // Кнопка 'Отключить'
            $disconnecturl = new moodle_url('/auth/otoauth/disconnect.php',
                    array('id'       => $linked[$service]->id,
                          'wantsurl' => new moodle_url('/my')));
            $row .= $OUTPUT->single_button($disconnecturl, get_string('disconnect', 'block_linksocial'));
            $contentslinked .= html_writer::tag('div', $row, $attributesbutton);
        }
    }
    
    // Выводим доступные социалки
    $attributesbutton['class'] = 'potentialidp available';
    foreach ($socialbuttons as $service => $button) {
        if (!array_key_exists($service, $linked)) {
            // Добавим дополнительные параметры, которые подхватит плагин авторизации otoauth
            $params = [
                'link' => $USER->id,
                'secret' => generate_user_secret($USER->id),
                'wantsurl' => $CFG->wwwroot . '/my'
            ];
            $href = new moodle_url($button['url'], $params);
            
            $attributeslink = array('href' => $href,
                                    'title'=> $button['name']);
            $renderedbutton = $OUTPUT->render($button['icon'], $button['name']);
            $link = html_writer::tag('a', $renderedbutton, $attributeslink);
            $contentsavailable .= html_writer::tag('div', $link, $attributesbutton);
        }
    }
    // Заголовки и контент
    $result = '';
    if ( ! empty($contentslinked ) && ! ( isset($opt['available']) && ! empty($opt['available']) ) ) 
    {
        $result .= html_writer::tag('h2', get_string('linked', 'block_linksocial'));
        $result .= html_writer::tag('div', $contentslinked, $attributeslist);
    }
    $result .= html_writer::tag('h2', get_string('available', 'block_linksocial'));
    $result .= html_writer::tag('div', $contentsavailable, $attributeslist);
    return $result;
}

/**
 * Показать статус аккаунта пользователя
 * 
 * @return string - HTML-код для отображения в блоке
 */
function show_account_status() {
    global $OUTPUT, $USER, $CFG;
    
    $link = new moodle_url($CFG->wwwroot . '/blocks/linksocial/signup.php');
    $result = '';
    $status = get_string('emailsend', 'block_linksocial');
    $button = $OUTPUT->single_button($link, get_string('resend', 'block_linksocial'));
    if ( isset($USER->auth) && isset($USER->confirmed) )
    {
        if ($USER->auth == 'otoauth' && $USER->confirmed == 0) 
        {
            $result .= html_writer::tag('h2', $status . $button);
        }
    }
    
    return $result;
}

/**
 * Вернуть HTML таблицы статистики использования 
 * аккаунтов соцсетей, сгруппированных по типу
 * 
 * @param int $page - Номер страницы
 * @param int $limit - Лимит записей
 * 
 * @return string - HTML-код таблицы
 */
function block_linksocial_statitics_bytype($page, $limit)
{
    
    global $DB;
    
    $html = '';
    // Смещение
    $limitfrom = $page * $limit;
    
    // Полученение списка аккаунтов
    $accounts = $DB->get_records('auth_otoauth', null, ' service, userid ASC ', '*', $limitfrom, $limit);
    
    if ( empty($accounts) )
    {// Записи не найдены
        $html .= get_string('records_not_found', 'block_linksocial');
        return $html;
    }
    // Подготовка к формированию строк
    $rows = array();
    
    // Кэш пользователей
    $usercache = array();
    foreach ( $accounts as $account )
    {
        // ID пользователя
        $userid = $account->userid;
        if ( ! isset($usercache[$userid]) )
        {// Пользователь еще не определен
            $usercache[$userid] = $DB->get_record('user', array('id' => $userid));
        }
        
        // Получить Имя пользовтеля
        if ( is_object($usercache[$userid]) )
        {// Пользователь определен
            $name = fullname($usercache[$userid]);
            $fullname = html_writer::link( new moodle_url('/user/profile.php', array('id' => $userid)), $name);
        } else 
        {// Пользователя нет в системе
            $fullname = get_string('undefined_user', 'block_linksocial');
        }
        
        // Время создания и последнего доступа
        $timezone = get_user_timezone();
        $datestr = userdate($account->datacreate, get_string('strftimedatetimeshort'), $timezone);
        $laststr = userdate($account->lastaccess, get_string('strftimedatetimeshort'), $timezone);
        // Статус аккаунта
        if ( empty($account->active) )
        {
            $active = get_string('inactive', 'block_linksocial');    
        } else
        {
            $active = get_string('active', 'block_linksocial');
        }
        
        $rows[] = array($fullname, $account->service, $datestr, $laststr, $active);
    }
    
    // Формирование таблицы
    $table = new html_table();
    $table->head = array(
            'h1' => get_string('fullname', 'block_linksocial'),
            'h2' => get_string('service', 'block_linksocial'),
            'h3' => get_string('datacreate', 'block_linksocial'),
            'h4' => get_string('lastaccess', 'block_linksocial'),
            'h5' => get_string('status', 'block_linksocial')     
        );
    $table->data = $rows;
    $table->align = array(
            'h1' => 'center', 
            'h2' => 'center', 
            'h3' => 'center', 
            'h4' => 'center',
            'h5' => 'center'
    );
    $html .= html_writer::table($table);
    
    return $html;
}

/**
 * Вернуть HTML таблицы статистики использования
 * аккаунтов соцсетей, сгруппированных по пользователям
 *
 * @param int $page - Номер страницы
 * @param int $limit - Лимит записей
 *
 * @return string - HTML-код таблицы
 */
function block_linksocial_statitics_byuser($page, $limit)
{
    global $DB;
    
    $html = '';
    // Смещение
    $limitfrom = $page * $limit;
    // Полученение списка пользователей
    $users = $DB->get_records('user', array('auth' =>'otoauth', 'deleted' => '0'), 'firstname ASC', '*', $limitfrom, $limit);

    if ( empty($users) )
    {// Пользователи не найдены
        $html .= get_string('records_not_found', 'block_linksocial');
        return $html;
    }
    
    // Подготовка к формированию строк
    $rows = array();
    
    // Формирование строк таблицы
    foreach ( $users as $user )
    {
        // Формирование таблицы привязанных аккаунтов
        $accountstable = new html_table();
        // Заголовок
        $accountstable->head = array(
                'h1' => get_string('service', 'block_linksocial'),
                'h2' => get_string('datacreate', 'block_linksocial'),
                'h3' => get_string('lastaccess', 'block_linksocial'),
                'h4' => get_string('status', 'block_linksocial')
        );
        // Получение списка аккаунтов
        $accounts = $DB->get_records('auth_otoauth', array('userid' => $user->id));
        
        if ( empty($accounts) )
        {// Аккаунты не найдены
            continue;
        }
        
        $accountsrows = array();
        foreach ( $accounts as $account )
        {   
            // Формироване строки аккаунта
            $timezone = get_user_timezone();
            $datestr = userdate($account->datacreate, get_string('strftimedatetimeshort'), $timezone);
            $laststr = userdate($account->lastaccess, get_string('strftimedatetimeshort'), $timezone);
            if ( empty($account->active) )
            {
                $active = get_string('inactive', 'block_linksocial');
            } else 
            {
                $active = get_string('active', 'block_linksocial');
            }
            $accountsrows[] = array($account->service, $datestr, $laststr, $active);
        }

        $accountstable->data = $accountsrows;
        $accountstable->align = array('h1' => 'center', 'h2' => 'center', 'h3' => 'center', 'h4' => 'center');
        $accountstablehtml = html_writer::table($accountstable);
        // Формирование строки пользователя
        $rows[] = array(fullname($user), $accountstablehtml);
    }
    
    // Формирование таблицы
    $table = new html_table();
    $table->head = array(
            'h1' => get_string('fullname', 'block_linksocial'), 
            'h2' => get_string('accounts', 'block_linksocial')
    );
    $table->data = $rows;
    $table->align = array('h1' => 'center', 'h2' => 'center');
    $html .= html_writer::table($table);
    
    return $html;
}

/**
 * Вернуть HTML таблицы статистики использования
 * аккаунтов соцсетей.
 *
 * @return string - HTML-код таблицы
 */
function block_linksocial_statitics_counter()
{
    global $DB;
    
    $html = '';
    
    // Полученение списка аккаунтов
    $accounts = $DB->get_records('auth_otoauth', null);
    
    if ( empty($accounts) )
    {// Записи не найдены
        $html .= get_string('records_not_found', 'block_linksocial');
        return $html;
    }
    // Подготовка к формированию строк
    $rows = array();
    
    // Кэш типов аккаунтов
    $cache = array();
    foreach ( $accounts as $account )
    {
        // ID пользователя
        $type = $account->service;
        if ( isset($cache[$type]) )
        {// Для данного типа уже просчитаны данные
            continue;
        }
        // Подстчет числа активных и неакивных линковок
        $activecount = $DB->count_records('auth_otoauth', array('service' => $type, 'active' => 1));
        $unactive = $DB->count_records('auth_otoauth', array('service' => $type, 'active' => 0));
        $cache[$type] = array('active' => $activecount, 'unactive' => $unactive);
        // Добавляем строку
        $rows[] = array($type, intval($activecount), intval($unactive));
    }
    
    // Формирование таблицы
    $table = new html_table();
    $table->head = array(
            'h1' => get_string('service', 'block_linksocial'),
            'h2' => get_string('activecount', 'block_linksocial'),
            'h3' => get_string('unactivecount', 'block_linksocial')
        );
    $table->data = $rows;
    $table->align = array('h1' => 'center', 'h2' => 'center', 'h3' => 'center');
    $html .= html_writer::table($table);
    return $html;
}

function block_linksocial_get_accounts_count()
{
    global $DB;
    $count = $DB->count_records('auth_otoauth');
    return $count;
}


function block_linksocial_get_users_count()
{
    global $DB;
    $count = $DB->count_records('user', array('auth' =>'otoauth', 'deleted' => '0'));
    return $count;
}

function block_linksocial_get_users_accounts()
{
    global $DB, $USER;
    $accounts = $DB->get_records('auth_otoauth', array('userid' => $USER->id, 'active' => 1));
    return $accounts;
}