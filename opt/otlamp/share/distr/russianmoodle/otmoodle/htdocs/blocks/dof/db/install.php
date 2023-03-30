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
 * Free Dean's Office installation.
 *
 * @package    block
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/dof/locallib.php');
require_once($CFG->dirroot . '/blocks/dof/classes/otserial.php');

function xmldb_block_dof_install()
{
    global $DB, $OUTPUT, $CFG;
    
    $otapi = new block_dof\otserial();
    $result = $otapi->issue_serial_and_get_data();
    if (isset($result['response']) && !empty($result['message']))
    {
        echo $OUTPUT->notification($result['message'], \core\output\notification::NOTIFY_SUCCESS);
        
    } else if(!isset($result['response']))
    {
        echo $OUTPUT->notification($result['message']??'Unknown error', \core\output\notification::NOTIFY_ERROR);
    }

    
    // Получение экземпляров блока Деканата
    $instances = $DB->get_records('block_instances', ['blockname' => 'dof']);
    if ( empty($instances) )
    {// Экземпляры не найдены
        // Автоматическое добавление первого экземпляра на старницу уведомлений
        $page = new moodle_page();
        $page->set_url(new moodle_url('/admin/index.php'));
        $page->set_context(context_system::instance());
        
        // Добавление экземпляра блока
        $blockmanager = new block_manager($page);
        $blockmanager->add_region('side-post', true);
        $blockmanager->add_block('dof', 'side-post', 0, false);
    }
    

    // Инициализация $DOF
    $dof = new dof_control($CFG);
    // Добавление ID экземпляра блока к Контроллеру
    $instances = $DB->get_records('block_instances', ['blockname' => 'dof']);
    if ( empty($instances) )
    {// Экземпляр не определен
        $dof->instance = NULL;
    } else
    {
        $instance = array_shift($instances);
        $dof->instance = $instance;
    }
    
    // Установка плагинов деканата
    if( defined('CLI_SCRIPT') && CLI_SCRIPT )
    {
        $eol = "\n";
    } else
    {
        $eol = "<br />";
    }
    $dof->mtrace(1, get_string('plugin_installation', 'block_dof'), $eol);
    $result = $dof->plugin_setup();
    if($result)
    {
        $dof->mtrace(1, get_string('plugin_installation_success', 'block_dof'), $eol);
    } else
    {
        $dof->mtrace(1, get_string('plugin_installation_error', 'block_dof'), $eol);
    }
    
    return true;
}

?>