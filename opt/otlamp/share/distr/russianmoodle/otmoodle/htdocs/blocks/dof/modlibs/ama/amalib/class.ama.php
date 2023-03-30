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

// Подключаем Утилиты
require_once(dirname(realpath(__FILE__)) . '/utils.php');
//Подключаем класс для работы с категориями
require_once(dirname(realpath(__FILE__)) . '/class.ama_category.php');
//Подключаем класс для работы с курсами
require_once(dirname(realpath(__FILE__)) . '/class.ama_course.php');
//Подключаем класс для работы с записями о завршении курса
require_once(dirname(realpath(__FILE__)) . '/class.ama_course_completion.php');
//Подключаем класс для работы с пользователями
require_once(dirname(realpath(__FILE__)) . '/class.ama_user.php');
//Подключаем класс для работы с оценивамыми элементами
require_once(dirname(realpath(__FILE__)) . '/class.ama_grade_item.php');

/** 
 * Основной класс, для работы с библиотекой AMA(Alternative Moodle Api)
 */
class ama
{
    /** 
     * Конструктор класса
     *
     * @access public
     */
    public function __construct()
    {
    }

    /**
     * Возвращает объект для работы с категорией
     *
     * @param int $id - id категории, либо NULL (пустая категория)
     * @return ama_category объект для работы с категорией
     * @access public
     */
    public function category($id = NULL)
    {
        return new ama_category($id);
    }
    
    /** 
     * Возвращает объект для работы с курсом
     *
     * @param int $id - id курса либо NULL (пустой курс)
     * @return ama_course объект для работы с курсом
     * @access public
     */
    public function course($id = NULL)
    {
        return new ama_course($id);
    }
    
    /**
     * Возвращает объект для работы с записью о завершении курса
     *
     * @param int|null|false $id - id записи о завершении курса
     *                             либо NULL (новая запись),
     *                             либо false (для использования методов не требующих наличия реального объекта)
     * @return ama_course_completion объект для работы с записями о завершении курса
     * @access public
     */
    public function course_completion($id = NULL)
    {
        return new ama_course_completion($id);
    }
    
    /** 
     * Получить объект для работы с курсом
     * 
     * @example если передавать NULL, то создается пользователь. Если создать не нужно, передавать FALSE
     * @access public
     * @param  int $id [optional] - id пользователя
     * 
     * @return ama_user
     */
    public function user($id = NULL)
    {
        return new ama_user($id);
    }
    
    /** Получить объект для работы с оцениваемым элементом
     *
     * @access public
     * @return ama_grade_item
     * @param  int $id [optional] - id пользователя
     */
    public function grade_item($id = NULL)
    {
        return new ama_grade_item($id);
    }
}
?>