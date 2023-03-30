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
use local_pprocessing\resolver;
defined('MOODLE_INTERNAL') || die();

/**
 * Класс итератора черезз событие
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_based extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // проверка конфигов
        if ( ! is_array($this->result) || empty($this->result) || empty($this->config['scenario']) )
        {
            // данных недостаточно для отправки уведомлений
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'empty_result' => empty($this->result),
                    'is_array'     => is_array($this->result),
                    'scenario'      => $this->config['scenario']
                ],
                'inactivity explanation'
            );
            return;
        }
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        foreach ( $this->result as $key => $value )
        {
            try {
            // отправка события
                
                // собираем данные из исходного события, запустившего текущий сценарий, чтобы прокинуть в запускаемый итератором
                $eventdata = [];
                if ($relateduserid = $container->export('userid'))
                {
                    $eventdata['relateduserid'] = $relateduserid;
                }
                
                $eventdata['courseid'] = null;
                if ($courseid = $container->export('courseid'))
                {
                    $eventdata['courseid'] = $courseid;
                }
                if ($objecttable = $container->export('objecttable'))
                {
                    $eventdata['objecttable'] = $objecttable;
                }
                if ($objectid = $container->export('objectid'))
                {
                    $eventdata['objectid'] = $objectid;
                }
                if ($other = $container->export('data'))
                {
                    $eventdata['other'] = $other;
                } else {
                    $eventdata['other'] = [];
                }
                
                $eventdata['other']['iterate_item'] = container::make_json_encodable($value);
                if (!empty($this->config['iterate_item_var_name']))
                {
                    $eventdata['other']['iterate_item_var_name'] = $this->config['iterate_item_var_name'];
                }
                $eventdata['other']['scenario'] = $this->config['scenario'];
                
                // наше событие в init не устанавливает контекст, чтобы здесь мы могли указать, если понадобится
                // устанавливаем контекст курса, чтобы не было нотисов или системный (0)
                if (!array_key_exists('contextid', $eventdata))
                {
                    if (!empty($eventdata['courseid']))
                    {
                        $eventdata['context'] = \context_course::instance($eventdata['courseid']);
                    } else
                    {
                        $eventdata['context'] = \context_system::instance();
                    }
                }
                $event = \local_pprocessing\event\iteration_initialized::create($eventdata);
                
                if (!empty($this->config['trigger_event'])) {
                    // Требуется отправка события
                    $event->trigger();
                } else {
                    // Имитируем срабатывание события для запуска нужного сценария
                    resolver::resolve_event($event);
                }
                
            } catch (\Exception $e) {
                // во время формирования события возникла ошибка
                // запись в лог
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'error',
                    [
                        'scenariocode' => $scenariocode,
                        'source'       => $this->result,
                        'iteration'    => $key,
                        'error'        => $e->getMessage(),
                        'scenario'     => $this->config['scenario']
                    ]
                );
                return;
            }
            
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'success',
                [
                    'scenariocode' => $scenariocode,
                    'scenario'      => $this->config['scenario'],
                    'eventdata' => $eventdata
                ]
            );
        }
    }
    
}

