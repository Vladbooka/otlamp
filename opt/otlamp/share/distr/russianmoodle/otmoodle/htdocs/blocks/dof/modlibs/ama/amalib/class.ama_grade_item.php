<?php
use core\session\exception;

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

require_once(dirname(realpath(__FILE__)) . "/../../../../../config.php");

global $CFG;

// Подключаем базовый класс
require_once(dirname(realpath(__FILE__)).'/class.ama_base.php');
require_once $CFG->libdir . '/grade/grade_item.php';
require_once $CFG->libdir . '/grade/constants.php';

/** Класс для работы с курсом
 * @access public
 */
class ama_grade_item extends ama_base 
{
    /**
     * Получение грейд итемов курса
     * 
     * @param int  $courseid
     * 
     * @return array
     */
    public function get_course_grade_items($courseid)
    {
        $gradeitems = grade_item::fetch_all(['courseid' => $courseid]);
        return $gradeitems;
    }
    
    /** 
     * Проверяет существование объекта
     * Проверяет существование в таблице записи с указанным id
     * и возвращает true или false
     * 
     * @return bool
     */
    public function is_exists($id = null)
    {
        try 
        {
            grade_item::fetch(['id' => $id]);
        } catch ( Exception $e )
        {
            return false;
        }
        return true;
    }
    
    /** Возвращает шаблон нового объекта
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию
     * @return object
     */
    public function template($obj = null)
    {
        return true;
    }
    
    /** Обновляет информацию об объекте в БД
     * @access public
     * @param object $obj - объект с информацией
     * @param bool $replace - false - надо обновить запись курс
     * true - записать новую информацию в курс
     * @return mixed id объекта или false
     */
    public function update($obj, $replace = false)
    {
        return true;
    }
    
    /** Возвращает информацию об объекте из БД
     * @access public
     * @return grade_item объект типа параметр=>значение
     */
    public function get()
    {
        $this->require_real();
        $gradeitem = grade_item::fetch(['id' => $this->get_id()]);
        return $gradeitem;
    }
    
    /** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию
     * @return mixed
     */
    public function create($obj = null)
    {
        return true;
    }
    
    /** Удаляет объект из БД
     * @access public
     * @return bool true - удаление прошло успешно
     * false в противном случае
     */
    public function delete()
    {
        return true;
    }
    
    /**
     * Получение объекта grade_grade юзера
     * 
     * @param int $userid
     * 
     * @return grade_grade
     */
    public function fetch_user_grade($userid)
    {
        return new grade_grade(['itemid' => $this->get()->id, 'userid' => $userid]);
    }
    
    /**
     * Получить ссылку на журнал оценок в одиночном виде по текущему грейд итему
     * 
     * @return moodle_url
     */
    public function get_link_to_gradebook()
    {
        $gradeitem = $this->get();
        return new moodle_url('/grade/report/singleview/index.php',
                [
                    'id' => $gradeitem->courseid,
                    'item' => 'grade',
                    'itemid' => $gradeitem->id
                ]);
    }
    
    /**
     * Получить ссылку на оценки 
     *
     * @return string
     */
    public function get_link_to_element_gradebook()
    {
        global $DOF;
        $gradeitem = $this->get();
        if ( $gradeitem->is_external_item() && $gradeitem->itemmodule == 'quiz' )
        {
            $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance, $gradeitem->courseid);
            return dof_html_writer::link(new moodle_url('/mod/quiz/report.php', [
                'id' => $cm->id,
                'mode' => 'overview'
            ]), dof_html_writer::div($DOF->get_string('quiz_report_attempts', 'ama', null, 'modlib')), ['target' => '_blank']);
        }
        
        return '';
    }
    
    /**
     * Получить ссылку на оценки
     *
     * @return moodle_url
     */
    public function get_link_to_element_view() 
    {
        $gradeitem = $this->get();
        $cm = get_coursemodule_from_instance($gradeitem->itemmodule, $gradeitem->iteminstance, $gradeitem->courseid);
        return new moodle_url("/mod/{$gradeitem->itemmodule}/view.php", ['id' => $cm->id]);
    }
}
