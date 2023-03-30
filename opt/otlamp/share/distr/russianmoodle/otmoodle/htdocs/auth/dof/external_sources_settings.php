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
 * Отображает настройки регистрации по предварительным спискам.
 */

require_once('../../config.php');
require_once("$CFG->libdir/adminlib.php");

// Установка параметров страницы
$data['baseurl'] = new moodle_url('/auth/dof/external_sources_settings.php');
$PAGE->set_url($data['baseurl']);

admin_externalpage_setup('external_sources_settings');

$PAGE->set_context(null);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('external_sources_settings', 'auth_dof'));
$PAGE->set_heading(get_string('external_sources_settings', 'auth_dof'));

$data['select_source'] = optional_param('select_source', '', PARAM_TEXT);
$data['get_src_fields_btn'] = 
    optional_param('get_src_fields_btn', false, PARAM_TEXT) === false ? false : true;
$data['add_source_btn'] = 
    optional_param('add_source_btn', false, PARAM_TEXT) === false ? false : true;

    $form = new auth_dof\settings_forms\external_sources_settings($data['baseurl'], 
        $data, 'post', '', ['class' => 'external_sources_settings']);
$form->process();


echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();