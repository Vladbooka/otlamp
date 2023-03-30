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
 * Виджет выпадающего блока
 * 
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_modlib_widgets_dropblock
{
    /**
     * Экземпляр Деканата
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * HTML-код текста в кнопке для показа\скрытия выпадающего блока
     * 
     * @var string
     */
    protected $label = '';
    
    /**
     * HTML-код содержимого выпадающего блока
     *
     * @var string
     */
    protected $content = '';
    
    /**
     * HTML-код заголовка
     *
     * @var string
     */
    protected $title = '';
    
    /**
     * Дополнительные опции отображения
     *
     * @var array
     */
    protected $options = [];
    
    /**
     * Аттрибуты для лейбла
     * @var array
     */
    protected $labelattr = [];
    
    /**
     * Аттрибут для дроп-блока
     * @var array
     */
    protected $contentattr = [];
    
    /** 
     * Конструктор класса
     * 
     * @param dof_control - Контроллер Деканата
     * @param array $options - Дополнительные опции отображения виджета
     *      ['uniqueid'] - Имя виджета
     */
    function __construct(dof_control $dof, $options = [])
    {
        $this->dof     = $dof;
        $this->options = $options;
        if( ! isset($this->options['labelattr']) )
        {
            $this->options['labelattr'] = [];
        }
        if( ! isset($this->options['contentattr']) )
        {
            $this->options['contentattr'] = [];
        }
        if ( ! isset($this->dof->modlib('widgets')->dropblock) )
        {// Первый вызов построения виджета
            $this->dof->modlib('widgets')->dropblock = ['0'];
            $this->uniqueid = '0';
        } else 
        {// Определить текущий уникальный идентификатор виджета
            $lastid = end($this->dof->modlib('widgets')->dropblock);
            $lastid++;
            $this->uniqueid = $lastid;
        }
        if ( isset($this->options['uniqueid']) )
        {
            $this->uniqueid = $this->options['uniqueid'];
        }
        $this->dof->modlib('widgets')->dropblock[] = $this->uniqueid;
        
        // Добавить CSS
        $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/dropblock/styles.css', false);
        
        // Добавить JS
        $this->dof->modlib('nvg')->add_js('modlib', 'widgets', '/dropblock/script.js', false);
    }
    
    /**
     * Выставить аттрибуты для лейбла
     * @param array $attr
     */
    protected function set_label_attributes($attr = [])
    {
        $this->labelattr = $attr;
    }
    
    /**
     * Выставить аттрибуты для дроп-блока
     * @param array $attr
     */
    protected function set_content_attributes($attr = [])
    {
        $this->contentattr = $attr;
    }
    
    /**
     * Установить HTML-код текста в кнопке показа\скрытия выпадающего блока
     * 
     * @param string $html - HTML-код текста в кнопке
     * 
     * @return bool - Результат установки
     */
    public function set_label($html = '')
    {
        $this->label = $html;
        $this->set_label_attributes($this->options['labelattr']);
        return true;
    }
    
    /**
     * Установить HTML-код содержимого выпадающего блока
     *
     * @param string $html - HTML-код содержимого
     *
     * @return bool - Результат установки
     */
    public function set_content($html = '')
    {
        $this->content = $html;
        $this->set_content_attributes($this->options['contentattr']);
        return true;
    }
    
    /**
     * Установить HTML-код текста в заголовке выпадающего блока
     *
     * @param string $html - HTML-код текста
     *
     * @return bool - Результат установки
     */
    public function set_title($html = '')
    {
        $this->title = $html;
        return true;
    }
    
    /**
     * Генерация HTML-кода виджета
     * 
     * @return string - HTML-код виджета
     */
    public function render()
    {
        // Статус отображения выпадающего блока
        $display = false;
        if ( ! empty($this->options['show']) )
        {
            $display = true;
        }
        
        // Инициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Уникальное имя виджета
        $name = 'dof_dropblock_'.$this->uniqueid;

        $wrapperattr = [
            'id' => 'dof_dropblock_wrapper_'.$name,
            'data-id' => $name,
            'data-direction' => $this->options['direction'] ?? 'auto'
        ];
        // Формирование блока
        $html = dof_html_writer::start_div('dof_dropblock_wrapper', $wrapperattr);
        if( isset($this->labelattr['class']) )
        {
            $this->labelattr['class'] = 'dof_dropblock_actionblock ' . $this->labelattr['class'];
        } else 
        {
            $this->labelattr['class'] = 'dof_dropblock_actionblock';
        }
        // Кнопка открытия
        $html .= dof_html_writer::label($this->label, $name, true, $this->labelattr);
        $html .= dof_html_writer::start_div('dof_dropblock', $this->contentattr);
        $html .= dof_html_writer::checkbox($name, null, $display, null, 
            ['id' => $name, 'class' => 'dof_dropblock_control']);

        $html .= dof_html_writer::start_div('dof_dropblock_content');
        $html .= dof_html_writer::start_div('dof_dropblock_content_body');
        $html .= $this->content;
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        
        $html .= dof_html_writer::end_div();
        
        return $html;
    }
}
?>