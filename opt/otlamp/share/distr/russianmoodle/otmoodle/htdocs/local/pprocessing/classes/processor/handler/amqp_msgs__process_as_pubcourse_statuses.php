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
class amqp_msgs__process_as_pubcourse_statuses extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB;
        
        $readmessages = $container->export('amqp_read_messages');
        if (is_array($readmessages))
        {
            foreach ($readmessages as $queuename=>$messages )
            {
                
                list($serviceshortname, $suffix) = explode('.', $queuename, 2);
                
                if ($suffix != 'status.update.moodle')
                {
                    continue;
                }
                $serviceclass = '\block_mastercourse\\eduportal\\'.$serviceshortname;
                
                foreach ($messages as $message)
                {
                    
                    $body = $message->getBody();
                    $decodedmsg = json_decode($body);
                    
                    // TODO LOG!!!
                    if (is_null($decodedmsg))
                    {
                        // запись в лог успешного результата
                        logger::write_log(
                            'processor',
                            $this->get_type()."__".$this->get_code(),
                            'error',
                            [
                                'info' => 'message cannot be decoded',
                                'message' => $message,
                            ]
                        );
                        continue;
                    }
                    
                    
                    
                    if (isset($decodedmsg->courseid) && isset($decodedmsg->newstatus))
                    {
                        $coursecontext = \context_course::instance($decodedmsg->courseid);
                        /** @var \block_mastercourse\eduportal\nmfo $serviceinstance */
                        $serviceinstance = new $serviceclass($coursecontext);
                        $serviceinstance->set_new_status(
                            $decodedmsg->newstatus,
                            true,
                            false,
                            $decodedmsg->status_reason ?? ''
                        );
                    }
                }
            }
        }
    }
}

