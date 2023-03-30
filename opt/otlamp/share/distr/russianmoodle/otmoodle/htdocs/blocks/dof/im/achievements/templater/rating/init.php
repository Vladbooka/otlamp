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
 * Класс шаблонизатора xls для отчета по студентам
*
* @package    im
* @subpackage journal
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

global $DOF;
require_once($DOF->plugin_path('modlib','templater','/formats/xls/init.php'));
require_once($DOF->plugin_path('modlib','templater','/package.php'));

class dof_modlib_templater_format_achievements_rating_xls extends dof_modlib_templater_format_xls
{
    /**
     * Указание поля отчета, в котором содержатся данные для экспорта
     * 
     * @return mixed string - Имя поля или bool false
     */
    protected function get_field_name()
    {
        return 'table';
    }
}

class dof_im_achievements_templater_rating extends dof_modlib_templater_package
{
	public function create_format($type, $options = null)
	{
	    if ( !$type OR ! is_string($type) )
        {// неизвестно, в какой формат экспортировать данные'; 
            return false;
        }
        $formats = $this->get_formats();
        if ( !in_array($type, $formats) )
        {// в списке поддерживаемых форматов запрашиваемый не значится';
            return false;
        }
        if ( isset($this->formats->$type) )
        {//уже создали объект для экспорта в такой тип файлов';
            return $this->formats->$type;//вернем его
        }
        
        // определяем имя класса, занимающегося форматированием
        $classname = 'dof_modlib_templater_format_achievements_rating_'.$type;
        if ( class_exists($classname) )
        {// класс с нужным названием есть в папке';
            //создаем его экземпляр и сохраняем его
            $this->formats->$type = new $classname($this->dof, 
                $this->plugintype, $this->pluginname, $this->templatename);
            return $this->formats->$type;
        } else
        {// в файле нет класса с нужным названием';
            return false;
        }
	}
}
?>