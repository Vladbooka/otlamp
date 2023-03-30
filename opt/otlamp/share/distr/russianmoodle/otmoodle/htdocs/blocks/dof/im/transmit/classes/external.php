<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Вебсервис трансмита
 *
 * @package    im
 * @subpackage transmit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(realpath(__FILE__))."/../lib.php");
require_once($CFG->libdir . '/weblib.php');

class dof_external_api_plugin extends dof_external_api_plugin_base
{
    /**
     * Выполнить действие над пакетом настроек
     *
     * @param int $packid
     * @param string $action
     *
     * @return bool
     */
    public static function do_pack_action($packid, $action)
    {
        global $DOF;
        
        switch($action)
        {
            case 'execute':
                // TODO Execute me
                if ( $DOF->is_access('admin') )
                {
                    $packrecord = $DOF->storage('transmitpacks')->get($packid);
                    
                    if (!empty($packrecord))
                    {
                        /**
                         * @var \dof_modlib_transmit_pack $pack
                         */
                        $pack = $DOF->modlib('transmit')->get_pack($packrecord);
                        // запуск процесса синхронизации
                        $DOF->modlib('transmit')->transmit_from_pack($pack);
                    }
                    
                    return true;
                } else
                {
                    return false;
                }
                break;
            case 'activate':
                $status = 'active';
                if ( $DOF->is_access('admin') )
                {
                    return $DOF->workflow('transmitpacks')->change($packid, $status);
                } else
                {
                    return false;
                }
            case 'suspend':
                $status = 'suspended';
                if ( $DOF->is_access('admin') )
                {
                        return $DOF->workflow('transmitpacks')->change($packid, $status);
                } else
                {
                    return false;
                }
            case 'delete':
                if ( $DOF->is_access('admin') )
                {
                    $status = 'deleted';
                    return $DOF->workflow('transmitpacks')->change($packid, $status);
                } else
                {
                    return false;
                }
            default:
                return false;
        }
        
        return false;
    }
    
    
    public static function get_pack($packid)
    {
        global $DOF;
        
        $html = '';
        
        $packrecord = $DOF->storage('transmitpacks')->get_record([
            'id' => $packid,
            'status' => array_keys($DOF->workflow('transmitpacks')->get_meta_list('real'))
        ]);
        if( ! empty($packrecord))
        {
            $html .= $DOF->im('transmit')->display_pack($packrecord, ['returnhtml' => true]);
        }
        
        return $html;
    }
    
    public static function set_packs_order($packids)
    {
        global $DOF;
        $updateresult = true;
        
        // TODO throw exception on access denied
        
        if ( $DOF->is_access('admin') && !empty($packids) && is_array($packids))
        {
            foreach($packids as $sortorder=>$packid)
            {
                $packrecord = new stdClass();
                $packrecord->id = $packid;
                $packrecord->sortorder = $sortorder;
                $updateresult = $updateresult && $DOF->storage('transmitpacks')->update($packrecord);
            }
        }
        
        return $updateresult;
    }
}