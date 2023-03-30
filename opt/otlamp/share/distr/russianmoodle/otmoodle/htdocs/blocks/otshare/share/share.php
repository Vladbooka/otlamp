<?php

/**
 * Блок "Поделиться ссылкой". Страница обработки действия "Поделиться"
 * 
 * @package    block
 * @subpackage block_otshare
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otshare;

use context_system;
use moodle_url;
use block_otshare\publication_types\publication_builder as builder;

require_once ('../../../config.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/'));

$sn = optional_param('sn', 'fb', PARAM_TEXT);
$pl = optional_param('pl', 'result_course', PARAM_TEXT);

// Билдим объект с необходимыми данными
$builder = new builder();
$builder->set_sn($sn);
$builder->set_pl($pl);

// Получим объект публикатора
$publicator = $builder->get_publicator();

// Заполним параметры
$publicator->set_params();

// Сохраним слепок данных
$publicator->save_data();

// Публикуем в соц сеть
$publicator->share();

echo $OUTPUT->header();
echo $OUTPUT->footer();
