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

require_once(dirname(realpath(__FILE__)) . "/../../../../../config.php");

global $CFG;

//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5');//если ниже php5, то кончаем работу
}
// Подключаем базовый класс
require_once(dirname(realpath(__FILE__)).'/class.ama_base.php');

/** Класс для работы с курсом
 * @access public
 */
class ama_course_completion extends ama_base
{
	/** Проверяет существование записи о завершении курса
	 * Проверяет существование в таблице записи с указанным id 
	 * и возвращает true или false
	 * @return bool
	 */
	public function is_exists($id=null)
	{
	    global $DB;
		if (is_null($id))
		{
			$this->require_real();
			$id = $this->get_id();
		}
        if ( ama_utils_is_intstring($id) )
        {// переланный id курса является числом, все нормально
            $id = intval($id);
        }else
        {// переданный id не является числом - вернем всесто него 0
            $id = 0;
        }
        
		return $DB->record_exists('course_completions', array('id' => intval($id)));
	}
		
	/** НЕ РЕАЛИЗОВАН! Предназначен для создания объекта
	 * @param mixed $obj - параметры объекта или null для параметров по умолчанию
	 * @return mixed
	 */
	public function create($obj = null)
	{
	    return false;
	}
	
	/** НЕ РЕАЛИЗОВАН! Предназначен для получения шаблона нового объекта
	 * @param mixed $obj - параметры объекта или null для параметров по умолчанию
	 * @return object
	 */
	public function template($obj = null)
	{
	    return false;
	}
		
    /** Возвращает информацию о завершении курса из БД
     * @access public
     * @return object массив типа параметр=>значение
     */
    public function get()
    {
        global $DB;
		$this->require_real();
        return $DB->get_record('course_completions', array('id' => $this->get_id()));
    }
    
    /**  НЕ РЕАЛИЗОВАН! Предназначен для обновления информации об объекте в БД
     * @access public
     * @param object $obj - объект с информацией
     * @param bool $replace - false - надо обновить запись курса
     * true - записать новую информацию в курс
     * @return mixed id объекта или false
     */
    public function update($obj, $replace = false)
    {
        return false;
    }
    
    /**  НЕ РЕАЛИЗОВАН! Предназначен для удаления объекта из БД
     * @access public
     * @return bool true - удаление прошло успешно
     * false в противном случае
     */
    public function delete()
    {
        return false;
    }
    
    
    /**
     * Получение записей о завершении курсов
     * 
     * @param array|null $userids
     * @param array|null $courseids - массив идентификаторов курсов для отсечения выборки, либо null для выборки по всем курсам
     * @param int|null $datestart - начальная отсечка времени для ограничения выборки по дате завершения
     * @param int|null $dateend - конечная отсечка времени для ограничения выборки по дате завершения
     * @return array - массим объектов, записей БД о завершении курса
     */
    public function get_course_completions($courseids=null, $userids = null, $datestart=null, $dateend=null)
    {
        global $DB;
        
        $where = ['timecompleted IS NOT NULL'];
        $params = [];
        
        if( ! is_null($datestart) )
        {
            $where[] = 'timecompleted >= :datestart';
            $params['datestart'] = (int)$datestart;
        }
        
        if( ! is_null($dateend) )
        {
            $where[] = 'timecompleted <= :dateend';
            $params['dateend'] = (int)$dateend;
        } else 
        {
            $where[] = 'timecompleted IS NOT NULL';
        }
        
        if( ! is_null($courseids) && is_array($courseids))
        {
            $where[] = 'course IN (:courseids)';
            $params['courseids'] = implode(',',$courseids);
        }
        if( ! is_null($userids) && is_array($userids))
        {
            $where[] = 'userid IN (:userids)';
            $params['userids'] = implode(',',$userids);
        }
        
        $sql = "SELECT *
            FROM {course_completions} 
            WHERE ".implode(' AND ',$where);
        return $DB->get_records_sql($sql, $params);
    }
}

