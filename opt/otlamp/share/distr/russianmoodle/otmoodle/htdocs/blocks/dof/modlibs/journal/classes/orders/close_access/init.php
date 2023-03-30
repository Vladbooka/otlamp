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

/**
 * Приказ на закрытие доступа в СДО
 * 
 * @package    block_dof
 * @subpackage modlib_journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlibs_order_close_access extends dof_modlibs_order_base_access
{
    /**
     * {@inheritDoc}
     * @see dof_storage_orders_baseorder::code()
     */
    public function code()
    {
        return 'close_access';
    }
    
    /**
     * {@inheritDoc}
     * @see dof_modlibs_order_base_access::get_newstatus()
     */
    public function get_newstatus()
    {
        return false;
    }
}
