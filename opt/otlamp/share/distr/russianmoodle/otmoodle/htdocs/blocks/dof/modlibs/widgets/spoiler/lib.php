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
 * Класс спойлера
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_widgets_spoiler
{
    /**
     * Экземпляр Деканата
     * 
     * @var dof_control
     */
    var $dof;
    
    /**
     * HTML-код текста в кнопке для открытия\закрытия спойлера
     * 
     * @var string
     */
    protected $label = '';
    
    /**
     * HTML-код содержимого спойлера
     *
     * @var string
     */
    protected $content = '';
    
    /**
     * Дополнительные опции отображения
     *
     * @var array
     */
    protected $options = [];
    
    /** 
     * Конструктор класса
     * 
     * @param dof_control - глобальный объект $DOF 
     * @param array $options - Дополнительные опции отображения спойлера
     *      ['uniqueid'] - Имя спойлера
     *      ['show'] - По умолчанию открыть спойлер
     */
    function __construct(dof_control $dof, $options = [])
    {
        $this->dof     = $dof;
        $this->options = $options;
        if ( ! isset($this->dof->modlib('widgets')->spoilerids) )
        {// Первый вызов построения спойлера
            $this->dof->modlib('widgets')->spoilerids = ['0'];
            $this->uniqueid = '0';
        } else 
        {// Определить текущий уникальный идентификатор модального окна
            $lastid = end($this->dof->modlib('widgets')->spoilerids);
            $lastid++;
            $this->uniqueid = $lastid;
        }
        $this->dof->modlib('widgets')->spoilerids[] = $this->uniqueid;
        
        // Добавить CSS
        $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/css/dof_spoiler.css', false);
    }
    
    /**
     * Установить HTML-код текста в кнопке спойлера
     * 
     * @param string $html - HTML-код текста в кнопке спойлера
     * 
     * @return bool - Результат установки
     */
    public function set_label($html = '')
    {
        $this->label = $html;
        return true;
    }
    
    /**
     * Установить HTML-код содержимого спойлера
     *
     * @param string $html - HTML-код содержимого спойлера
     *
     * @return bool - Результат установки
     */
    public function set_content($html = '')
    {
        $this->content = $html;
        return true;
    }
    
    /**
     * Отобразить спойлер
     * 
     * @return string - HTML-код спойлера
     */
    public function render()
    {
        // Инициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Уникальное имя спойлера
        $name = 'dof_modal_'.$this->uniqueid;
        $state = false;
        if ( isset($this->options['show']) )
        {// Установлено переопределение состояния спойлера
            $state = (bool)$this->options['show'];
        }
        
        // Формирование блока модального окна
        $html = dof_html_writer::start_div('dof_spoiler_wrapper');
        
        // Кнопка открытия
        $html .= dof_html_writer::label($this->label, $name);
        $html .= dof_html_writer::start_div('dof_spoiler');
        $html .= dof_html_writer::checkbox($name, null, $state, null, ['id' => $name, 'class' => 'dof_spoiler_control']);
        $html .= dof_html_writer::start_div('dof_spoiler_content');
        $html .= dof_html_writer::start_div('dof_spoiler_content_body');
        $html .= $this->content;
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        
        return $html;
    }
}
?>