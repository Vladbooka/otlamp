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

/**
 * Класс области доступа, с которым 
 * 
 * @package    block_dof
 * @subpackage sync_authcontrol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_sync_authcontrol_access_area 
{
    /**
     * Идентификатор модуля элемента/курса
     * 
     * @var int
     */
    protected $objid = null;
    
    /**
     * Опционально хранится объект
     * 
     * @var cm_info|stdClass
     */
    protected $obj = null;
    
    /**
     * Область доступа
     * 
     * @var string
     */
    protected $area = null;
    
    /**
     * Доступные области
     * 
     * @var array
     */
    protected $available_areas = ['course', 'coursemodule'];
    
    /**
     * Проверка готовности объекта области доступа
     * 
     * @return bool 
     */
    public function is_formed()
    {
        if ( empty($this->area) || empty($this->objid) )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Конвертация грейд итема в area и objid
     * 
     * @param number $objid
     */
    public function convert_gradeitem($objid)
    {
        global $DOF;
        $gradeitem = $DOF->modlib('ama')->grade_item($objid);
        if ( empty($gradeitem) )
        {
            return;
        }
        $gradeitemobj = $gradeitem->get();
        if ( $gradeitemobj->is_course_item())
        {
            // курс
            $this->set_area('course');
            $this->set_objid($gradeitemobj->courseid);
            $this->obj = get_course($gradeitemobj->courseid);
        } elseif ( $gradeitemobj->is_external_item() )
        {
            // модуль
            $cm = get_course_and_cm_from_instance($gradeitemobj->iteminstance, $gradeitemobj->itemmodule, $gradeitemobj->courseid);
            if ( empty($cm) )
            {
                return;
            }
            $cm = $cm[1];
            $this->set_area('coursemodule');
            $this->set_objid($cm->id);
            $this->obj = $cm;
        }
    }
    
    /**
     * @return string
     */
    public function get_area()
    {
        return $this->area;
    }

    /**
     * @param string $area
     */
    public function set_area($area)
    {
        if ( ! in_array($area, $this->available_areas) )
        {
            throw new dof_exception('invalid_access_area', 'sync_authcontrol');
        }
        $this->area = $area;
    }
    
    /**
     * @param number $objid
     */
    public function set_objid($objid)
    {
        $this->objid = $objid;
    }
    
    /**
     * @return number
     */
    public function get_objid()
    {
        return $this->objid;
    }
    
    /**
     * @return cm_info|stdClass
     */
    public function get_obj()
    {
        return $this->obj;
    }
}
