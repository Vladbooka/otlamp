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
 * Блок таблиы курсов c AJAX - отображением описания. Страница AJAX.
 * 
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../../../config.php');
require_once('renderer.php');

global $OUTPUT, $PAGE;

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/crw/ajax.php');

$id = optional_param('id', 0, PARAM_INT);

// Формируем параметры для получения курса
$params = array('id' => $id);

// Получаем курс из БД
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

// Получаем рендер локального плагина
$renderer = $PAGE->get_renderer('crw_courses_list_ajax');

echo $renderer->cajax_courseblock($course);