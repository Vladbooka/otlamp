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

namespace local_pprocessing\processor\condition;

use context_course;
use local_pprocessing\container;

defined('MOODLE_INTERNAL') || die();

/**
 * Условие - является ли пользователь студентом
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class is_student extends base
{
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\condition\base::execute()
     */
    protected function execution_process(container $container)
    {
        //TODO: брать входные данные через params
        $userid = $container->read('userid');
        $courseid = $container->read('courseid');
        
        if ( empty($userid) || empty($courseid) )
        {
            $this->debugging('missing required parameters', ['userid' => $userid, 'courseid' => $courseid]);
            // без пользователя и курса нечего проверять
            return false;
        }
        
        $targetroleid = null;
        // если передан идентификатор роли, то будет проверять конкретную роль
        $objecttable = $container->read('objecttable');
        $objectid = $container->read('objectid');
        if ( ($objecttable == 'role') && ! empty($objectid) )
        {
            $targetroleid = $objectid;
        }
        
        // студентом является оцениваемая роль
        // получим конфиг
        $graderoles = get_config('core', 'gradebookroles');
        if ( empty($graderoles) )
        {
            $this->debugging('missing gradebookroles', ['gradebookroles' => $graderoles]);
            // в системе отсутствуют оцениваемые роли
            return false;
        }
        
        // идентификаторы ролей через запятую
        $rolesids = explode(',', $graderoles);
        
        if ( ! empty($targetroleid) )
        {
            if ( in_array($targetroleid, $rolesids) )
            {
                return true;
            } else
            {
                $this->debugging('targetrole is no gradebookrole', ['targetroleid'=> $targetroleid, 'gradebookroles'=>$rolesids]);
            }
        } else
        {
            // получение контекста курса
            $context = context_course::instance($courseid);
            
            foreach ( $rolesids as $roleid )
            {
                if ( user_has_role_assignment($userid, $roleid, $context->id) )
                {
                    // у пользователя есть оцениваемая роль в курсе
                    return true;
                } else
                {
                    $this->debugging('user has no gradebookrole assignment in course', ['useris'=> $userid, 'gradebookrole'=>$roleid, 'courseid' => $courseid]);
                }
            }
        }
        
        return false;
    }
}

