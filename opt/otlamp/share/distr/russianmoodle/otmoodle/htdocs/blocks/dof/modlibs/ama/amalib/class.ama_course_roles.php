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

/** Класс для работы с ролями курса
 * @todo предусмотреть возможность подписки на курс используя не только плагин enrol_dof
 */
class ama_course_roles
{
    protected $dof;
    
    //id курса, в котором должна быть определена роль
    protected $courseid = 0;
    protected $roleid = 0;

    /** конструктор класса - создает объект от текущего класса
     * @access public
     * @param int $courseid - id курса, с которым собираются работать
     * @param int $roleid[optional] - id роли (в таблице mdl_role) которая будет назначена пользователю 
     *                      при записи на курс. 
     *                      Если роль не указана - то она берется из настроек плагина enrol_dof
     * @return null
     */
    public function __construct($courseid, $roleid = false, $type = 'student')
    {
        global $DOF;
        $this->dof = $DOF;
        $this->courseid = (int)$courseid;

        // Выбираем роль
        if( $roleid === false )
        {// Берем из настроек плагина подписки роль по умолчанию в зависимости от типа
            $this->roleid = $this->get_dof_role($type);
        } else
        {// Нам уже передали роль
            $this->roleid = (int)$roleid;
        }
    }
    
    /**
     * Получить идентификатор роли
     * @return number
     */
    public function get_id()
    {
        return $this->roleid;
    }
    
    /**
     * Назначение роли
     * @param int $userid идентификатор пользователя
     * @return int иденификатор назначения
     */
    public function role_assign($userid)
    {
        $context = context_course::instance($this->courseid);
        return role_assign($this->roleid, $userid, $context->id);
    }
    
    /**
     * Снятие назначения роли
     * @param int $userid идентификатор пользователя
     */
    public function role_unassign($userid)
    {
        $context = context_course::instance($this->courseid);
        role_unassign($this->roleid, $userid, $context->id);
    }
    
    /**
     * Получение роли записи через enrol_dof
     * @param string $type student|teacher|redactor
     * @return int идентификатор роли
     */
    protected function get_dof_role($type = 'student')
    {
        $enrol = enrol_get_plugin('dof');
        switch( $type )
        {
            case 'student':
                return (int)$enrol->get_config('roleid');
                break;
            case 'teacher':
                return (int)$enrol->get_config('teacherroleid');
                break;
            case 'redactor':
                return (int)$enrol->get_config('editingroleid');
                break;
            default:
                return (int)$enrol->get_config('roleid');
                break;
        }
    }

    /** Возвращает список ролей, определенных в текущем  контексте 
     * @access public
     * @return array массив ролей
     */
    public function roles()
    {
        
    }

    /** Возвращает список пользователей, которые 
     * имеют указанную роль в текущем контексте
     * @access public
     * @param int $roleid - id роли
     * @return array - массив id пользователей
     */
    public function assigned($roleid)
    {// Оставим этот код дня потомков:)
        $returnvalue = array();
        return (array) $returnvalue;
    }
}
