<?php

/**
 * Блок "Поделиться ссылкой". Посадочная страница
 *
 * @package    block
 * @subpackage block_otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare;

use context_system;
use moodle_url;
use html_writer;
use block_otshare\publication_types\publication_builder as builder;

require_once ('../../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/'));

// Подключение JS и CSS
$PAGE->requires->js(new moodle_url('/blocks/otshare/lp/js/script.js'));

$hash = required_param('hash', PARAM_TEXT);

// Билдим объект с необходимыми данными
$builder = new builder();

// Установим хэш
$builder->set_hash($hash);

// Получим объект публикатора
$publicator = $builder->get_publicator_by_hash();

// Установим дополнительные тэги для шаринга
$publicator->set_meta_properties();

// Дефолтные параметры
$html = '';

if ( isloggedin() )
{
    // Информация о пользователи и оценка
    $html .= $publicator->get_user_info();
    
    // Информация о прохождении курса пользователем
    $html .= $publicator->get_course_info();
    
    // Описание курса
    $html .= $publicator->get_course_description();
} else
{
    $html .= $builder->get_require_registration_info();
}

// Навигация
$html .= $publicator->set_navbar();

// Результирующий вывод
$result_html = html_writer::div($html, 'block_otshare_lp_page');

// Вывод хидера
echo $OUTPUT->header();
// Печать собранного html кода
echo $result_html;

// Вывод футера
echo $OUTPUT->footer();
