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
 * File downloader
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// disable moodle specific debug messages and any errors in output
define('NO_DEBUG_DISPLAY', true);

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);
$resource = required_param('resource', PARAM_RAW);

// единственное предназначение блока - отображать контент из внешнего источника
// если нет права видеть блок - значит и нет права смотреть контент из внешнего источника
require_capability('moodle/block:view', context_block::instance($id));

/** @var block_otexternaldata $blockinstance */
// получение текущих настроек блока
$blockinstancerecord = $DB->get_record('block_instances', ['id' => $id]);
$blockinstance = block_instance('otexternaldata', $blockinstancerecord);




$contenttypename = $blockinstance->config->content_type;
$contenttype = \block_otexternaldata\content_type::get_content_type_instance($contenttypename, $blockinstance->instance);
if (!empty($blockinstance->config->content_type_configs[$contenttypename]))
{
    $contenttypeconfig = $blockinstance->config->content_type_configs[$contenttypename];
    
    $resourcedata = $contenttype->get_item_file($contenttypeconfig, $resource);
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if (array_key_exists('headers', $resourcedata))
    {
        foreach($resourcedata['headers'] as $headername => $headervalue)
        {
            header($headername.': '.implode('; ',$headervalue));
        }
    }
    
    echo $resourcedata['body'];
    
    exit;
}