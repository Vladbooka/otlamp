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

use core_user;
use local_pprocessing\container;
use local_pprocessing\logger;
defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика сохранения паролей
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_password extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        global $DB, $CFG;
        
        // уникальный код сценария
        $scenariocode = $container->read('scenario.code');
        // получим пользователя из контейнера
        $userid = $container->read('user.id');
        // проверим существование usera
        if ( empty($userid) )
        {
            // данных недостаточно для отправки уведомлений
            // запись в лог
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'empty_users' => empty($userid)
                ],
                'inactivity explanation'
            );
            return;
        }
        // получим пароль и проверим что не пуст
        $password = $this->get_required_parameter('password');
        if (empty($password)) {
            // запись в лог что пароль отсутствует
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'error',
                [
                    'scenariocode' => $scenariocode,
                    'userid' => $userid
                ],
                'password is empty'
                );
            return false;
        }
        // получим тип пароля
        $passtype = $this->get_optional_parameter('password_type', 'plaintext');
        switch($passtype) {
            case 'md5':
                $DB->set_field('user', 'password',  $password, ['id' => $userid]);
                $user = $DB->get_record('user', ['id' => $userid]);
                \core\event\user_password_updated::create_from_user($user)->trigger();
                // Remove WS user tokens.
                if (!empty($CFG->passwordchangetokendeletion)) {
                    require_once($CFG->dirroot.'/webservice/lib.php');
                    \webservice::delete_user_ws_tokens($user->id);
                }
                break;
            case 'plaintext':
                $user = core_user::get_user($userid);
                update_internal_user_password($user, $password, true);
                break;
           default:
               // запись в лог что тип пароля указан не верно
               logger::write_log(
               'processor',
               $this->get_type()."__".$this->get_code(),
               'error',
               [
               'scenariocode' => $scenariocode,
               'userid' => $userid
               ],
               'password type is incorrect'
                   );
               return false;
        }
        // запись в лог об удачной смене пароля
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'success',
            [
                'scenariocode' => $scenariocode,
                'userid' => $userid
            ]
        );
        
        return true;
    }
}

