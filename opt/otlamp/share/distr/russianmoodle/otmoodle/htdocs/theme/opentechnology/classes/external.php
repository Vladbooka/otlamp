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
 * Тема СЭО 3KL. Веб-сервисы.
 *
 * @package    theme
 * @subpackage opentechnology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_opentechnology;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use block_contents;
use context;
use context_system;
use external_api;
use external_function_parameters;
use external_value;
use theme_opentechnology\event\spelling_mistake;

class external extends external_api
{
    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function set_collapsiblesection_state_parameters()
    {
        $collapsiblesection = new external_value(
            PARAM_TEXT,
            'Code of collapsible section',
            VALUE_REQUIRED
        );
        $state = new external_value(
            PARAM_INT,
            'State of collapsible section',
            VALUE_REQUIRED
        );
        $layout = new external_value(
            PARAM_TEXT,
            'Page layout of collapsible section',
            VALUE_REQUIRED
        );
        $params = [
            'collapsiblesection' => $collapsiblesection,
            'state' => $state,
            'layout' => $layout
        ];

        return new external_function_parameters($params);
    }

    /**
     * Исходящий параметр
     *
     * @return external_value
     */
    public static function set_collapsiblesection_state_returns()
    {
        return new external_value(PARAM_BOOL, 'Result of state setting');
    }

    /**
     * AJAX-обработчик состояния сворачиваемого блока
     *
     * @return bool
     */
    public static function set_collapsiblesection_state($collapsiblesection, $state, $layout)
    {
        global $USER;

        $params = self::validate_parameters(self::set_collapsiblesection_state_parameters(), [
            'collapsiblesection' => $collapsiblesection,
            'state' => $state,
            'layout' => $layout
        ]);

        if ( ! empty($USER->id) )
        {
            set_user_preference('theme_opentechnology_collapsiblesection_'.$params['collapsiblesection'].'_'.$params['layout'].'_state', $params['state'], $USER);
            return true;
        }

        return false;
    }

    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function send_spelling_mistake_parameters()
    {
        $url = new external_value(
                PARAM_URL,
                'Current url',
                VALUE_REQUIRED
                );
        $mistake = new external_value(
                PARAM_TEXT,
                'Mistake info',
                VALUE_REQUIRED
                );
        $phrase = new external_value(
                PARAM_TEXT,
                'Selected phrase',
                VALUE_DEFAULT,
				''
                );
        $beforemistaketext = new external_value(
                PARAM_TEXT,
                'Text before mistake',
                VALUE_DEFAULT,
				''
                );
        $aftermistaketext = new external_value(
                PARAM_TEXT,
                'Text after mistake',
                VALUE_DEFAULT,
				''
                );
        $userscomment = new external_value(
                PARAM_TEXT,
                'User\'s comment',
                VALUE_DEFAULT,
				''
                );
        $params = [
            'url' => $url,
            'mistake' => $mistake,
            'phrase' => $phrase,
            'start' => $beforemistaketext,
            'end' => $aftermistaketext,
            'comment' => $userscomment
        ];

        return new external_function_parameters($params);
    }

    /**
     * Тип результата выполнения сервисной функции
     *
     * @return external_value
     */
    public static function send_spelling_mistake_returns()
    {
        return new external_value(PARAM_TEXT, 'Result');
    }

    /**
     * AJAX-обработчик состояния сворачиваемого блока
     *
     * @return bool
     */
    public static function send_spelling_mistake($url, $mistake, $phrase, $start, $end, $comment)
    {
        global $USER;

        if ( empty($url) || empty($mistake) )
        {
            // Валидация данных на пустоту
            return false;
        }

        // формирование данных для события
        $eventdata = [
            'context' => context_system::instance(),
            'other' => [
                'url' => $url,
                'mistake' => $mistake,
                'phrase' => $phrase,
                'start' => $start,
                'end' => $end,
                'comment' => $comment
            ]
        ];

        // создание события о новой орфографической ошибки
        $event = spelling_mistake::create($eventdata);
        $event->trigger();

        return true;
    }


    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function get_login_form_parameters()
    {
        return new external_function_parameters([]);
    }

    /**
     * Тип результата выполнения сервисной функции
     *
     * @return external_value
     */
    public static function get_login_form_returns()
    {
        return new external_value(PARAM_RAW, 'Result');
    }

    /**
     * AJAX-обработчик состояния сворачиваемого блока
     *
     * @return bool
     */
    public static function get_login_form()
    {
        global $PAGE, $CFG;

        require_once("$CFG->libdir/authlib.php");

        $PAGE->set_context(null);

        $authsequence = get_enabled_auth_plugins(true);

        $loginform = new \core_auth\output\login($authsequence);

        $corerenderer = $PAGE->get_renderer('core');

        $loginformdata = $loginform->export_for_template($corerenderer);
        $loginformdata->cookieshelpiconformatted = $corerenderer->help_icon('cookiesenabled');
        $loginformdata->errorformatted = $corerenderer->error_text($loginformdata->error);

        return json_encode($loginformdata);
    }



    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function get_dock_icon_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(
                PARAM_INT,
                'courseid',
                VALUE_REQUIRED
            ),
            'pagelayout' => new external_value(
                PARAM_ALPHAEXT,
                'pagelayout',
                VALUE_REQUIRED
            ),
            'pagetype' => new external_value(
                PARAM_ALPHANUMEXT,
                'pagetype',
                VALUE_REQUIRED
            ),
            'subpage' => new external_value(
                PARAM_ALPHANUMEXT,
                'subpage',
                VALUE_REQUIRED
            ),
            'contextid' => new external_value(
                PARAM_INT,
                'contextid',
                VALUE_REQUIRED
            ),
            'blockinstanceid' => new external_value(
                PARAM_INT,
                'blockinstanceid',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Тип результата выполнения сервисной функции
     *
     * @return external_value
     */
    public static function get_dock_icon_returns()
    {
        return new external_value(PARAM_URL, 'Docked block icon url');
    }

    /**
     * Получить массив адресов иконок для блоков в доке
     *
     * @return bool
     */
    public static function get_dock_icon($courseid, $pagelayout, $pagetype, $subpage, $contextid, $blockinstanceid) {
        global $OUTPUT, $PAGE, $USER;

        // Setting pagetype and URL.
        $PAGE->set_pagetype($pagetype);
//         $PAGE->set_url('/lib/ajax/blocks.php', array('courseid' => $courseid, 'pagelayout' => $pagelayout, 'pagetype' => $pagetype));

        // Set context from ID, so we don't have to guess it from other info.
        $context = context::instance_by_id($contextid);
        $PAGE->set_context($context);
        if ($context->contextlevel == CONTEXT_MODULE) {
            $coursecontext = $context->get_course_context();
            $modinfo = get_fast_modinfo($coursecontext->instanceid);
            $cm = $modinfo->get_cm($context->instanceid);
            $PAGE->set_cm($cm);
        }

        // Setting layout to replicate blocks configuration for the page we edit.
        $PAGE->set_pagelayout($pagelayout);
        $PAGE->set_subpage($subpage);
        $PAGE->blocks->add_custom_regions_for_pagetype($pagetype);
        $pagetype = explode('-', $pagetype);
        switch ($pagetype[0]) {
            case 'my':
                $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
                break;
            case 'user':
                if ($pagetype[1] === 'profile' && $PAGE->context->contextlevel == CONTEXT_USER
                && $PAGE->context->instanceid == $USER->id) {
                    // A user can only move blocks on their own site profile.
                    $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
                } else {
                    $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
                }
                break;
        }
        $PAGE->blocks->load_blocks();
        $PAGE->blocks->create_all_block_instances();


        $icon = null;
        foreach($PAGE->blocks->get_regions() as $region) {
            /**
             * @var block_contents $bc
             */
            foreach($PAGE->blocks->get_content_for_region($region, $OUTPUT) as $bc)
            {
                if ($bc instanceof block_contents &&
                    $bc->blockinstanceid == $blockinstanceid)
                {
                    try {
                        $icon = $OUTPUT->get_block_icon($bc);
                    } catch(\Exception $ex){}
                    break 2;
                }
            }
        }

        return $icon;
    }
}