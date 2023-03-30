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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Хелпер для отправки уведомлений по праву
 */

namespace auth_otoauth\helper;

use stdClass;
use core\message\message;
use core_user;
use context_system;
use moodle_url;
use html_writer;

/**
 * Контроллер отправки уведомлений
 */
class send_notifications_by_capabilities
{
    // переменные сообшеня
    private $messagesubject;
    private $messageshort;
    private $messagefull;
    
    /**
     * 
     * @param string $capability
     */
    function __construct($messagesubject, $messageshort, $messagefull) {
        $this->messagesubject = $messagesubject; 
        $this->messageshort = $messageshort; 
        $this->messagefull = $messagefull;
    }
    
    /**
     * Валидация сообщения
     *
     * @param message $message
     * @return bool
     */
    private function validate_message(message $message) : bool {
        if ( empty($message->smallmessage) || empty($message->fullmessage) || empty($message->fullmessagehtml) ||
            empty($message->subject) || empty($message->userfrom) || empty($message->userto) || 
            empty($message->component) ) {
            // сообщение не прошло валидацию
            return false;
        }
        return true;
    }
    
    /**
     * Отсылка сообщения
     *
     * @param message $message
     * @return bool/string
     */
    private function send_message(message $message) : bool {
        if ( ! $this->validate_message($message) ) {
            // сообщение  не прошло валидацию
            return 'message failed validation';
        }
        if (! message_send($message) ) {
            // не отправилось получателю
            return 'not sent to the recipient';
        }
        return true;
    }
    
    /**
     * Получение основных полей профиля для макроподстановки
     *
     * @return string[]
     */
    public static function get_macrosubstitution_fields()
    {
        return [
            'city',
            'country',
            'lang',
            'url',
            'idnumber',
            'institution',
            'department',
            'phone1',
            'phone2',
            'address',
            'firstnamephonetic',
            'lastnamephonetic',
            'middlename',
            'alternatename',
            'firstname',
            'lastname',
            'email',
            'description',
            'username'
        ];
    }
    
    /**
     * Замена макроподстановок в строке
     * 
     * @param string $string
     * @param stdClass $user - запись пользователя
     * @return string
     */
    private function replace_macrosubstitutions($string, stdClass $user ) {
        global $CFG;
        // Формирование подстановок в сообщении
        $macrosubstitutionsdata = new stdClass();
        // Макроподстановки пользователя
        $macrosubstitutionsdata->userfullname = fullname($user);
        $url = new moodle_url('/user/editadvanced.php', ['id' => $user->id]);
        $macrosubstitutionsdata->userprofileediturl = $url->out();
        
        $doflibpath = $CFG->dirroot . '/blocks/dof/locallib.php';
        if (file_exists($doflibpath)) {
            require_once($doflibpath);
            global $DOF;
            if( ! is_null($user->id) ) {
                // получил список полей
                $fields = self::get_macrosubstitution_fields();
                $customfields = $DOF->modlib('ama')->user(false)->get_user_custom_fields_list();
                // получим поля без валидации
                $ufd = $DOF->modlib('ama')->user(false)->get_user_fields($user, $fields);
                $upfd = $DOF->modlib('ama')->user(false)->get_user_profilefields($user, $customfields);
                // запишем в макроподстановки
                foreach(array_merge($ufd, $upfd) as $key => $field) {
                    $fieldkeyname = $field->shortname;
                    if (substr($key, 0, 18) == 'user_profilefield_') {
                        $fieldkeyname = 'profile_field_' . $field->shortname;
                    }
                    $macrosubstitutionsdata->$fieldkeyname =$field->displayvalue;
                }
            }
        }
        
        $macrosubstitutionsdata->currentdate = date('d-m-Y H:i:s', time());
        $message = $string;
        // Обработка макроподстановок
        if ( ! empty($macrosubstitutionsdata) ) {
            $matches = null;
            if( preg_match_all('/{(.+)}/mU', $message, $matches) ) {
                foreach($matches[1] as $key => $match) {
                    if( property_exists($macrosubstitutionsdata, strtolower($match)) ) {
                        $message = str_replace($matches[0][$key], $macrosubstitutionsdata->{strtolower($match)}, $message);
                    }
                }
            }
        }
        return $message;
    }
    
    /**
     * Форирование и отправка сообщений
     * 
     * @param stdClass $user
     * @param string $capability
     * @return boolean/string
     */
    public function send(stdClass $user, string $capability) {
        if (! empty($user) && ! empty($this->messagesubject) && ! empty($this->messagefull) && ! empty($capability)) {
            // Формирование сообщения
            $message = new message();
            $message->userfrom = core_user::get_noreply_user();
            $message->component = 'auth_otoauth';
            $message->fullmessageformat = FORMAT_HTML;
            $message->subject = strip_tags($this->replace_macrosubstitutions($this->messagesubject, $user));
            $message->smallmessage = strip_tags($this->replace_macrosubstitutions($this->messageshort, $user));
            $message->fullmessagehtml = $this->replace_macrosubstitutions($this->messagefull, $user);
            $message->fullmessage = strip_tags($message->fullmessagehtml);
            $message->name = 'messages_new_suspended_user';
            // получим пользователей по возможности
            $admusers = get_users_by_capability(context_system::instance(), $capability);
            $status = true;
            // отправка уведомлений получателям
            foreach ( $admusers as $admuserid => $admuser ) {
                // установим получателя
                $message->userto = $admuserid;
                // отправим сообщение
                $status = $this->send_message($message);
            }
            return $status;
        }
        return 'wrong defaults params';
    }
}