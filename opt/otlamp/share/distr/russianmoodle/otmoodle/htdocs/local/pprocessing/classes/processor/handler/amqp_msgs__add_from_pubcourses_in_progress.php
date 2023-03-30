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
class amqp_msgs__add_from_pubcourses_in_progress extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        
        otcomponent_phpamqplib\autoload::register();
        
        $epcourses = $container->export('pubcourses_in_progress');
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        try {
            
            // получение пулла сообщений
            $messages = $container->export('amqpmessages');
            if (is_null($messages))
            {
                $messages = [];
            }
            
            $rmq = new rmq();
                        
            foreach($epcourses as $epcode => $courses)
            {
                $routingkey = 'moodle.status.request.'.$epcode;
                
                if (!array_key_exists($routingkey, $messages))
                {
                    $messages[$routingkey] = [];
                }
                
                foreach($courses as $courseid)
                {
                    
                    $messagebody = [
                        'id' => $courseid
                    ];
                    $messageproperties = [
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT
                    ];
                    
                    // У сообщения есть еще ряд атрибутов, например - устойчивость к перезагрузке
                    $messages[$routingkey][] =  $rmq->createMessage($messagebody, $messageproperties);
                    
                }
            }
            
            $container->write('amqpmessages', $messages);
            
            // запись в лог успешного результата
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'success',
                [
                    'messages' => $messages,
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
            return false;
        }
        
    }
}

