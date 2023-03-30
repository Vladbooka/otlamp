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
 * Универсальная панель управления. Главная страница
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!file_exists('../../config.php'))
{// Не нашли конфиги - переходим к установке
    header('Location: install.php');
    die;
}
// Подключаем библиотеки
require_once('../../config.php');
require_once("$CFG->libdir/adminlib.php");

admin_externalpage_setup('otcontrolpanel');

// параметры
// выбранная вкладка
$v = optional_param('v', null, PARAM_ALPHANUMEXT);

$baseurl = new moodle_url('/local/otcontrolpanel/index.php');

/** @var moodle_url $currenturl */
$currenturl = fullclone($baseurl);
if (!is_null($v))
{
    $currenturl->param('v', $v);
}

// Базовые свойства страницы
$PAGE->set_url($currenturl);
$contextsystem = context_system::instance();

$views = \local_otcontrolpanel\view::get_configured_views($currenturl);
$currentview = null;
$tabs = [];
foreach($views as $view)
{
    if ((string)$view->code === (string)$v)
    {
        $currentview = $view;
        $PAGE->navbar->add($view->displayname, $currenturl);
    }

    $taburl = fullclone($baseurl);
    $taburl->param('v', $view->code);
    $tabs[] = new \tabobject($view->code, $taburl, $view->displayname, $view->displayname);
}


$PAGE->requires->css('/local/opentechnology/js/bootstrap-table/dist/bootstrap-table.min.css');

$html = '';

// конфигуратор требуется переделать, поэтому пока скрываю функционал
if (has_capability('local/otcontrolpanel:config', $contextsystem) ||
    has_capability('local/otcontrolpanel:config_my', $contextsystem))
{
    $configurl = new moodle_url('/local/otcontrolpanel/manage_config.php');
    $configtext = get_string('config', 'local_otcontrolpanel');
    $icon = html_writer::tag('i', '', ['class' => 'fa fa-cogs']);
    $link = html_writer::link($configurl, $icon , [
        'title' => $configtext,
        'target' => '_blank',
        'class' => 'btn btn-secondary'
    ]);
    $html .= html_writer::div($link, 'pull-right float-right');
}

$html .= print_tabs([$tabs], $v, null, null, true);

if (is_null($currentview))
{
    // не указано какое представление отобразить - отобразим вводную информацию
    $html .= html_writer::div(get_string('starttext', 'local_otcontrolpanel'), 'p-4');
}

if (!is_null($currentview))
{
    $filterform = $currentview->get_filter_form();
    if (!is_null($filterform))
    {
        $headertext = get_string('filterform_header', 'local_otcontrolpanel');
        $cardheader = html_writer::div($headertext, 'card-header');

        $cardbodycontent = '';
        if ($filterform->get_data())
        {
            $linkcancel = html_writer::link($currenturl, get_string('filterform_cancel', 'local_otcontrolpanel'));
            $cardbodycontent .= html_writer::div(get_string('filterform_applied', 'local_otcontrolpanel', $linkcancel), 'py-1');
        }
        $cardbodycontent .= $filterform->render();
        $cardbody = html_writer::div($cardbodycontent, 'card-body');

        $formhtml = html_writer::div($cardheader . $cardbody, 'card');
        $formhtml = html_writer::div($formhtml, 'col-md-6');
        $formhtml = html_writer::div($formhtml, 'row');
        $html .= $formhtml;
    }
    $bstable = $currentview->entity->auto_render($OUTPUT, null, [
        'template_table' => 'local_otcontrolpanel/bootstrap-table',
        'template_list' => 'local_otcontrolpanel/bootstrap-table'
    ]);
    $html .= html_writer::div($bstable, 'view', [
        'data-view-code' => $currentview->code,
        'data-entity-code' => $currentview->entity->get_code()
    ]);
}

$html = html_writer::div(
    $html,
    'otcontrolpanel'
);



// Шапка
echo $OUTPUT->header();

echo $html;
// Подвал
echo $OUTPUT->footer();

