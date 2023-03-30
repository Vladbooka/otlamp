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
 * Класс генератора ссылки на историю статусов
 *
 * @package    modlib
 * @subpackage widgets
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_widgets_status_link
{
    /**
     * Экземпляр Деканата
     * 
     * @var dof_control
     */
    var $dof;
    
    /**
     * Параметры начального отбора
     *
     * @var array
     */
    protected $options = [];
    
    /** 
     * Конструктор класса
     * 
     * @param dof_control - глобальный объект $DOF 
     * @param array $options - Параметры начального отбора
     *    ['plugintype'] - Тип плагина
     *    ['plugincode'] - Код плагина
     *    ['objectid']   - ID обьекта
     *    ['startdate']  - Начальная дата
     *    ['finishdate'] - Конечная дата  
     */
    function __construct(dof_control $dof, $options = [])
    {
        $this->dof     = $dof;
        $this->options = $options;
    }
    /**
     * Отобразить спойлер
     * 
     * @return string - HTML-код спойлера
     */
    public function render()
    {
        if($this->dof->im('statushistory')->is_access('view') === false){
            return;
        }
        // Формирование ссылки
        $optlist = ['plugintype', 'plugincode', 'objectid', 'startdate', 'finishdate'];
        $addvars = [];
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        foreach ($optlist as $opt){
            if(!empty($this->options[$opt])){
                $addvars[$opt] = $this->options[$opt];
            }
        }
        if(empty($addvars['startdate'])){
            $addvars['startdate'] = 1;
        }
        if(empty($addvars['finishdate'])){
            $addvars['finishdate'] = time() + 86400;
        }
        // Подключаем url с параметрами
        $url = $this->dof->url_im('statushistory','/index.php', $addvars);
        // Инициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();
        // текущий статус
        if(!empty($addvars['plugincode']) && !empty($addvars['objectid'])){
            $obj = $this->dof->storage($addvars['plugincode'])->get($addvars['objectid']);
            $status = dof_html_writer::tag('b', $this->dof->get_string('current_status', 'widgets', null, 'modlib'));
            $status .= $this->dof->workflow($addvars['plugincode'])->get_name($obj->status);
        }else{
            $status = '';
        }
        // Ссылка
        $link = dof_html_writer::link(
            $url,
            $this->dof->get_string('status_link_button', 'widgets', null, 'modlib')
            );
        // Формирование блока
        $html = dof_html_writer::div(
            $status.'<br>'.$link,
            'dof_status_link_wrapper'
            );
        return $html;
    }
}
?>