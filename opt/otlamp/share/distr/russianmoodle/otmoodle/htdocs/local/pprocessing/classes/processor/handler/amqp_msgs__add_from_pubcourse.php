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
class amqp_msgs__add_from_pubcourse extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        
        otcomponent_phpamqplib\autoload::register();
        
        // Идентификатор курса
        $courseid = $container->read('course.id');
        // Стандартные поля курса и их производные
        $coursefields = [
            'id' => $courseid,
            'fullname' => $container->read('course.fullname'),
            'summary' => $container->read('course.summary'),
            'info_url' => (new \moodle_url('/local/crw/course.php', ['id' => $courseid]))->out(false),
            'url' => (new \moodle_url('/course/view.php', ['id' => $courseid]))->out(false)
        ];
        
        // Доп.поля курса
        $courseprops = [];
        $courseproprecs = $DB->get_records('crw_course_properties', ['courseid' => $courseid]);
        foreach($courseproprecs as $courseproprec)
        {
            $value = $courseproprec->value;
            if (explode('_', $courseproprec->name, 2)[0] == 'cff')
            {// Поле, созданное через настраиваемую форму, декодируем данные
                $value = json_decode($value);
            }
            $courseprops[$courseproprec->name] = $value;
        }
        
        // Данные из события (статусы, сервис)
        $eventdata = [
            'publication_oldstatus' => $container->read('data.oldstatus'),
            'publication_newstatus' => $container->read('data.newstatus'),
            'publication_service' => $container->read('data.service'),
        ];
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
                
        try {
            
            // получение пулла сообщений
            $messages = $container->export('amqpmessages');
            if (is_null($messages))
            {
                $messages = [];
            }
            $routingkey = 'moodle.status.update.'.$container->read('data.service');
            
            $rmq = new rmq();
            
            $messagebody = array_merge($coursefields, $courseprops, $eventdata);
            $messageproperties = [
                'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT
            ];
            
            if (!array_key_exists($routingkey, $messages))
            {
                $messages[$routingkey] = [];
            }
            
            // У сообщения есть еще ряд атрибутов, например - устойчивость к перезагрузке
            $messages[$routingkey][] =  $rmq->createMessage($messagebody, $messageproperties);
            
            $container->write('amqpmessages', $messages);
            
            // запись в лог успешного результата
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'success',
                [
                    'messagebody' => $messagebody,
                    'messageproperties' => $messageproperties
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

