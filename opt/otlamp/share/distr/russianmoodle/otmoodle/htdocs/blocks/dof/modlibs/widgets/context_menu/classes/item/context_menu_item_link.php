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

global $CFG;
require_once($CFG->dirroot . '/blocks/dof/modlibs/widgets/context_menu/classes/item/context_menu_item_html.php');

/**
 * Класс элемента контекстного меню "ссылка"
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_context_menu_item_link extends dof_context_menu_item_html
{
    /**
     * текст ссылки
     * @var string
     */
    protected $text = '';
    
    /**
     * Урл-адрес ссылки
     * @var string|moodle_url
     */
    protected $url = '#';
    
    /**
     * Аттрибуты ссылки
     * @var array
     */
    protected $attr = [];
    
    /**
     * Построить html-представление объекта
     * {@inheritDoc}
     * @see dof_context_menu_item_html::build()
     */
    public function build()
    {
        if( ! $this->build )
        {
            $this->html = dof_html_writer::link($this->url, dof_html_writer::div($this->text), $this->attr);
            $this->build = true;
        } else 
        {
            return;
        }
    }
}