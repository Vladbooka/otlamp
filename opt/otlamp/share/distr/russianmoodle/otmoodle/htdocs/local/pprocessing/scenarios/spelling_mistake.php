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

// сценарий отправки уведомления об орфографической ошибке
$scenarios['spelling_mistake'] = [
    // в отдельной таблице будем хранить связь отлавливаемых событий со сценариями
    'events' => ['\theme_opentechnology\event\spelling_mistake'],
    'processors' => [
        [
            'type' => 'handler',
            'code' => 'get_config_users'
        ],
        [
            'type'   => 'iterator',
            'code'   => 'event_based',
            'config' => [
                'scenario' => 'send_spelling_mistake_message',
                'trigger_event' => false,
                'iterate_item_var_name' => 'user'
            ]
        ]
    ]
];