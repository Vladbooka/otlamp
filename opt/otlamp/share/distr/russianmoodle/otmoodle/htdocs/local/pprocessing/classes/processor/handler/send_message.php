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
use core\message\message;
use context_system;
use core_user;
use stdClass;
use local_pprocessing\container;
use local_pprocessing\logger;
defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_message extends base
{

    /**
     * валидация сообщения
     *
     * @param message $message
     *
     * @return bool
     */
    protected static function validate_message(message $message) : bool
    {
        if ( empty($message->smallmessage) || empty($message->fullmessage) || empty($message->fullmessagehtml) ||
                 empty($message->subject) || empty($message->userfrom) || empty($message->userto) || empty($message->component) )
        {
            // сообщение не прошло валидацию
            return false;
        }
        
        return true;
    }

    /**
     * отсылка сообщения
     *
     * @param message $message
     *
     * @return bool
     */
    protected static function send_message(message $message) : bool
    {
        if ( self::validate_message($message) && message_send($message) )
        {
            // сообщение прошло валидацию и успешно отправилось получателю
            return true;
        }
        
        return false;
    }

    /**
     * замена макроподстановок
     *
     * @param string $text
     * @param array $vars
     *
     * @return string
     */
    protected static function replace_macrosubstitutions($text, $container)
    {
        global $CFG, $PAGE;
        
        // без контекста не отформатировать текст в безопасный html
        // с использованием мудловских фильтров
        $PAGE->set_context(null);
        
        
        preg_match_all('/%{(.*?)}/', $text, $matches, PREG_SET_ORDER);
        foreach($matches as $match)
        {
            list($fullmatch, $groupmatch) = $match;
            $subject = $container->read($groupmatch);
            if (!is_null($subject))
            {
                $text = str_replace($fullmatch, $subject, $text);
            }
        }
        // форматирование текста
        $text = format_text($text, FORMAT_MOODLE);
        
        return $text;
    }

    /**
     * создание сообщения для отправки уведомлений получателям
     *
     * @param container $container
     * @param array $configs
     *
     * @return stdClass
     */
    protected static function build_message(container $container)
    {
        // Формирование баового сообщения
        $message = new message();
        
        // подумать как можно вынести в конфиги и настраивать
        $message->userfrom = core_user::get_noreply_user();
        $message->component = 'local_pprocessing';
        $message->fullmessageformat = FORMAT_HTML;
        
        return $message;
    }

    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        // получим пользователя из контейнера
        $userid = $container->read('user.id');
        
        // проверка конфигов
        if ( empty($userid) || empty($this->config['messagesubject']) || empty($this->config['messagefull']) )
        {
            // данных недостаточно для отправки уведомлений
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'empty_receivers' => $userid,
                    'empty_message_subject' => $this->config['messagesubject'],
                    'empty_messagefull' => $this->config['messagefull']
                ],
                'inactivity explanation'
            );
            return;
        }
        
        // билд сообщения
        $message = static::build_message($container);
        
        if (! empty($userid))
        {
            
            // обрабатывать нужно только ранее не обработанные данные
            if( ! $this->is_precedent_processed($scenariocode, $container) )
            {
                $pmessage = clone ($message);
                $pmessage->subject = strip_tags(self::replace_macrosubstitutions($this->config['messagesubject'], $container));
                $pmessage->smallmessage = strip_tags(self::replace_macrosubstitutions($this->config['messageshort'], $container));
                $pmessage->fullmessagehtml = self::replace_macrosubstitutions($this->config['messagefull'], $container);
                $pmessage->fullmessage = strip_tags($pmessage->fullmessagehtml);
                $pmessage->name = ! empty($this->config['message_name']) ? $this->config['message_name'] : 'notifications';
                
                // отправка уведомлений получателям
                $pmessage->userto = $userid;
                
                if ( self::send_message($pmessage) )
                {
                    // сохранение данных обработанного прецедента
                    $this->add_processed($scenariocode, $container);
                        
                    // запись в лог
                    logger::write_log(
                        'processor',
                        $this->get_type()."__".$this->get_code(),
                        'success',
                        [
                            'scenariocode' => $scenariocode,
                            'message' => $pmessage,
                            'subject' => $pmessage->subject,
                            'smallmessage' => $pmessage->smallmessage,
                            'fullmessagehtml' => $pmessage->fullmessagehtml,
                            'fullmessage' => $pmessage->fullmessage,
                            'userid' => $userid
                        ]
                    );
                } else
                {
                    // во время отправки возникла ошибка
                    // запись в лог
                    logger::write_log(
                        'processor',
                        $this->get_type()."__".$this->get_code(),
                        'error',
                        [
                            'scenariocode' => $scenariocode,
                            'message' => $pmessage,
                            'userid' => $userid
                        ]
                    );
                }
            }
        }
    }
}

