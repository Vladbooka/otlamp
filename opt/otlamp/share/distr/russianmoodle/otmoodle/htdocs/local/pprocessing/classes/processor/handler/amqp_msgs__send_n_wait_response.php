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
class amqp_msgs__send_n_wait_response extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        otcomponent_phpamqplib\autoload::register();
        
        // формирование уникального идентификатора
        $corrid = uniqid();
        // флаг обработки сообщения
        $acked = false;
        // ответ из amqp
        $response = null;
        
        
        $rmq = new rmq();
        $adapter = $rmq->getAdapter($this->config['rabbitmq']);
        
        $messagedata = $this->config['message_varnames'];
        $data = [];
        if (!empty($messagedata) && is_array($messagedata))
        {
            foreach($messagedata as $varname)
            {
                $data[$varname] = $container->read($varname);
            }
        }
        
        // положить в очередь запрос nmfo.login_url.request.khipu
        $message = $rmq->createMessage($data, ['correlation_id' => $corrid]);
        $adapter->basic_publish(
            $message,
            $this->config['exchange_name'],
            $this->config['routing_key']
        );
        
        $queue = $this->config['queue_to_listen'];
        // ждать из другой очереди ответа
        $adapter->consumeCustomCondition(
            $queue,
            function($cmdmsg) use ($corrid, &$acked, &$response, $queue) {
                
                $channel = $cmdmsg->delivery_info['channel'];
                
                // проверка сообщения на соответствие ожидаемому
                if ($corrid == $cmdmsg->get('correlation_id'))
                {
                    $decoded = json_decode($cmdmsg->body, true);
                    
                    if (!is_null($decoded))
                    {
                        $response = $decoded;
                    }
                    
                    // помечаем сообщение обработанным
                    $channel->basic_ack($cmdmsg->delivery_info['delivery_tag']);
                    // фиксируем результат обработки
                    $acked = true;
                }
            },
            '', false, false, false, false, null, [],
            function () use (&$acked) {
                // функция-условие будет выполняться до тех пор, пока мы
                // не обработаем нужное нам сообщение, которое мы ждем
                return $acked;
            }
        );
        
        $container->write('amqp_response', $response);
        
        return $response;
    }
}

