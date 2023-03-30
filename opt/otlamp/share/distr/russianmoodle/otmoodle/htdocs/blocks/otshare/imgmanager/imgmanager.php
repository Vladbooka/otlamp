<?php

/**
 * Менеджер изображения
 * 
 * @package    block
 * @subpackage otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once('forms/img_form.php');

// Получение идентификатора экземпляра блока
$blockid = required_param('blockid', PARAM_INT);
// URL для возвращения на страницу с которой пришли
$backurl = required_param('backurl', PARAM_URL);
// ID курса для сохранения в файловую зону
$courseid = required_param('courseid', PARAM_INT);

// Получение экземпляра блока
$instance = $DB->get_record('block_instances', ['id' => $blockid]);
$blockinstance = block_instance('otshare', $instance);

// Текущий URL страницы
$pageurl = new moodle_url(
    '/blocks/otshare/imgmanager/imgmanager.php',
    [
        'blockid' => $blockid,
        'backurl' => $backurl,
        'courseid' => $courseid
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
$PAGE->set_title(get_string('imgmanager_page_title', 'block_otshare'));
$PAGE->requires->css('/blocks/otshare/imgmanager/css/styles.css');
$PAGE->navbar->add(
    get_string('go_back','block_otshare'), 
    new moodle_url($backurl, [
        'sesskey' => sesskey()
    ])
);

$html = '';

// Генерация формы создания привязки
$customdata = new stdClass();
$customdata->courseid = $courseid;
$customdata->backurl = $backurl;

$form = new img_form($pageurl, $customdata);
$form->process();

$html .= $form->render();

// Установка шапки страницы
echo $OUTPUT->header();

echo $html;

// Установка подвала страницы
echo $OUTPUT->footer();
