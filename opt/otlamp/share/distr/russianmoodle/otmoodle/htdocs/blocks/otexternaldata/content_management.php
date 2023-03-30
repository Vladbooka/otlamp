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
 * Manage providers
 *
 * @package    auth
 * @subpackage otoauth
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

require('../../config.php');

$cid = required_param('cid', PARAM_INT);
// $id = optional_param('id', null, PARAM_INT);
// $action = optional_param('action', null, PARAM_ALPHA);

// $syscontext = context_system::instance();
$blockcontext = context::instance_by_id($cid);

$baseurl = new moodle_url('/blocks/otexternaldata/content_management.php', ['cid' => $cid]);
$PAGE->set_url($baseurl);
$PAGE->set_context($blockcontext);
$PAGE->set_pagelayout('admin');


// Получение всех родителей
$parents = $blockcontext->get_parent_contexts();
// От старшего к младшему
$parents = array_reverse($parents);
// Системный контекст - не нужен, он и так будет в навбаре
array_shift($parents);
// Добавление в навбар родительских итемов
foreach ($parents as $parentcontext)
{
    $PAGE->navbar->add($parentcontext->get_context_name(), $parentcontext->get_url());
}
// Добавление текущего контекста (блок)
$PAGE->navbar->add($blockcontext->get_context_name());
// Добавление текущей страницы
$PAGE->navbar->add(get_string('content_management', 'block_otexternaldata'), $baseurl);


require_capability('block/otexternaldata:managecontent', $blockcontext);





$html = '';

$blockinstanceid = $blockcontext->instanceid;
$blockinstancerecord = $DB->get_record('block_instances', ['id' => $blockinstanceid]);
$blockinstance = block_instance('otexternaldata', $blockinstancerecord);

$contenttype = $blockinstance->config->content_type ?? null;
$contenttypeconfigs = $blockinstance->config->content_type_configs ?? [];
$defaultvalues = array_merge_recursive(($contenttypeconfigs[$contenttype] ?? []), ['content_type' => $contenttype]);
$customdata = [
    'blockinstanceid' => $blockinstanceid,
    'content_type' => $contenttype,
    'baseurl' => $baseurl
];

$form = new \block_otexternaldata\content_management_form($baseurl, $customdata);
$form->set_data($defaultvalues);
$form->process();
$html .= $form->render();


echo $OUTPUT->header();
echo $html;
echo $OUTPUT->footer();

