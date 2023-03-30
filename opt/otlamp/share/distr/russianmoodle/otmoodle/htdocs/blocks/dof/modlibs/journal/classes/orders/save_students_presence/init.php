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

/**
 * Класс для создания приказов о выставлении текущей посещаемости
 */

class dof_modlibs_order_save_students_presence extends dof_storage_orders_baseorder
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
        return 'save_students_presence';
    }
    
    protected function execute_actions($order)
    {
        // Получена посещаемость из приказа
        if ( empty($order->data->schpresences) || empty($order->data->scheventid) )
        {// Не получена посещаемость из приказа
            return false;
        }
        
        // Сохранение посещаемости
        $savepresenceresult = $this->dof->storage('schpresences')->save_students_presence(
            $order->data->schpresences, 
            $order->id,
            $order->departmentid
        );
        
        return $savepresenceresult;
    }
}
?>