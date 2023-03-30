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
 * Слайдер изображений. Менеджер слайдов.
 * 
 * @package    block
 * @subpackage otslider
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// Получение идентификатора экземпляра блока
$blockid = required_param('blockid', PARAM_INT);
// URL для возвращения на страницу с которой пришли
$backurl = required_param('backurl', PARAM_URL);

// Получение экземпляра блока
$instance = $DB->get_record('block_instances', ['id' => $blockid]);
$blockinstance = block_instance('otslider', $instance);

// Текущий URL страницы
$pageurl = new moodle_url(
    '/blocks/otslider/slidemanager.php',
    [
        'blockid' => $blockid,
        'backurl' => $backurl
    ]
);

// Получение текущего контекста
$context = context_block::instance($blockid);
// Требуется авторизация в системе
require_login();
// Требуется право доступа
require_capability('moodle/block:edit', $context);

// Установка общих параметров страницы
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('slidemanager_page_title', 'block_otslider'));
$PAGE->requires->css('/blocks/otslider/slidemanager.css');
$PAGE->navbar->add(
    get_string('go_back','block_otslider'), 
    new moodle_url($backurl, [
        'sesskey' => sesskey()
    ])
);

$html = '';

// Инициализация слайдера
$slider = new block_otslider\slider($blockinstance);

// Генерация формы создания привязки
$customdata = new stdClass();
$customdata->block = $blockinstance;
$customdata->slider = $slider;
$customdata->backurl = $backurl;
$form = new block_otslider\slides\formsave($pageurl, $customdata, 'post', '', ['class' => 'otslider-manager-form']);
$formmessage = $form->process();
if ( $formmessage )
{
    $html .= $OUTPUT->notification($formmessage, 'error');
}

$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();