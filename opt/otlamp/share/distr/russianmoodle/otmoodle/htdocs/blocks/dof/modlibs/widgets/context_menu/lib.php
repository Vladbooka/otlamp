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
 * Класс контекстного меню
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_widgets_context_menu
{
    /**
     * Экземпляр Деканата
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Дополнительные опции отображения
     * @var array
     */
    protected $options = [];
    
    /**
     * массив элементов меню
     * @var array
     */
    protected $menu;
    
    /**
     * Лейбл дроп-блока
     * @var string
     */
    protected $dropblocklabel;
    
    /**
     * HTML-код виджета
     * @var unknown
     */
    protected $html;
    
    /**
     * Флаг построения html-кода виджета
     * @var unknown
     */
    protected $build;
    
    /** 
     * Конструктор класса
     * @param dof_control - глобальный объект $DOF 
     * @param array $options - Дополнительные опции отображения контекстного меню
     */
    function __construct(dof_control $dof, $label = null, $options = [])
    {
        $this->dof = $dof;
        $this->options = $options;
        $this->menu = [];
        $this->html = '';
        $this->build = false;
        
        $this->set_label($label);
        
        // Добавляем CSS
        $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/css/dof_context_menu.css', false);
    }
    
    /**
     * Вернуть html-представление дроп-блока с контекстным меню
     * @return unknown
     */
    public function render()
    {
        $this->build();
        return $this->dof->modlib('widgets')->dropblock(
            $this->dropblocklabel,
            $this->html,
            '',
            [
                'labelattr' => [
                    'class' => 'dof_context_menu_label'
                ],
                'contentattr' => [
                    'class' => 'dof_context_menu_content'
                ],
                'direction' => $this->options['direction'] ?? 'auto'
                ]
        );
    }
    
    /**
     * Распечатать меню
     */
    public function display()
    {
        echo $this->render();
    }
    
    /**
     * Построить html-код меню
     */
    protected function build()
    {
        if( ! $this->build )
        {
            foreach($this->menu as $item)
            {
                $menu[] = $this->wrap_item($item);
            }
            $this->html = implode('', $menu);
            $this->build = true;
        } else 
        {
            return;
        }
    }
    
    /**
     * Установить лейбл дроп-блока
     * @param unknown $label
     */
    protected function set_label($label = null)
    {
        if( is_null($label) )
        {
            $this->dropblocklabel = dof_html_writer::div(dof_html_writer::tag('span', $this->dof->get_string('actions_label', 'widgets', null, 'modlib')));
        } else
        {
            $this->dropblocklabel = $label;
        }
    }
    
    /**
     * Добавить элемент меню
     * @param mixed $item объект элемента меню
     */
    protected function add_item($item)
    {
        $item->build();
        $this->menu[] = $item;
    }
    
    /**
     * Обертка элемента
     */
    protected function wrap_item($item)
    {
        return dof_html_writer::div($item->html, 'dof_context_menu_item ' . get_class($item), ['id' => $item->id]);
    }
    
    /**
     * Добавить элементы меню
     * @param array $items массив объектов элементов меню
     */
    public function add_items($items)
    {
        foreach($items as $item)
        {
            $this->add_item($item);
        }
    }
    
    /**
     * Получить объект элемента меню типа "ссылка"
     * @return dof_context_menu_item_link
     */
    public function get_item_link()
    {
        // Подключение класса виджета
        require_once($this->dof->plugin_path('modlib', 'widgets', '/context_menu/classes/item/context_menu_item_link.php'));
        return new dof_context_menu_item_link();
    }
    
    /**
     * Получить объект элемента меню типа "html"
     * @return dof_context_menu_item_html
     */
    public function get_item_html()
    {
        // Подключение класса виджета
        require_once($this->dof->plugin_path('modlib', 'widgets', '/context_menu/classes/item/context_menu_item_html.php'));
        return new dof_context_menu_item_html();
    }
}
?>