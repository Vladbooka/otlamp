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

//Подключаем класс для работы с курсом
require_once('class.ama_course.php');
// Подключение дополнительных библиотек
require_once('class.ama_course_enrol_manual.php');
require_once('class.ama_course_enrol_dof.php');
require_once(dirname(realpath(__FILE__)) . '/../../../../../lib/enrollib.php');

class ama_course_enrol_manager
{
    protected $dof;
    
    //id курса, в котором должна быть определена подписка
    protected $courseid = 0;
    
    public function __construct($courseid, $enrolid = false)
    {
        global $DOF;
        $this->dof = $DOF;
        $this->courseid = (int)$courseid;
        
        if( ! empty($enrolid) )
        {
            $this->instance = $this->get_instance($enrolid);
        } else 
        {
            $this->instance = false;
        }
    }
    
    /**
     * Получить интанс по идентификатору
     * @param int $enrolid идентификатор инстанса записи на курс
     * @return mixed|stdClass|false
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    protected function get_instance($enrolid)
    {
        global $DB;
        return $DB->get_record('enrol', ['id' => $enrolid, 'courseid' => $this->courseid]);
    }
    
    /**
     * Получить объект для работы с нужным способом записи на курс
     * @return NULL|ama_course_enrol_[dof,manual,...]
     */
    public function get_instance_manager()
    {
        if ( ! empty($this->instance->enrol) )
        {
            $class = 'ama_course_enrol_' . $this->instance->enrol;
            if( ! class_exists($class) )
            {// Класс работы с модулем не найден
                return null;
            }
        
            $modulemanager = new $class($this->instance);
        } else
        {
            $modulemanager = null;
        }
        
        return $modulemanager;
    }
    
    /**
     * Получить инстанс enrol_dof в курсе
     * @return stdClass|boolean
     */
    public function get_dof_enrol_instance()
    {
        // получаем все плагины подписки, доступные в этом курсе
        $instances = enrol_get_instances($this->courseid, false);
        
        // получаем название используемого в текущий момент плагина подписки
        $dofinstancename = enrol_get_plugin('dof')->get_name();
        foreach ( $instances as $instance )
        {// проверяем, есть ли плагин enrol_dof в списке разрешенных к использованию в курсе
            if ( $instance->enrol == $dofinstancename )
            {
                return $instance;
            }
        }
        // просмотрели все плагины, но не нашли нашего - значит он не доступен в курсе
        return false;
    }
    
    /**
     * Возвращает объект для управления подпиской указанного типа. Если способа записи в курсе нет, создает его.
     * @param string $plugin тип плагина записи на курс
     * @return NULL|ama_course_enrol_[dof,manual,...]
     */
    public function create_instance($plugin = 'manual')
    {
        $class = 'ama_course_enrol_' . $plugin;
        if( ! class_exists($class) )
        {// Класс работы с модулем не найден
            return null;
        }
        
        $instancemanager = new $class($this->instance);
        if( empty($instancemanager->get_instance($this->courseid)) )
        {
            $instancemanager->add_instance($this->dof->modlib('ama')->course($this->courseid)->get());
            return $instancemanager;
        } else 
        {
            return $instancemanager;
        }
    }
    
    /**
     * Отписывает пользователя из курса
     * @param int $userid идентификатор пользователя
     */
    public function unenrol_user($userid)
    {
        if( ! empty($this->instance) )
        {
            $plugin = $this->instance->enrol;
            $class = 'ama_course_enrol_' . $plugin;
            if( class_exists($class) )
            {
                $instancemanager = new $class($this->instance);
                $instancemanager->unenrol_user($userid);
            } else 
            {// Класс работы с модулем не найден
                $this->$plugin->unenrol_user($this->instance, $userid);
            }
        }
    }
}