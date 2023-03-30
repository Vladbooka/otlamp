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

namespace local_pprocessing;

use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс с функциями-заглушками, тк пока нет хранилищ для сценариев/обработчиков
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stub
{
    /**
     * Возращаем массив зашитых сценариев
     *
     * @return array
     */
    public static function get_scenarios()
    {
        global $CFG;

        // планируется хранение сценариев в базе данных
        // на текущий момент используется только хардкод
        $scenarios = [];

        // получение локальных сценариев плагина
        $localscenariospath = $CFG->dirroot.'/local/pprocessing/scenarios';
        foreach(self::get_scenarios_from_path($localscenariospath) as $scenariocode => $customscenario)
        {
            $customscenario['status'] = $customscenario['status'] ?? self::is_scenario_enabled($scenariocode);
            $scenarios[$scenariocode] = $customscenario;
        }

        // получение кастомных сценариев клиента,
        // переопределяют локальный, если код сценария совпадает
        $customscenariospath = $CFG->dataroot.'/plugins/local_pprocessing/scenarios';
        foreach(self::get_scenarios_from_path($customscenariospath) as $scenariocode => $customscenario)
        {
            $customscenario['status'] = $customscenario['status'] ?? self::is_scenario_enabled($scenariocode);
            $scenarios[$scenariocode] = $customscenario;
        }

        return $scenarios;

    }

    /**
     * Получение сценариев, срабатывающих на переданные источник и название события
     *
     * @param string $source
     * @param string $eventname
     *
     * @return scenario[]
     */
    public static function get_active_scenarios($source, \core\event\base $event)
    {
        // метод имитирует работу с БД
        // когда реализуем хранилище, допишем методы
        $scenarios = static::get_scenarios();

        // массив отфильтрованных сценариев
        $filtered_scenarios = [];



        // фильтруем сценарии
        foreach ( $scenarios as $code => $scenario )
        {
            if ( ! empty($scenario['status']) && isset($scenario['events'])
                && is_array($scenario['events']) && in_array($event->eventname, $scenario['events']) )
            {
                // обработка
                if ($event->eventname == '\\local_pprocessing\\event\\iteration_initialized')
                {
                    $data = $event->get_data();
                    if (empty($data['other']['scenario']) || $data['other']['scenario'] != $code)
                    {
                        // сценарий, который необходимо запустить не указан или не соответствует текущему, пропускаем
                        continue;
                    }
                }
                // сценарий подходит под событие
                $filtered_scenarios[$code] = new scenario();

                if( isset($scenario['processors']) && is_array($scenario['processors']) )
                {
                    foreach( $scenario['processors'] as $processor )
                    {
                        self::add_processor($filtered_scenarios[$code], $processor);
                    }
                }
            }
        }

        return $filtered_scenarios;
    }

    public static function get_scenario_by_code($code) {

        $scenarios = static::get_scenarios();

        if (array_key_exists($code, $scenarios) && !empty($scenarios[$code]['status']))
        {
            $scenario = new scenario();

            if (isset($scenarios[$code]['processors']) && is_array($scenarios[$code]['processors']))
            {
                foreach($scenarios[$code]['processors'] as $processor)
                {
                    self::add_processor($scenario, $processor);
                }
            }

            return $scenario;
        } else {
            /**
             * @todo подумать правильно ли тут все
             */
            throw new moodle_exception('scenario_not_found');
        }
    }

    public static function add_processor(&$scenario, $processor)
    {
        if( ! empty($processor['type']) && ! empty($processor['code']) )
        {
            $processorconfig = [];
            if( isset($processor['config']) && is_array($processor['config']) )
            {
                $processorconfig = $processor['config'];
            }

            $compositekeyfields = null;
            if( isset($processor['composite_key_fields']) && is_array($processor['composite_key_fields']) )
            {
                $compositekeyfields = $processor['composite_key_fields'];
            }

            $resvar = null;
            if (isset($processor['result_variable']) && is_string($processor['result_variable']))
            {
                $resvar = $processor['result_variable'];
            }

            $input = null;
            if (isset($processor['params']) && is_array($processor['params']))
            {
                $input = $processor['params'];
            }

            $preconditions = null;
            if (isset($processor['preconditions']) && is_array($processor['preconditions']))
            {
                $preconditions = $processor['preconditions'];
            }

            $scenario->add_processor(
                $processor['type'],
                $processor['code'],
                $processorconfig,
                $compositekeyfields,
                $input,
                $resvar,
                $preconditions,
                $processor['desc']??null
            );
        }
    }

    /**
     * Настроено ли выполнение сценария через настройки
     *
     * @param string $scenariocode - код сценария
     * @return boolean
     */
    public static function is_scenario_enabled($scenariocode)
    {
        $configplugin = 'local_pprocessing';

        // обработка настроек, созданных не по принятому соглашению
        switch($scenariocode)
        {
            case 'spelling_mistake':
                $configname = 'spelling_mistake_message_status';
                break;
            case 'student_enrolled':
                $configname = 'student_enrolled_message_status';
                break;
            case 'teacher_enrolled':
                $configname = 'teacher_enrolled_message_status';
                break;
            default:
                $configname = $scenariocode . '__status';
                break;
        }

        try {
            $configvalue = get_config($configplugin, $configname);
        } catch(\dml_exception $ex) {
            $configvalue = false;
        }

        return !empty($configvalue);
    }

    /**
     * Получение всех сценариев, размещенных в виде файлов по указанному пути
     *
     * @param string $path - пусть до категории со сценариями
     * @return array - массив конфигов сценариев
     */
    public static function get_scenarios_from_path($path)
    {
        $scenarioslist = [];

        // получение php-сценариев
        foreach(glob($path.'/*.php') as $filepath)
        {
            $scenariocode = basename($filepath, '.php');
            include $filepath;
            if( isset($scenarios[$scenariocode]))
            {
                $scenarioslist[$scenariocode] = $scenarios[$scenariocode];
            }
        }

        // получение yaml-сценариев, переопределят php-сценарии, если код сценария (имя файла) совпадет
        foreach(glob($path.'/*.yaml') as $filepath)
        {
            $scenariocode = basename($filepath, '.yaml');
            try {
                $scenarioslist[$scenariocode] = \otcomponent_yaml\Yaml::parseFile($filepath);

            } catch(\otcomponent_yaml\Exception\ParseException $ex)
            {
                // не удалось распарсить - значит нет такого сценария :)
            }
        }

        return $scenarioslist;
    }
}

