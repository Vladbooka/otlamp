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
use core_user;
use stdClass;

require_once($CFG->dirroot . '/user/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Базовый класс обработчика
 *
 * @package     local_pprocessing
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_user extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // получим пользователя из контейнера
        // отдельная переменная для наглядности
        $notactiveuserid = $container->read('user.id');
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        
        if (!is_null($notactiveuserid))
        {
            // обрабатывать нужно только ранее не обработанные данные
            if( ! $this->is_precedent_processed($scenariocode, $container) )
            {
                $user = core_user::get_user($notactiveuserid);
                if ($user->auth === 'manual' and is_siteadmin($user))
                {
                    // запись в лог
                    logger::write_log(
                        'processor',
                        $this->get_type()."__".$this->get_code(),
                        'debug',
                        [
                            'user' => $user,
                            'scenariocode' => $scenariocode,
                        ],
                        'Local administrator accounts can not be deleted.'
                    );
                }
                else
                {
                    if ( $user && user_delete_user($user) )
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
                                'userid' => $notactiveuserid
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
                                'userid' => $notactiveuserid
                            ]
                        );
                    }
                }
            }
        }
    }
}

