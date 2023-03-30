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
 * Витрина курсов. Главная страница Витрины
 *
 * @package    local
 * @subpackage crw
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!file_exists('../../config.php'))
{// Не нашли конфиги - переходим к установке
    header('Location: install.php');
    die;
}
// Подключаем библиотеки
require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

// Редирект, если требуется обновление системы
redirect_if_major_upgrade_required();

// Для категорий есть отдельная страница - перенаправим в неё
if (optional_param('cid', 0, PARAM_INT))
{
    $currenturl = new moodle_url($FULLME);
    redirect(new moodle_url('/local/crw/category.php', $currenturl->params()));
}

// Формируем GET параметры
$urlparams = array();
if ( ! empty($CFG->defaulthomepage) &&
     ($CFG->defaulthomepage == HOMEPAGE_MY) &&
     optional_param('redirect', 1, PARAM_BOOL) === 0
   )
{
    $urlparams['redirect'] = 0;
}

// Базовые свойства страницы
$PAGE->set_url('/local/crw/index.php', $urlparams);
$PAGE->set_pagelayout('standard');
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Кеширование
$PAGE->set_cacheable(false);

require_course_login($SITE);

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

course_view(context_course::instance(SITEID));

/// If the hub plugin is installed then we let it take over the homepage here
if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
    require_once($CFG->dirroot.'/local/hub/lib.php');
    $hub = new local_hub();
    $continue = $hub->display_homepage();
    //display_homepage() return true if the hub home page is not displayed
    //mostly when search form is not displayed for not logged users
    if (empty($continue)) {
        exit;
    }
}

// Добавим свойства страницы
//$PAGE->set_pagetype('site-index');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

$customfrontpageinclude = $CFG->customfrontpageinclude ?? '';
$isfrontpageinclude = stristr($customfrontpageinclude, 'local/crw/homepageinclude.php') !== FALSE;

// Настройка переопределения навигации
$configoverride = get_config('local_crw', 'override_navigation');
if ((!$isfrontpageinclude || $CFG->defaulthomepage != HOMEPAGE_SITE) && !empty($configoverride))
{// Витрина на главной не включена, добавим в навигацию ссылку на плагин
    $PAGE->navbar->add(get_string('courses_showcase', 'local_crw'), new moodle_url('/local/crw/index.php'));
}

// Шапка
echo $OUTPUT->header();

// СТРАНИЦА

// Print Section or custom info.
$modinfo = get_fast_modinfo($SITE);
$modnamesused = $modinfo->get_used_module_names();

// Include course AJAX.
include_course_ajax($SITE, $modnamesused);


// Подключаем витрину
require_once($CFG->dirroot .'/local/crw/lib.php');
// Получаем плагин витрины
$showcase = new local_crw();
$displayopts = ['return_html' => true];
// Отобразить витрину курсов
$html = $showcase->display_showcase($displayopts);


echo $html;
// Подвал
echo $OUTPUT->footer();

