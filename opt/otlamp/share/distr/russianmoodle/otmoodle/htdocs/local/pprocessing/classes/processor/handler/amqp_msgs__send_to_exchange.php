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
namespace local_pprocessing\processor\handler;
use local_pprocessing\container;
use local_pprocessing\logger;
use otcomponent_rabbitmq\main as rmq;
use otcomponent_phpamqplib;

defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class amqp_msgs__send_to_exchange extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        otcomponent_phpamqplib\autoload::register();
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        // amqp-сообщение
        $amqpmessages = $container->export('amqpmessages');
        
        if (!empty($this->config['exchange_name']) && is_array($amqpmessages))
        {
        
            try {
                $rmq = new rmq();
                
                $adapter = $rmq->getAdapter($this->config['rabbitmq']);
                
                
                foreach($amqpmessages as $routingkey => $messages)
                {
                    foreach($messages as $message)
                    {
                        $adapter->basic_publish(
                            $message,
                            $this->config['exchange_name'],
                            is_null($routingkey) ? '' : $routingkey
                        );
                    }
                }
                
                $adapter->close_channel();
                $rmq->closeAll();
                
                // запись в лог успешного результата
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'success',
                    [
                        'scenariocode' => $scenariocode,
                        'exchange_name' => $this->config['exchange_name'],
                        'amqpmessages' => $amqpmessages
                    ]
                );
            } catch (\Exception $ex)
            {
                // запись в лог ошибки
                logger::write_log(
                    'processor',
                    $this->get_type()."__".$this->get_code(),
                    'error',
                    [
                        'scenariocode' => $scenariocode,
                        'exception' => $ex->getMessage(),
                        'trace' => $ex->getTraceAsString()
                    ]
                );
            }
            
        } else
        {
            // запись в лог ошибки валидации
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'error',
                [
                    'scenariocode' => $scenariocode,
                    'exchange_name' => $this->config['exchange_name'] ?? 'missing',
                    'amqpmessages' => $amqpmessages ?? 'missing'
                ],
                'missing required parameters'
            );
        }
    }
}

