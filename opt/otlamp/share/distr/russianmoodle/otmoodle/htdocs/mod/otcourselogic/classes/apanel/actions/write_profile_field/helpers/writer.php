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
 * Хелпер
*
 * @package    mod_otcourselogic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_otcourselogic\apanel\actions\write_profile_field\helpers;

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

use core_user;
use moodle_exception;
use stdClass;
use mod_otcourselogic\apanel\helper;
use context_course;

/**
 * Контроллер записи на курс
 */
class writer
{
    /**
     * Запись в поле профиля
     * 
     * @param stdClass $instance
     * @param int $userid
     * @param stdClass $course
     * @param stdClass $action_instance
     * 
     * @return bool
     */
    public static function write_to_field($instance, $userid, $course, $action_instance, &$pool)
    {
        global $DB;
        
        // Пользователя
        $user = $DB->get_record('user', ['id' => $userid]);
        if ( empty($user) )
        {
            return false;
        }
        
        // Получение контекста курса, на который подписываем пользователя
        $context = context_course::instance($course->id);
        
        if ( ! has_capability('mod/otcourselogic:is_student', $context, $user->id) )
        {// Пользователь является преподавателем
            return false;
        }

        // Получение данных из инстанса
        $data = unserialize(base64_decode($action_instance->options));
        $all_fields = helper::get_all_fields();
        
        if ( ! in_array($data->field, $all_fields) )
        {
            // Такого поля не существует
            return false;
        }
        
        // Запись для сохранения
        $user_to_update = new \stdClass();
        $user_to_update->id = $userid;
        $value = $user_to_update->{$data->field} = strip_tags(helper::replace_macrosubstitutions($data->text, $instance, $course, $user, $pool));
        
        // Валидация доп полей профиля пользователя
        $errors = profile_validation($user_to_update, []);
        
        // Валидация основных полей профиля пользователя
        $uservalidation = core_user::validate($user_to_update);
        
        if  ( ($uservalidation !== true) || (! empty($errors)) )
        {
            // Во время валидации полей произошла ошибка
            return false;
        }
        
        try 
        {
            user_update_user($user_to_update, false, true);
            profile_save_data($user_to_update);
        } catch ( moodle_exception $e )
        {
            return false;
        }
        
        // Добавление поля в пулл
        $elem = 'var_' . $action_instance->id . '_field';
        $pool[$elem] = $value;
        
        return true;
    }
}