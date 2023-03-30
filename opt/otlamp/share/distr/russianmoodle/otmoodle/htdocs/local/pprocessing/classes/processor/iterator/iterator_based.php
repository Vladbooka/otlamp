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
namespace local_pprocessing\processor\iterator;

use local_pprocessing\container;
use local_pprocessing\logger;
use local_pprocessing\stub;
use local_pprocessing\executor;
use Iterator;
use local_pprocessing\processor\exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс итератора для обработки записей, полученных через итератор recordset
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class iterator_based extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $CFG;
        // Если передали объект в переменной - работаем с ним
        $result = $this->get_optional_parameter('rs', null);
        // Если не передали, то используем результат последнего обработчика
        if (is_null($result)) {
            $result = $this->result;
        }
        $mtrace = $this->get_optional_parameter('mtrace', null);
        // проверка конфигов
        if (empty($result) || empty($this->config['scenario']) || !($result instanceof Iterator)) {
            // данных недостаточно для отправки уведомлений
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type() . '__' . $this->get_code(),
                'debug',
                [
                    'is_empty_result'         => empty($result),
                    'scenario'                => $this->config['scenario'],
                    'not_implements_iterator' => !($result instanceof Iterator)
                ],
                'inactivity explanation'
            );
            return;
        }
        
        $iteratorscenario = stub::get_scenario_by_code($this->config['scenario']);
        $this->debugging('scenario', [
            'scenario' => var_export($iteratorscenario, true),
            'lastresult' => var_export($result, true),
            
        ]);
        $timestart = time();
        foreach ($result as $record) {
            // Вывод информации в лог крона, чтобы понимать с каким объектом сейчас идет работа
            if (!is_null($mtrace) && $CFG->debugdeveloper) {
                $a = new stdClass();
                if (!empty($mtrace['properties'])) {
                    foreach ($mtrace['properties'] as $property) {
                        $a->{$property} = $record->{$property} ?? null;
                    }
                }
                $a->mtracetime = date('Y-m-d H:i:s', time());
                if (!empty($mtrace['identifier']) 
                    && get_string_manager()->string_exists($mtrace['identifier'], 'local_pprocessing')) {
                    mtrace(get_string($mtrace['identifier'], 'local_pprocessing', $a));
                }
            }
            try {
                // Пишем в контейнер каждую переданную запись
                $container->write($this->config['iterate_item_var_name'], $record, true, true);
                // Запускаем работу сценария без очистки контейнера, чтобы иметь доступ к переменным, ранее добавленным в контейнер
                executor::scenario_go($iteratorscenario, $this->config['scenario'], $container, $timestart, $record);
            } catch (exception $ex) {
                $errordata = [
                    'scenario_code' => $this->config['scenario'],
                    'exception_code' => $ex->getCode(),
                    'exception_message' => $ex->getMessage(),
                    'current' => $result->current(),
                    'key' => $result->key(),
                ];
                switch($ex->getCode()) {
                    case 200:
                        logger::write_log('processor', '<- ' . $ex->get_processor()->get_full_code(), 'debug', $errordata, 'scenario execution aborted', $timestart);
                        // плановый выход из сценария - переходим к следующему сценарию
                        continue 2;
                        break;
                    default:
                        logger::write_log('processor', '<- ' . $ex->get_processor()->get_full_code(), 'error', $errordata, 'scenario execution aborted', $timestart);
                        // возникла ошибка - переходим к следующему сценарию
                        continue 2;
                        break;
                }
                return;
            }
            
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type() . '__' . $this->get_code(),
                'success',
                [
                    'scenariocode' => $this->config['scenario'],
                    'scenario'     => $iteratorscenario,
                ]
            );
        }
        return $result;
    }
    
}

