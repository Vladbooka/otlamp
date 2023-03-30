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
 * Класс иконки подсказки
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_widgets_helpicon
{
    /**
     * Экземпляр Деканата
     *
     * @var dof_control
     */
    protected $dof;

    /**
     * Идентификатор строки заголовка подсказки
     *
     * @var string
     */
    protected $identidier = '';

    /**
     * Тип плагина
     *
     * @var string
     */
    protected $plugintype = '';

    /**
     * Код плагина
     *
     * @var string
     */
    protected $plugincode = '';

    /**
     * Конструктор класса
     *
     * @param dof_control - глобальный объект $DOF
     */
    function __construct(dof_control $dof, $identidier, $plugincode, $plugintype = 'im')
    {
        $this->dof     = $dof;
        $this->identidier = $identidier;
        $this->plugincode = $plugincode;
        $this->plugintype = $plugintype;
    }

    public function render()
    {
        $title = $this->dof->get_string($this->identidier, $this->plugincode, null, $this->plugintype);
        $alt = get_string('helpprefix2', '', trim($title, ". \t"));
        $helptext = $this->dof->get_string($this->identidier.'_help', $this->plugincode, null, $this->plugintype);
        $icon = $this->dof->modlib('ig')->icon_plugin('help_question', 'modlib', 'ig', null, [
            'alt' => $alt,
        ]);
        return dof_html_writer::tag('a', $icon, [
            'class' => 'btn btn-link p-0',
            'role' => 'button',
            'data-container' => 'body',
            'data-toggle' => 'popover',
            'data-placement' => 'right',
            'data-content' => $helptext,
            'data-html' => 'true',
            'tabindex' => '0',
            'data-trigger' => 'focus',
            'data-original-title' => '',
            'title' => ''
        ]);
    }
}
?>