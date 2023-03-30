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

// подключение базового класса
require_once(dirname(realpath(__FILE__)).'/class.ama_base.php');

//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5');//если ниже php5, то кончаем работу
}

/**
 * Класс для работы с категориями Moodle (через modlib/ama)
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ama_category extends ama_base
{
    /**
     * Объект категории
     * 
     * @var coursecat
     */
    protected $catobj = null;
    
	/** 
	 * Проверка существования категории
	 * 
	 * @return bool
	 */
	public function is_exists($id = null)
	{
	    global $DB;
		if ( is_null($id) )
		{
			$this->require_real();
			$id = $this->get_id();
		}
        if ( ama_utils_is_intstring($id) )
        {// переланный id категории является числом, все нормально
            $id = intval($id);
        }else
        {// переданный id не является числом - вернем всесто него 0
            $id = 0;
        }
        
		return $DB->record_exists('course_categories', ['id' => intval($id)]);
	}
	
    /** 
     * Шаблон категории
     * 
     * @param stdClass $data - массив значений, которые переопределяют соответствующие параметры по умолчанию 
     * 
     * @return stdClass
     */
    public function template($data = NULL)
    {
		$category = new stdClass();
		
		// обязательное поле при создании категории
		$category->name = 'Новая категория';
		
		if ( ! is_null($data) )
		{
			foreach ( $data as $key => $val )
			{
			    $category->$key = $val;
			}
		}
		
		return $category;
    }

    /**
     * Возвращает информацию о категории из БД
     *
     * @return stdClass
     */
    public function get()
    {
        return $this->get_coursecat()->get_db_record();
    }
    
    /**
     * Возвращает объект coursecat
     *
     * @return coursecat
     */
    public function get_coursecat()
    {
        if ( is_null($this->catobj) )
        {
            global $DB;
            $this->require_real();
            $this->catobj = \core_course_category::get($this->get_id());
        }
        
        return $this->catobj;
    }
    
    /** 
     * Возвращение отформатированного названия категории
     * 
     * @return string
     */
    public function get_formated_name($options = [])
    {
        return $this->get_coursecat()->get_formatted_name($options);
    }
    
    /**
     * Получение контекста
     *
     * @return context_coursecat
     */
    public function get_context()
    {
        return $this->get_coursecat()->get_context();
    }
    
    /**
     * Получение курсов категории
     *
     * @param array $options
     * 
     * @return stdClass[]
     */
    public function get_courses($options = [])
    {
        return $this->get_coursecat()->get_courses($options);
    }
    
    /**
     * Получение количества курсов в категории
     *
     * @param array $options
     *
     * @return int
     */
    public function get_courses_count($options = [])
    {
        return $this->get_coursecat()->get_courses_count($options);
    }
    
    /** 
     * Возвращение ссылки на категорию
     * 
     * @return string
     */
    public function get_link()
    {
        return $CFG->wwwroot.'/course/view.php?categoryid='.$this->get_id();
    }
    
    /**
     * Перенос категории
     *
     * @param int - $newparentid
     *
     * @return bool
     */
    public function move($newparentid)
    {
        $this->require_real();
        
        // получение объекта текущей категории
        $cat = \core_course_category::get($this->get_id());
        
        // получение объекта родительской категории
        $newparentcat = \core_course_category::get($newparentid);
        
        // получение родительских категорий
        $notavailablecats = $newparentcat->get_parents();
        
        if ( ($this->id == $newparentid)|| 
                in_array($this->id, $notavailablecats)) 
        {
            // нельзя переносить категорию в дочерние ему категории
            return false;
        }
        
        // перенос категории и возврат результата
        return $cat->change_parent($newparentid);
    }
    
    /**
     * Создании категории
     *
     * @param stdClass $obj - параметры объекта или null для параметров по умолчанию
     *
     * @return int | false
     */
    public function create($obj = null)
    {
        global $DB;
        
        // проверка по шаблону
        $category = $this->template($obj);
        
        // создание категории
        $rec = \core_course_category::create($category);//записываем его в БД
        if ( is_object($rec) )
        {
            return $rec->id;
        } else
        {
            return false;
        }
    }
    
    /** 
     * Обновление категории
     * 
     * @param stdClass
     * 
     * @return void
     */
    public function update($obj, $replace = false)
    {
		$this->require_real();
		
		// получение объекта категории
		$cat = \core_course_category::get($this->get_id());
		
		// обновление категории и возврат результата
		return $cat->update($obj);
    }

    /** 
     * Удаление категории
     * 
     * @return bool
     */
    public function delete()
    {
        global $DOF;
        
		// фиксируем в логах
		$DOF->add_to_log('modlib', 'ama', 'delete_category', "/course/index.php?categoryid={$this->get_id()}", "{$this->get_formated_name()} ID {$this->get_id()})");
		
		// проверка существования
		$this->require_real();
		
		// удаление категории
		return $this->get_coursecat()->delete_full(false);
    }
    
    /**
     * Поиск категорий по названию
     * 
     * @param string $name - название категроии, которую требуется найти
     * @param null|int $oneofparents - идентификатор категории, являющейся одной из родительских к искомой
     * 
     * @return array - массив найденных записей
     */
    public function search_by_name($name=null, $oneofparents=null)
    {
        global $DB;
        
        $conditions = ['1=1'];
        $params = [];
        
        if (!is_null($name))
        {
        // условия выборки - по названию
            $conditions[] = 'name = :catname';
        // параметры для подстановки в условия
            $params['catname'] = $name;
        }
        
        if( ! is_null($oneofparents) )
        {// требуется поиск внутри указанной категории
            $conditions[] = $DB->sql_like('path', ':catpath', false);
            $params['catpath'] = "%/".(int)$oneofparents."/%";
        }
        
        // Получение категорий по условиям
        $categoryrecords = $DB->get_records_select(
            'course_categories',
            implode(' AND ', $conditions),
            $params
        );
        if( ! empty($categoryrecords) )
        {// Найдены категории - вернем массив
            return $categoryrecords;
        } else 
        {// Категории не найдены - пустой массив
            return [];
        }
    }
}

