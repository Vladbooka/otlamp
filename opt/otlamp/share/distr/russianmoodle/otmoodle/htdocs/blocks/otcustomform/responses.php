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
 * Настраиваемые формы
 *
 * @package    block_otcustomform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use block_otcustomform\utils;

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);

require_login();

$context = context_block::instance($id);
$instance = $DB->get_record('block_instances', ['id' => $id]);
$pinstance = block_instance('otcustomform', $instance);
require_capability('block/otcustomform:viewresponses', $context);

if ( empty($pinstance->config->customformid) || ! utils::is_form_exists($pinstance->config->customformid) )
{
    throw new moodle_exception('invalid_formid', 'block_otcustomform');
}

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/otcustomform/responses.php', ['id' => $id]));
$PAGE->set_title(get_string('view_responses', 'block_otcustomform'));

// получение всех ответов на форму
$users = utils::get_users(utils::get_parent_form_id($pinstance->config->customformid));

// получение таблицы пользователей
$table = utils::get_users_table($id, $users);

echo $OUTPUT->header();

echo html_writer::tag('h1', get_string('header_users_responses', 'block_otcustomform'));
echo html_writer::table($table);

echo $OUTPUT->footer();