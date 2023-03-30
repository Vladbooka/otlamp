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

$capabilities = [
    // Право оценивать других слушателей
    'mod/otmutualassessment:gradeothers' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student'        => CAP_ALLOW
        ]
    ],
    // Право быть оценным другими слушателями
    'mod/otmutualassessment:begradedbyothers' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student'        => CAP_ALLOW
        ]
    ],
    
    // Право просматривать оценки
    'mod/otmutualassessment:viewgrades' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ]
    ],
    
    // Право пересчитывать оценки
    'mod/otmutualassessment:refreshgrades' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ]
    ],
    
    // Право изменять глобальные настройки модуля
    'mod/otmutualassessment:managesettings' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
        ]
    ],
    
    // Право удалять результаты голосования участников
    'mod/otmutualassessment:deletevotes' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy'       => [
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ]
    ],
    
    // Право на добавление инстанса модуля курса
   'mod/otmutualassessment:addinstance' => [
       'riskbitmask' => RISK_XSS,
       'captype' => 'write',
       'contextlevel' => CONTEXT_COURSE,
       'archetypes' => [
           'editingteacher' => CAP_ALLOW,
           'manager' => CAP_ALLOW
       ],
       'clonepermissionsfrom' => 'moodle/course:manageactivities'
   ]
];
