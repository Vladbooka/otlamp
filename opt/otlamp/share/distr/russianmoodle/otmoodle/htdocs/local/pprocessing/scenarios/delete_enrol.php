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

$scenarios['delete_enrol'] = [
    'status' => true,
    'events' => ['\local_pprocessing\event\iteration_initialized'],
    'processors' => [
        [// останавливаем сценарий, если в enrol ничего нет - должен быть enrol-инстанс
            'type' => 'handler',
            'code' => 'stop_scenario_execution',
            'preconditions' => [
                [
                    'code' => 'is_empty',
                    'params' => [
                        'value' => '$VAR.enrol'
                    ]
                ]
            ]
        ],
        [// удаляем enrol
            'type' => 'handler',
            'code' => 'delete_enrol',
            'params' => [
                'enrol_instance' => [
                    'source_type' => 'container',
                    'source_value' => 'enrol'
                ]
            ]
        ],
    ]
];