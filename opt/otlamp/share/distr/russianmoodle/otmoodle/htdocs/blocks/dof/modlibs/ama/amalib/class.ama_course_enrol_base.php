<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

abstract class ama_course_enrol_base
{
    protected $dof;
    
    //id курса, в котором должна быть определена подписка
    protected $instance = null;
    
    protected $plugin = null;
    
    /**
     * Возвращает имя плагина способа записи
     * @return string
     */
    public function get_name()
    {
        return $this->plugin->get_name();
    }
    
    /**
     * Добавляет инстанс способа записи на курс
     * @param stdClass $course объект курса
     * @param array $fields поля инстанса, кроме enrol, courseid, sortorder
     * @return int идентификатор добавленного инстанса
     */
    public function add_instance($course, $fields = null)
    {
        $id = $this->plugin->add_instance($course, $fields);
        $this->set_instance($id);
        return $id;
    }
    
    /**
     * Запись пользователя на курс
     * @param int $userid идентификатор пользователя
     * @param int $roleid идентификатор роли
     * @param int $timestart время начала подписки
     * @param int $timeend время окончания подписки
     * @param int $status статус подписки ENROL_USER_ACTIVE|ENROL_USER_SUSPENDED
     * @param bool $recovergrades флаг необходимости попытаться восстановить оценки
     * @return boolean
     */
    public function enrol_user($userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null)
    {
        if( ! empty($this->instance) )
        {
            try
            {
                $this->plugin->enrol_user($this->instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades);
                $this->dof->add_to_log('modlib', 'ama', 'enrol', 'view.php?id=' . $this->instance->courseid, '', $userid);
            } catch ( coding_exception $e )
            {// Ошибка записи на курс
                $this->dof->add_to_log('modlib', 'ama', 'enrol_error', 'view.php?id=' . $this->instance->courseid, $e->errorcode, $userid);
                return false;
            } catch ( dml_exception $e )
            {// Ошибка запроса в БД
                $this->dof->add_to_log('modlib', 'ama', 'enrol_error', 'view.php?id=' . $this->instance->courseid, $e->errorcode, $userid);
                return false;
            }
            return true;
        }
        return false;
    }
    
    /**
     * Устанавливает инстанс способа записи
     * @param int $id идентификатор инстанса
     */
    protected function set_instance($id)
    {
        global $DB;
        $this->instance = $DB->get_record('enrol', ['id' => $id]);
    }
    
    /**
     * Отписывает пользователя из курса
     * @param int $userid идентификатор пользователя
     */
    public function unenrol_user($userid)
    {
        if( ! empty($this->instance) )
        {
            $this->plugin->unenrol_user($this->instance, $userid);
        }
    }
    
    /**
     * Получить инстанс способа записи на курс
     * @param int $courseid идентификатор курса
     * @return stdClass|false|NULL
     */
    public function get_instance($courseid)
    {
        global $DB;
        if( ! empty($this->instance) )
        {
            return $this->instance;
        } else
        {
            if( $instance = $DB->get_record('enrol', ['enrol' => $this->get_name(), 'courseid' => $courseid]) )
            {
                $this->set_instance($instance->id);
                return $this->instance;
            }
            return null;
        }
    }
    
    /**
     * Установить статус инстанса способа записи на курс (0 или 1)
     * @param int $status 0|1
     */
    public function set_instance_status($status)
    {
        global $DB;
        if( ! empty($this->instance) )
        {
            $this->instance->status = $status;
            $DB->update_record('enrol', $this->instance);
        }
    }
}