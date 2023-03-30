<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','orders','/baseorder.php'));

class dof_modlibs_order_remove_student_lesson_data extends dof_storage_orders_baseorder
{
    public function plugintype()
    {
        return 'modlib';
    }
    
    public function plugincode()
    {
        return 'journal';
    }
    
    public function code()
    {
        return 'remove_student_lesson_data';
    }
    
    protected function execute_actions($order)
    {
        // Данные отчета
        if ( empty($order->data->listener) || 
                empty($order->data->listener['person']) || 
                empty($order->data->listener['cpassed']) )
        {
            // Недостаточно данных для исполнения приказа
            return false;
        }
        
        $order->data->orderid = $order->id;
        
        $result = true;
        if ( ! empty($order->data->listener) && ! empty($order->data->listener['person']) && ! empty($order->data->listener['cpassed']) )
        {
            // Удаление посещаемости
            if ( ! empty($order->data->scheventid) )
            {
                if ( ! empty($order->data->quiteschpresencedelete) )
                {
                    // Мягкое удаление, переведем в дефолтный вид
                    $result = $this->dof->storage('schpresences')->save_present_student(
                            (object)[
                                'personid' => $order->data->listener['person']->id,
                                'eventid' => $order->data->scheventid,
                                'present' => null,
                                'orderid' => null,
                                'mdlevent' => null,
                                'reasonid' => null
                            ]) && $result;
                } else 
                {
                    $schpresent = $this->dof->storage('schpresences')->get_record(['eventid' => $order->data->scheventid, 'personid' => $order->data->listener['person']->id]);
                    if ( ! empty($schpresent) )
                    {
                        // Полное удаление информации о посещаемости
                        $result = $this->dof->storage('schpresences')->delete($schpresent->id) && $result;
                    }
                }
            }
            
            // Удаление оценки
            if ( ! empty($order->data->planid) )
            {
                $gradedata = $this->dof->storage('cpgrades')->get_grade_student_cpassed($order->data->listener['cpassed']->id, $order->data->planid);
                if ( ! empty($gradedata) )
                {
                    // Удаление оценки
                    $result = $this->dof->storage('cpgrades')->delete($gradedata->id) && $result;
                }
            }
        }
        
        return $result;
    }
}

?>