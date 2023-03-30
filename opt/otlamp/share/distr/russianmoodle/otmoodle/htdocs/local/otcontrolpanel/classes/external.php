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
 * Универсальная панель управления. Веб-сервисы
 *
 * @package    local_otcontrolpanel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_otcontrolpanel;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use local_otcontrolpanel\entity\entity;

class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_config_data_parameters()
    {
        return new external_function_parameters([]);
    }

    /**
     * Returns json-encoded possible config data
     * @return string json-encoded structure and config data
     */
    public static function get_config_data()
    {
        global $PAGE, $USER;

        $PAGE->set_context(null);

        // сервис используется только для организации доступа к редактированию,
        // поэтому закрываем доступ правами на редактирование
        $syscontext = \context_system::instance();
        if (!config::has_access_config_otcontrolpanel($USER->id)) {
            throw new \required_capability_exception($syscontext, 'local/otcontrolpanel:configmy', 'nopermissions', '');
        }

        $configstructure = ['entities' => []];

        foreach(entity::get_known_entities() as $entity) {

            $entityserializable = $entity->jsonSerialize();
            // специально не добавляем связи непосредственно в entity, чтобы избежать случайных зацикливаний
            $entityrelations = array_values($entity->get_known_relations());
            $entityserializable['entity-relations'] = $entityrelations;
            $entityserializable['entity-relations-count'] = count($entityrelations);

            $configstructure['entities'][] = $entityserializable;
        }
        $configstructure['entities-count'] = count($configstructure['entities']);


        return json_encode([
            'config_structure' => $configstructure,
            'configured_views' => view::get_configured_views()
        ]);
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function get_config_data_returns()
    {
        return new external_value(PARAM_RAW, 'json-encoded structure and config data');
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function save_config_parameters()
    {
        $yaml = new external_value(
            PARAM_RAW,
            'config yaml',
            VALUE_REQUIRED
        );
        return new external_function_parameters(['yaml' => $yaml]);
    }

    /**
     * Saves config
     * @return bool save result
     */
    public static function save_config($yaml)
    {
        global $PAGE, $USER;

        $PAGE->set_context(null);

        // проверка прав на редактирование
        $syscontext = \context_system::instance();
        if (!config::has_access_config_otcontrolpanel($USER->id)) {
            throw new \required_capability_exception($syscontext, 'local/otcontrolpanel:configmy', 'nopermissions', '');
        }

        $saveresult = config::save_user_config($yaml);
        return !empty($saveresult);
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function save_config_returns()
    {
        return new external_value(PARAM_BOOL, 'save result');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function restore_default_config_parameters()
    {
        return new external_function_parameters([]);
    }

    /**
     * deletes config from user mcov
     * @return bool result of user mcov deletion
     */
    public static function restore_default_config()
    {
        global $PAGE, $USER;

        $PAGE->set_context(null);

        // проверка прав на редактирование
        $syscontext = \context_system::instance();
        if (!config::has_access_config_otcontrolpanel($USER->id)) {
            throw new \required_capability_exception($syscontext, 'local/otcontrolpanel:configmy', 'nopermissions', '');
        }

        $deleteresult = config::delete_user_config();
        return !empty($deleteresult);
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function restore_default_config_returns()
    {
        return new external_value(PARAM_BOOL, 'result of user mcov deletion');
    }
}