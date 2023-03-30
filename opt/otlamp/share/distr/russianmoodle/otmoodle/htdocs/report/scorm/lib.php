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
 * Отчет по результатам SCORM. Библиотека плагина.
 *
 * @package    report
 * @subpackage scorm
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Расширение навигации модуля курса
 *
 * @param navigation_node $navigation - Объект навигации системы
 * @param cm_info $cm - Модуль курса
 * 
 * @return void
 */
function report_scorm_extend_navigation_module($navigation, $cm) 
{
    // Ссылка на страницу редактирования настроек отчета для модуля
    if ( $cm->modname == 'scorm' && has_capability('report/scorm:editmodsettings', $cm->context) ) 
    {// Право редактирования настроек отчета в модуле scorm получено
        // Добавление ссылки на страницу настроек отчета для модуля
        $url = new moodle_url('/report/scorm/cmsettings.php', ['cmid' => $cm->id]);
        $navigation->add(
            get_string('cmsettings_link', 'report_scorm'), 
            $url, 
            navigation_node::TYPE_SETTING,
            null, 
            null, 
            new pix_icon('i/settings', '')
        );
    }
    
    // Ссылка на отчет по модулю
    if ( $cm->modname == 'scorm' && has_capability('report/scorm:viewstatistic', $cm->context) )
    {// Право просмотра отчета по модулю SCORM найдено
        // Добавление ссылки на отчет
        $url = new moodle_url('/report/scorm/index.php', ['cmid' => $cm->id]);
        $navigation->add(
            get_string('report_cm_link', 'report_scorm'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            null,
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Получить типы страниц отчета для установки блоков
 * 
 * Отображение блоков ограничивается типом страницы
 * 
 * @param string $pagetype - Тип текущей страницы
 * @param stdClass $parentcontext - Родительский контекст блока
 * @param stdClass $currentcontext - Текущий контекст блока
 * 
 * @return array - Список типов страниц отчета относительно текущего типа
 */
function report_scorm_page_type_list($pagetype, $parentcontext, $currentcontext) 
{
    
    // Типы страниц одинаковы для всех интерфейсов отчета
    $pagetypes = [
        '*'                           => get_string('page-x', 'pagetype'),
        'report-*'                    => get_string('page-report-x', 'pagetype'),
        'report-scorm-*'              => get_string('page-report-completion-x', 'report_scorm'),
        'report-scorm-index'          => get_string('page-report-completion-index', 'report_scorm'),
        'report-scorm-cmeditsettings' => get_string('page-report-completion-user', 'report_scorm')
    ];
    return $pagetypes;
}