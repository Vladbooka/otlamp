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
 * Базовый приказ смену доступа в СДО
 * 
 * @package    block_dof
 * @subpackage modlib_journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dof_modlibs_order_base_access extends dof_storage_orders_baseorder
{
    /**
     * Новый статус
     * 
     * @return bool
     */
    abstract public function get_newstatus();
    
    /**
     * {@inheritDoc}
     * @see dof_storage_orders_baseorder::plugintype()
     */
    public function plugintype()
    {
        return 'modlib';
    }
    
    /**
     * {@inheritDoc}
     * @see dof_storage_orders_baseorder::plugincode()
     */
    public function plugincode()
    {
        return 'journal';
    }
    
    /** 
     * Метод исполнения действий
     * 
     * @param object $order - объект из таблицы orders.
     * 
     * @return bool
     */
    protected function execute_actions($order)
    {
        if ( ! $this->dof->plugin_exists('sync', 'authcontrol') )
        {
            return false;
        }
        if ( empty($order->data->area) ||
                empty($order->data->objid) )
        {
            return false;
        }
        if ( empty($order->data->cpasseds) )
        {
            // открывать некому, успешно закроем приказ
            return true;
        }
        
        // формируем массив айдишников пользователей Moodle, кому необходимо сменить статус доступа
        $usersids = [];
        foreach ( $order->data->cpasseds as $cpassed )
        {
            do 
            {
                $person = $this->dof->storage('persons')->get_record(['id' => $cpassed->studentid]);
                if ( empty($person->sync2moodle) || empty($person->mdluser) )
                {
                    break;
                }
                if ( ! $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
                {
                    break;
                }
                $usersids[] = $person->mdluser;
            } while (false);
        }
        if ( empty($usersids) )
        {
            // пустой список пользователей, вернем положительный ответ
            return true;
        }
        
        // создаем объект области доступа
        $acessarea = new dof_sync_authcontrol_access_area();
        if ( $order->data->area == 'gradeitem' )
        {
            $acessarea->convert_gradeitem($order->data->objid);
            if ( $this->get_newstatus() )
            {
                // есть модуль элемента - тест
                // выдадим новую попытку при необходимости
                $cm = $acessarea->get_obj();
                if ( ! empty($cm->modname) && $cm->modname == 'quiz' )
                {
                    $amacourse = $this->dof->modlib('ama')->course($cm->course);
                    $amacourseinstance = $amacourse->instance($cm->id);
                    $amaquizinstance = $amacourseinstance->get_manager();
                    
                    // добавление попыток
                    $amaquizinstance->add_new_attempt($usersids);
                }
            }
        } else
        {
            $acessarea->set_area($order->data->area);
            $acessarea->set_objid($order->data->objid);
        }
        
        return $this->dof->sync('authcontrol')->switch_access($usersids, $acessarea, $this->get_newstatus());
    }
}
