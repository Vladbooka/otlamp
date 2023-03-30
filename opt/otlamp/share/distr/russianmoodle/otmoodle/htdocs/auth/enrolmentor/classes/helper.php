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

namespace auth_enrolmentor;

require_once($CFG->dirroot.'/auth/enrolmentor/auth.php');
require_once($CFG->dirroot.'/auth/enrolmentor/classes/task/updatementors.php');

use context_user;
use stdClass;
use auth_plugin_enrolmentor;

class helper {
    
    /**
     * __construct() HIDE: WE'RE STATIC
     */
    protected function __construct()
    {
        // static's only please!
    }
    
    /**
     * Возвращает основные и кастомные поля профиля пользователя
     * @return array массив полей
     */    
    static public function get_profile_fields() 
    {
        global $DB;
        
        $enrolmentor = new auth_plugin_enrolmentor();
        $userfields = array_merge($enrolmentor->userfields, $enrolmentor->get_custom_user_profile_fields());
        return array_combine($userfields, $userfields);

        return $fields;
    }
    
    /**
     * Назначает пользователя $user на роль $roleid в контексте пользователей $toenrol
     * @param array $toenrol пользователи, в контексте которых назначается роль
     * @param int $roleid идентификатор роли, которая должна быть назначена
     * @param stdClass $user объект пользователя, которому назначается роль $roleid в контексте пользователей $toenrol
     * @param bool $verbose выводить ли отладочную информацию, true для cron и false для web
     */
    static public function do_enrol($toenrol, $roleid, $user, $verbose = false)
    {
        foreach( $toenrol as $enrol ) 
        {
            role_assign($roleid, $user->id, context_user::instance($enrol)->id, '', 0, '');
            if ($verbose)
            {
                mtrace('User ' . $user->id . ' has role '
                    . $roleid . ' in context of user ' . $enrol . ' from now on.');
            }
        }
    }
    
    /**
     * Убирает назначение роли $roleid в контексте пользователей $tounenrol для пользователя $user
     * @param array $tounenrol пользователи, в контексте которых назначена роль
     * @param int $roleid идентификатор назначенной роли
     * @param stdClass $user объект пользователя, у которого убирается роль $roleid в контексте пользователей $tounenrol
     * @param bool $verbose выводить ли отладочную информацию, true для cron и false для web
     */
    static public function do_unenrol($tounenrol, $roleid, $user, $verbose = false)
    {
        foreach( $tounenrol as $unenrol ) 
        {
            role_unassign($roleid, $user->id, context_user::instance($unenrol)->id, '', 0, '');
            if ($verbose)
            {
                mtrace('User ' . $user->id . ' does not have role '
                    . $roleid . ' in context of user ' . $unenrol . ' anymore.');
            }
        }
    }    
    
    /**
     * Получить пользователей по списку идентификаторов
     * @param int|array $userids иднтификатор или массив идентификаторов пользователей
     * @return array массив объектов
     */
    public static function get_users_by_id($userids)
    {
        global $DB;
        if( empty($userids) )
        {
            return [];
        }
        list($insql, $params) = $DB->get_in_or_equal($userids);
        $sql = 'SELECT * FROM {user} WHERE id '. $insql;
        return $DB->get_records_sql($sql,$params);
        
    }
    
    /**
     * Возвращает данные из профиля пользователя по полю $fieldname (может быть кастомным, может быть основным)
     * @param int $userid идентификатор пользователя
     * @param string $fieldname название поля в конфиге плагина
     * @return string
     */
    public static function get_user_parent_list($userid, $fieldname)
    {
        global $DB;
        if( empty($userid) || empty($fieldname) )
        {
            return '';
        }
        if( strpos($fieldname, 'profile_field_') === 0 )
        {
            $fieldname = str_replace('profile_field_', '', $fieldname);
            $field = self::get_customfieldid_by_shortname($fieldname);
            $userinfo = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $field->id]);
            if( $userinfo )
            {
                return $userinfo->data;
            } else
            {
                return '';
            }
        } else 
        {
            $field = self::get_userfield($userid, $fieldname);
            if( $field )
            {
                return $field;
            } else 
            {
                return '';
            }
        }
        
        
    }
    
    /**
     * Получение идентификаторов пользователей, назаченных на роль с идентификатором $roleid в контексте пользователя $usercontext
     * @param context_user $usercontext контекст пользователя
     * @param int $roleid идентификатор роли
     * @return array массив идентификаторов пользователей
     */
    public static function get_users_in_usercontext_by_roleid($usercontext, $roleid)
    {
        global $DB;
        $userids = [];
        $users = $DB->get_records('role_assignments', ['contextid' => $usercontext->id, 'roleid' => $roleid], '', 'userid');
        if( $users )
        {
            foreach($users as $user)
            {
                $userids[] = $user->userid;
            }
        }
        return $userids;
    }
    
    /**
     * Получить кастомное поле по shortname
     * @param string $shortname
     */
    public static function get_customfieldid_by_shortname($shortname)
    {
        global $DB;
        return $DB->get_record('user_info_field', ['shortname' => $shortname]);
    }
    
    /**
     * Плучить значение основного поля профиля пользователя
     * @param int $userid идентификатор пользователя
     * @param string $fieldname имя поля
     * @return mixed|boolean
     */
    public static function get_userfield($userid, $fieldname)
    {
        global $DB;
        return $DB->get_field('user', $fieldname, ['id' => $userid]);
    }
    
    /**
     * Получение пользователей по полю $field
     * @param string $field поле профиля пользователя
     * @param string|array $value массив значений полей
     * @return array массив объектов
     */
    public static function get_users_by_field($field, $value)
    {
        global $DB;
        
        // в поле id допустимы только целочисленные значения - выполним предобработку значений
        if ($field == 'id' && is_array($value)) {
            foreach ($value as $k=>$v) {
                if (strval($v) != strval(intval($v))) {
                    // значение не является целым числом - удалим его
                    // иначе во время запроса возможно приведение типов и некорректное срабатывание
                    unset($value[$k]);
                }
            }
        }

        // значений для выборки нет - значит и выборку возвращаем пустую
        if (empty($value)) {
            return [];
        }
        
        list($insql, $params) = $DB->get_in_or_equal($value);
        $sql = 'SELECT * FROM {user} WHERE ' . $field . ' '. $insql . ' AND deleted = ?';
        $params[] = 0;
        $users = $DB->get_records_sql($sql, $params);
        if( $users )
        {
            return $users;
        } else 
        {
            return [];
        }
    }
    
    /**
     * Процесс назначения/снятия ролей по данным, сохраненным в выбранное поле пользователя
     * @param int $userchildid идентификатор пользователя, в контексте которого назначается/снимается роль
     * @param string $flag идентификтаор crud - c|r|u|d (запасная переменная)
     * @param bool $verbose выводить ли отладочную информацию, true для cron и false для web
     */
    public static function role_assignment_process($userchildid, $flag, $verbose = false)
    {
        $tounenrolusers = $toenrolusers = [];
        // Получаем контекст пользователя
        $usercontext = \context_user::instance($userchildid);
        // Получаем конфиг
        $config = get_config('auth_enrolmentor');
        if (empty($config->profile_field) || empty($config->role)) {
            // Не задано поле сохранения идентификатора куратора или роль, которую нужно назначить
            return;
        }
        if ( $config->profile_field != null )
        {
            // Получаем список пользователей, которые указаны в поле профиля пользователя
            $parentlist = self::get_user_parent_list($userchildid, $config->profile_field);
        }
        if ( ! empty($parentlist) )
        {
            // Разобьем полученный список в массив
            if (!empty($config->delimeter))
            {
                $delimeter = $config->delimeter;
            } else {
                $delimeter = ',';
            }
            $parentlist = explode($delimeter, $parentlist);
            // Получим объекты пользователей
            $newparents = self::get_users_by_field($config->compare, $parentlist);
            // Получим идентификаторы пользователей
            $newparentids = array_keys($newparents);
        } else
        {
            $newparentids = [];
        }
        // Получим идентификаторы пользователей, которые уже назначены на эту роль в контексте пользователя
        $oldparentids = self::get_users_in_usercontext_by_roleid($usercontext, $config->role);
        // Получаем массив идентификаторов пользователей, у которых надо снять назначенную роль
        $tounenrol = array_diff($oldparentids, $newparentids);
        // Получаем массив идентификаторов пользователей, которым надо назначить роль
        $toenrol = array_diff($newparentids, $oldparentids);
        
        if( empty($tounenrol) && empty($toenrol) )
        {// Если некого назначать на роль и некого снимать с роли, заканчиваем работу
            return;
        }
        
        if( ! empty($tounenrol) )
        {
            // Получим объекты пользователей, у которых надо снять назначенную роль
            $tounenrolusers = self::get_users_by_id($tounenrol);
        }
        if( ! empty($toenrol) )
        {
            // Получим объекты пользователей, которым надо назначить роль
            $toenrolusers = self::get_users_by_id($toenrol);
        }
        
        if( $tounenrolusers )
        {
            foreach($tounenrolusers as $oldparent)
            {
                // Снимем назначенные роли у нужных пользователей
                self::do_unenrol((array)$userchildid, $config->role, $oldparent, $verbose);
            }
        }
        if( $toenrolusers )
        {
            foreach($toenrolusers as $newparent)
            {
                // Назначим роли нужным пользователям
                self::do_enrol((array)$userchildid, $config->role, $newparent, $verbose);
            }
        }
    }
    
    /**
     * Проверить, имеется ли задача на обновление менторов для пользователей
     * @return bool результат проверки (true|false)
     */
    public static function is_task_added($action = 'updatementors') {
        global $DB;
        $classname = '\auth_enrolmentor\task\\' . $action;
        list($sqlin, $params) = $DB->get_in_or_equal($classname, SQL_PARAMS_NAMED);
        $sql = 'SELECT *
                  FROM {task_adhoc}
                 WHERE classname ' . $sqlin;
        if ($records = $DB->get_records_sql($sql, $params)) {
            return true;
        }
        return false;
    }
    
    /**
     * Добавление задачи и вывод уведомления о добавлении задачи на обновление кураторов в зависимости от изменения настроек
     * @param bool $paramupdated изменёны ли настройки: true - изменены, false - не изменены
     * @return void
     */
    public static function add_task($paramupdated = true) {
        
        if (!is_enabled_auth('enrolmentor')) {
            \core\notification::error(get_string('enrolmentor_disabled','auth_enrolmentor'));
            return;
        }
        
        \core\task\manager::queue_adhoc_task(new \auth_enrolmentor\task\updatementors());
        if ($paramupdated)
        {
            \core\notification::success(get_string('updatementors_task_added_paramupdated', 'auth_enrolmentor'));
        } else {
            \core\notification::success(get_string('updatementors_task_added', 'auth_enrolmentor'));
        }
    }
}