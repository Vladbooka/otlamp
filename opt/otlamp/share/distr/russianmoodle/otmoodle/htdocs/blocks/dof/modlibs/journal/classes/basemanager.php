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
 * Менеджер учебного процесса. Базовый класс подсистем.
 * 
 * @package    modlib
 * @subpackage journal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_journal_basemanager
{
    /**
     * Объект деканата для доступа к общим методам
     *
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Конструктор
     *
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Получить приказ
     *
     * @param string $ordername - название типа приказа
     *
     * @return object|false
     */
    public function order($ordername = '')
    {
        if ( empty($ordername) )
        {
            return false;
        }
        
        // Подключение дополнительных классов
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/save_students_presence/init.php'));
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/save_students_grades/init.php'));
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/remove_student_lesson_data/init.php'));
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/base_access.php'));
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/open_access/init.php'));
        require_once($this->dof->plugin_path('modlib','journal','/classes/orders/close_access/init.php'));
        
        switch ( $ordername )
        {
            case 'save_students_presence':
                $order = 'dof_modlibs_order_' . $ordername;
                $obj = new $order($this->dof);
                return $obj;
                
            case 'save_students_grades':
                $order = 'dof_modlibs_order_' . $ordername;
                $obj = new $order($this->dof);
                return $obj;
            
            case 'remove_student_lesson_data':
                $order = 'dof_modlibs_order_' . $ordername;
                $obj = new $order($this->dof);
                return $obj;
                
            case 'open_access':
                $order = 'dof_modlibs_order_' . $ordername;
                $obj = new $order($this->dof);
                return $obj;
                
            case 'close_access':
                $order = 'dof_modlibs_order_' . $ordername;
                $obj = new $order($this->dof);
                return $obj;
                
            default:
                return false;
        }

    }
    
    /**
     * Список обрабатываемых плагином событий
     *
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [];
    }
    
    /** 
     * Обработать событие
     * 
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        return false;
    }
}