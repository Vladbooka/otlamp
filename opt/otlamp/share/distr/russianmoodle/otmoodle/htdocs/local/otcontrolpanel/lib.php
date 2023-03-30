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
 * Панель управления СЭО 3KL
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use local_otcontrolpanel\actionform;
use local_otcontrolpanel\mcov_config;

function local_otcontrolpanel_output_fragment_actionform($args)
{
    global $PAGE;

    $PAGE->set_context(context_system::instance());

    // параметры закинутые с js'а
    $jsonformdata = $args['jsonformdata'];
    $viewcode = $args['viewcode'];
    $ids = json_decode($args['ids']);

    // параметры для создания формы
    $action=null;
    $customdata = ['viewcode' => $viewcode, 'ids' => $ids];
    $method='post';
    $target='';
    $attributes=null;
    $editable=true;
    $ajaxformdata = [];
    parse_str(json_decode($jsonformdata), $ajaxformdata);

    $actionform = new actionform($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    $actionform->process_form_data();

    return json_encode([
        'html' => $actionform->render(),
        'header' => $actionform->get_header()
    ]);
}

/**
 * Регистрация в local_mcov служебных полей
 * @return \local_mcov\hcfield[]
 */
function local_otcontrolpanel_get_hardcoded_mcov_fields() {
    // регистрируем под себя поле привязанное к пользователю, чтобы хранить пользовательский конфиг панели
    // в user_preferences не хватает длины чтобы хранить такой конфиг
    return [
        new mcov_config()
    ];
}

