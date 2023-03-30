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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Тема СЭО 3KL. Конфигурация темы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Базовая конфигурация
if ( ! isset($THEME) )
{
    $THEME = new stdClass();
}
$THEME->name = 'opentechnology';
$THEME->doctype = 'html5';
$THEME->parents = ['classic','boost'];
$THEME->sheets = ['custom'];
$THEME->supportscssoptimisation = false;
$THEME->yuicssmodules = [];
$THEME->enable_dock = false;
$THEME->editor_sheets = [];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_opentechnology_process_css';
$THEME->rarrow = '&#9658;';
$THEME->larrow = '&#9668;';
$THEME->javascripts_footer = [
    'dockmod',
    'customize',
    'langswitcher'
];
$THEME->iconsystem = '\\theme_opentechnology\\output\\icon_system_fontawesome';
$THEME->prescsscallback = 'theme_opentechnology_get_pre_scss';
$THEME->scss = function ($theme){
    return theme_opentechnology_get_main_scss_content($theme);
};
$THEME->extrascsscallback = 'theme_opentechnology_get_extra_scss';
$THEME->precompiledcsscallback = 'theme_opentechnology_get_precompiled_css';


// Конфигурация страниц
$THEME->layouts = [
    // Базовая страница без блоков
    'base' => [
        'file' => 'columns1.php',
        'regions' => []
    ],
    // Базовая страница с блоками
    'standard' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страница курсов
    'course' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'options' => [
            'langmenu' => true
        ],
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страница описания курсов
    'coursedesc' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'options' => [
            'langmenu' => true
        ],
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страница категории курсов
    'coursecategory' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Модуль курса
    'incourse' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Главная страница
    'frontpage' => [
        'file' => 'frontpage.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'options' => [
        ],
        'defaultregiondocking' => [
            'side-pre' => 'dock',
            'side-post' => 'dock',
        ]
    ],
    // Страница администрирования
    'admin' => [
        'file' => 'admin.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страница MY
    'mydashboard' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-content-top',
            'side-content-bot',
            'content-heading',
            'content-footing',
            'side-pre',
            'side-post',
        ],
        'defaultregion' => 'side-content-top',
        'options' => [
            'langmenu' => true
        ],
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Моя публичная страница
    'mypublic' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страница входа в систему
    'login' => [
        'file' => 'login.php',
        'regions' => [],
        'options' => [
            'langmenu' => true
        ]
    ],
    // Страница, которая повляется во всплывающем окне
    'popup' => [
        'file' => 'popup.php',
        'regions' => [],
        'options' => [
            'nofooter' => true,
            'nonavbar' => true
        ]
    ],
    // Фрейм
    'frametop' => [
        'file' => 'columns1.php',
        'regions' => [],
        'options' => [
            'nofooter' => true,
            'nocoursefooter' => true
        ]
    ],
    // Страницы объектов
    'embedded' => [
        'file' => 'embedded.php',
        'regions' => [
        ],
    ],
    // Страницы уведомлений
    'maintenance' => [
        'file' => 'maintenance.php',
        'regions' => [
        ],
    ],
    // Страницы для печати
    'print' => [
        'file' => 'columns3.php',
        'regions' => [],
        'options' => [
            'nofooter' => true,
            'nonavbar' => false
        ]
    ],
    // Страница редиректа
    'redirect' => [
        'file' => 'embedded.php',
        'regions' => [
        ]
    ],
    // Страницы отчетов
    'report' => [
        'file' => 'columns3.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ],
    // Страницы защищенного просмотра
    'secure' => [
        'file' => 'secure.php',
        'regions' => [
            'side-post',
            'side-pre',
            'content-heading',
            'content-footing',
            'side-content-top',
            'side-content-bot',
        ],
        'defaultregion' => 'side-post',
        'defaultregiondocking' => [
            'side-pre' => 'dock',
        ]
    ]
];
