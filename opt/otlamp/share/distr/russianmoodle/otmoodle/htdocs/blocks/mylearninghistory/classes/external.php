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
 * Блок История обучения. Веб-сервисы.
 *
 * @package    block
 * @subpackage mylearninghistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_mylearninghistory;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use otcomponent_customclass\utils;
use moodle_url;
use block_mylearninghistory\form\courses_filter_rules;
use stdClass;

class external extends external_api
{
    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function get_courses_filter_form_parameters()
    {
        $config = new external_value(
            PARAM_TEXT,
            'Config name for need form',
            VALUE_REQUIRED
        );
        $params = [
            'config' => $config
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Тип результата выполнения сервисной функции
     *
     * @return external_value
     */
    public static function get_courses_filter_form_returns()
    {
        return new external_value(PARAM_RAW, 'HTML code for the form');
    }
    
    /**
     * AJAX-обработчик состояния сворачиваемого блока
     *
     * @return bool
     */
    public static function get_courses_filter_form($config)
    {
        global $PAGE;
        $PAGE->set_context(null);
        $url = new moodle_url('/admin/settings.php?', ['section' => 'blocksettingmylearninghistory']);
        $customformhtml = '';
        $customcoursefields = get_config('local_crw', 'custom_course_fields');
        $result = utils::parse($customcoursefields);
        if ($result->is_form_exists()) {
            // Форма
            $customform = $result->get_form();
            // Сохраненные данные
            $cffrecordsjson = get_config('block_mylearninghistory', $config);
            $cfdata = json_decode($cffrecordsjson);
            // инициализация формы
            $customform->setForm($url->out(false), null, 'post', '', ['class' => $config]);
            // Установка хранящихся в БД данных к форме
            $customform->set_data($cfdata);
            // Рендер формы
            $customformhtml = $customform->render();
        }
        
        return json_encode($customformhtml);
    }
    
    /**
     * Список входящих параметров
     *
     * @return external_function_parameters
     */
    public static function get_courses_filter_rules_form_parameters()
    {
        $config = new external_value(
            PARAM_TEXT,
            'Config name for need form',
            VALUE_REQUIRED
        );
        $params = [
            'config' => $config
        ];
        
        return new external_function_parameters($params);
    }
    
    /**
     * Тип результата выполнения сервисной функции
     *
     * @return external_value
     */
    public static function get_courses_filter_rules_form_returns()
    {
        return new external_value(PARAM_RAW, 'HTML code for the form');
    }
    
    /**
     * AJAX-обработчик состояния сворачиваемого блока
     *
     * @return bool
     */
    public static function get_courses_filter_rules_form($config)
    {
        global $PAGE;
        $PAGE->set_context(null);
        $url = new moodle_url('/admin/settings.php?', ['section' => 'blocksettingmylearninghistory']);
        $formhtml = '';
        // Сохраненные данные
        $rulesjson = get_config('block_mylearninghistory', $config);
        $rulesdata = json_decode($rulesjson);
        $customdata = new stdClass();
        $customdata->config = $config;
        $form = new courses_filter_rules(
            $url->out(false), $customdata, 'post', '', ['class' => $config]);
        // Установка хранящихся в БД данных к форме
        $form->set_data($rulesdata);
        $formhtml = $form->render();
        
        return json_encode($formhtml);
    }
}