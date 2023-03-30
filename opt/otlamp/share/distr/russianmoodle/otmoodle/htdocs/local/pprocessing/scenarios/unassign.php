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

defined('MOODLE_INTERNAL') || die();

$scenarios['unassign'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
    // снять назначенную роль
        [
            'type' => 'handler',
            'code' => 'role_unassign',
            'params' => [
                'roleid' => [
                    'source_type' => 'container',
                    'source_value' => 'role_assignment.roleid'
                ],
                'userid' => [
                    'source_type' => 'container',
                    'source_value' => 'role_assignment.userid'
                ],
                'contextid' => [
                    'source_type' => 'container',
                    'source_value' => 'role_assignment.contextid'
                ],
                'component' => [
                    'source_type' => 'container',
                    'source_value' => 'role_assignment.component'
                ],
                'itemid' => [
                    'source_type' => 'container',
                    'source_value' => 'role_assignment.itemid'
                ]
            ]
        ] 
    ]
];
