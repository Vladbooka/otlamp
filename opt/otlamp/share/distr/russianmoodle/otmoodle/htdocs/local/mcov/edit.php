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
 * Настраиваемые поля. Страница редактирования настраиваемых полей
 *
 * @package    local_mcov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \otcomponent_yaml\Yaml;

require_once('../../config.php');

// Сущность
$entitycode = required_param('entity', PARAM_ALPHAEXT);
// Идентификатор объекта
$objid = required_param('objid', PARAM_INT);
// URL для возврата
$backurl = optional_param('backurl', null, PARAM_RAW);

// Контекст системы
$syscontext = context_system::instance();
$PAGE->set_context($syscontext);

require_login();

// формирование урла текущей страницы
$pageurlparams = ['objid' => $objid, 'entity' => $entitycode];
if (!is_null($backurl))
{
    $pageurlparams['backurl'] = $backurl;
}
$pageurl = new moodle_url('/local/mcov/edit.php', $pageurlparams);

$classname = '\\local_mcov\\entity\\' . $entitycode;
/** @var local_mcov\entity $entity */
$entity = new $classname($entitycode, $objid, $pageurl);

// строка Редактирование настраиваемых полей
$edittitle = $entity->get_edit_entity_title(true);

$PAGE->set_pagelayout('standard');
$PAGE->set_url($pageurl);
$PAGE->set_title($edittitle);

// формирования урла для возврата к экземпляру сущности
$entityurl = null;
// формирование строки для возврата к экземпляру сущности
$entitytitle = $entity->get_entity_title();
if (!is_null($backurl))
{
    $entityurl = new moodle_url(urldecode($backurl));
    $entitytitle = get_string('back_to_entity', 'local_mcov', $entitytitle);
}
// добавление ссылки на возврат
$PAGE->navbar->add($entitytitle, $entityurl);
// добавление в крошки тайтла текущей страницы
$PAGE->navbar->add($edittitle, $pageurl);
$PAGE->set_heading($edittitle);//. (is_null($entityname) ? '.' : ': "' . $entityname . '"')



$html = '';

if (!$entity->has_editable_fields()) {
    core\notification::add(get_string('error_mcov_has_no_fields_to_edit', 'local_mcov'), core\notification::INFO);
} else {
    try {
        // обработка формы
        if ($entity->process_form())
        {
            // форма была отправлена и обработана - редиректим, чтобы избавиться от случайной повторной отправки
            // и убедиться, что в форму прописаны именно сохраненные значения, а не отправленные формой
            redirect($pageurl);
        }
        // установка значений из базы
        $entity->set_form_data();
        // рендерим форму
        $html .= $entity->render_form();
    } catch (\local_mcov\entity_exception $ex)
    {
        $a = new stdClass();
        $a->errorcode = $ex->getCode();
        $a->errormessage = $ex->getMessage();
        $a->trace = $ex->getTraceAsString();
        core\notification::add(get_string('error_mcov_form', 'local_mcov', $a), core\notification::ERROR);
    }
}


echo $OUTPUT->header();

// Отобразим форму
echo $html;

echo $OUTPUT->footer();
