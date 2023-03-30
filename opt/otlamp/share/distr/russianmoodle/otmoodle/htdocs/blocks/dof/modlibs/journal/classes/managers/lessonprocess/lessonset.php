<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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

/**
 * Менеджер учебного процесса. Набор уроков.
 * 
 * @package    modlib
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_lessonset
{
    /**
     * Объект контроллера Деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof = null;
    
    /**
     * Массив занятий
     * 
     * @var dof_lesson[]
     */
    protected $lessons = [];
    
    /**
     * Массив подписок
     * 
     * @var array
     */
    protected $cpasseds = [];
    
    /**
     * Конструктор
     *
     * @param dof_control - Объект контроллера Деканата для доступа к общим методам
     * @param dof_lesson[] $lesson - Массив занятий
     */
    public function __construct(dof_control $dof, $lessons)
    {
        $this->dof  = $dof;
        $tmplessons = $lessons;
        // Сортировка по дате
        uasort($tmplessons, function($lessona, $lessonb){
            $a = $lessona->get_startdate();
            $b = $lessonb->get_startdate();
            if ($a == $b) {
                return 0;
            }

            if( is_null($a) )
            {
                $a = 0;
            }

            if( is_null($b) )
            {
                $b = 0;
            }
            
            return ($a < $b) ? -1 : 1;
        });
        $indexnum = 0;
        foreach($tmplessons as $k=>$lesson)
        {
            $indexnum++;
            $tmplessons[$k]->set_indexnum($indexnum);
            $this->lessons[$indexnum] = $tmplessons[$k];
        }
    }
    
    /**
     * Получить количество занятий
     * 
     * @return number
     */
    public function get_count()
    {
        return count($this->lessons);
    }
    
    /**
     * Получить список занятий, сгруппированных по дням
     * 
     * @return array - Массив в формате [year][month][day][]
     */
    public function group_by_dates($desceding=false)
    {
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
    
        $dates = [];
        if($desceding)
        {
            $lessons = array_reverse($this->lessons);
        } else 
        {
            $lessons = $this->lessons;
        }
        foreach ( $lessons as &$lesson )
        {
            // Получение года
            $year = dof_userdate($lesson->get_startdate(), '%Y', $usertimezone);
            // Получение месяца
            $month = dof_userdate($lesson->get_startdate(), '%m', $usertimezone);
            // Получение дня
            $day = dof_userdate($lesson->get_startdate(), '%d', $usertimezone);
    
            $dates[$year][$month][$day][] = &$lesson;
        }
    
        return $dates;
    }
    
    /**
     * Получить полный список подписок на дисциплины для набора с сортировкой по фамилии
     *
     * @return array - Массив подписок, отсортированный по указанному полю
     */
    public function get_cpasseds_fullset_lastname($sortdir = 'asc')
    {
        // Подписки на учебный процесс
        $cpassedsdata = $this->cpasseds;
        
        // Функция прямой сортировки подписок по фамилии
        $sortcallbackasc = function($a, $b)
        {
            return strcmp($a['person']->lastname, $b['person']->lastname);
        };
        // Функция обратной сортировки подписок по фамилии
        $sortcallbackdesc = function($a, $b)
        {
            return strcmp($a['person']->lastname, $b['person']->lastname) * -1;
        };
        
        // Сортировка по фамилии
        if ( $sortdir == 'desc' )
        {
            uasort($cpassedsdata, $sortcallbackdesc);
        } else 
        {
            uasort($cpassedsdata, $sortcallbackasc);
        }
        // Генерация результата
        foreach ( $cpassedsdata as &$cpassed )
        {
            $cpassed = $cpassed['cpassed'];
        }
        return $cpassedsdata;
    }
    
    /**
     * Добавить подписки на дисциплины к текущему списку
     * 
     * @return void
     */
    public function merge_cpasseds()
    {
        foreach ( $this->lessons as $lesson )
        {
            $lesson->add_listeners($this->cpasseds);
        }
    }
    
    /**
     * Получение занятий
     *
     * @return array
     */
    public function get_lessons()
    {
        return $this->lessons;
    }
    
    /**
     * Добавить подписки на дисциплины к текущему списку
     *
     * @param stdClass[] $cpasseds - Список подписок на на учебный процесс
     *
     * @return void
     */
    public function add_cpasseds($cpasseds = [])
    {
        // Дополнительная информация о слушателях
        foreach ( $cpasseds as $cpassedid => $listener )
        {
            $personid = $listener->studentid;
            $listener = ['cpassed' => $listener];
            $listener['person'] = $this->dof->storage('persons')->get($personid);
            
            $this->cpasseds[$cpassedid] = $listener;
        }
    }
}