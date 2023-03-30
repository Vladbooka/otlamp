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
 * Вебсервис доп.полей
 *
 * @package    im
 * @subpackage customfields
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../lib.php');
require_once($CFG->libdir . '/weblib.php');
//require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));

class dof_external_api_plugin extends dof_external_api_plugin_base
{
    public static function sort_customfields($sorteditems)
    {
        GLOBAL $DOF;
        
        $result = true;
        
        foreach($sorteditems as $customfieldsort=>$customfieldid)
        {
            $customfield = $DOF->modlib('formbuilder')->init_customfield_by_item($customfieldid);
            $customfield->set_sortorder((int)$customfieldsort);
            $result = $result && $customfield->save();
        }
        sleep(2);
        
        return $result;
    }
    
}
