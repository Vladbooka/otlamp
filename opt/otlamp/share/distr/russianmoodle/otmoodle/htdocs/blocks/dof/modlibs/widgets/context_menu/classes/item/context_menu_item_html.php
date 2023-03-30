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
 * Базовый класс элемента контекстного меню
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_context_menu_item_html
{
    /**
     * id для обертки элемента
     * @var string
     */
    protected $id;
    
    /**
     * html код элемента
     * @var string
     */
    protected $html;
    
    /**
     * Флаг построение html-кода элемента
     * @var bool
     */
    protected $build;
    
    /**
     * Конструктор
     * @param unknown $dof
     */
    public function __construct()
    {
        $this->id = $this->html = '';
        $this->build = false;
    }
    
    /**
     * Метод чтения данных
     * @param string $property
     * @return mixed|NULL
     */
    public function __get($property)
    {
        if( isset($this->$property) )
        {
            return $this->$property;
        } else 
        {
            return null;
        }
    }
    
    /**
     * Метод установки данных
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if( property_exists($this, $name) )
        {
            $this->$name = $value;
        }
    }
    
    /**
     * Преобразование объекта в строку
     * @return string
     */
    public function __toString()
    {
        return $this->html;
    }
    
    /**
     * Построить html-представление объекта
     */
    public function build()
    {
        $this->build = true;
        return;
    }
}