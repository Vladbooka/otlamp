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
 * Блок привязки аккаунтов соцсетей. Статистика привязок.
 * 
 * @package    block
 * @subpackage linksocial
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $OUTPUT, $PAGE, $CFG, $COURSE;

// Подключение библиотек
require_once($CFG->dirroot . '/blocks/linksocial/lib.php');

// Требуется авторизация
require_login();

// Получение GET параметров
$activetab = optional_param('tab', 'count', PARAM_TEXT);
$page     = optional_param('page', 0, PARAM_INT);
$limitnum = optional_param('limitnum', 50, PARAM_INT);

// Установка параметров страницы
$PAGE->set_context(context::instance_by_id($COURSE->id));
$PAGE->set_url('/blocks/linksocial/statistics.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('page_statistics', 'block_linksocial'));
$PAGE->set_title(get_string('page_statistics', 'block_linksocial'));

// Установка хлебных крошек
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('page_statistics', 'block_linksocial'), new moodle_url('/blocks/linksocial/statistics.php'));

// Формирование вкладок
$tabs = array();
$tabs[] = new tabobject('count', new moodle_url('/blocks/linksocial/statistics.php', array('tab' => 'count')),
        get_string('page_statistics_tab_count', 'block_linksocial'), '', false);
$tabs[] = new tabobject('bytype', new moodle_url('/blocks/linksocial/statistics.php', array('tab' => 'bytype')),  
        get_string('page_statistics_tab_bytype', 'block_linksocial'), '', false);
$tabs[] = new tabobject('byuser', new moodle_url('/blocks/linksocial/statistics.php', array('tab' => 'byuser')), 
        get_string('page_statistics_tab_byuser', 'block_linksocial'), '', false);
 
// Шапка страницы
echo $OUTPUT->header();

// Отображение вкладок
echo $OUTPUT->tabtree($tabs, $activetab);

// Отображение таблицы
switch($activetab) 
{
    case 'count' :
        // Отображение статистики по типу аккаунта
        $count = 0;
        print (block_linksocial_statitics_counter());
        break;
    case 'bytype' : 
        // Отображение статистики по типу аккаунта
        $count = block_linksocial_get_accounts_count();
        print (block_linksocial_statitics_bytype($page, $limitnum));
        break;
    case 'byuser' :
        // Отображение статистики по пользователям
        $count = block_linksocial_get_users_count();
        print (block_linksocial_statitics_byuser($page, $limitnum));
        break;
}
// Пагинация
$paginationurl = $PAGE->url;
$paginationurl->params(['tab' => $activetab, 'limitnum' => $limitnum]);
echo $OUTPUT->paging_bar($count, $page, $limitnum, $paginationurl);


// Подвал страницы
echo $OUTPUT->footer();