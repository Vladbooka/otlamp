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

//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5');
}

//подключаем библиотеку для работы с экземплярами модулей
require_once('class.ama_course_instance.php');

/** Класс для работы с экземплярами модулей типа ресурс
 * @access public
 */
class ama_course_instance_subcourse extends ama_course_instance
{
    
    public function __construct($cm)
    {
        $this->cm = $cm;
        if( isset($this->cm->id) )
        {
            $this->context = context_module::instance($this->cm->id);
        } else
        {
            $this->context = null;
        }
        if( empty($this->courseid) )
        {
            $this->courseid = $this->cm->course;
        }
    }
    
    /** Вернуть тип задания из таблицы modules
     *
     * @return string
     */
    public function get_instance_name()
    {
        return 'subcourse';
    }
    
    public function get_count_instances()
    {
        // Получаем все сабкурсы курса
        $subcourses = $this->get_course_instances();
        return count($subcourses);
    }
}

?>