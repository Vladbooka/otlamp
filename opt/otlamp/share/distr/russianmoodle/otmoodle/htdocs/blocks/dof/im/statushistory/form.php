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
 * Интерфейс формы история статусов
 *
 * @package    im
 *
 * @package    statushistory
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

require_once("$CFG->libdir/formslib.php");
/** 
 * Форма редактирования сообщения
 */
class dof_im_statushistory_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    // массив доступных типов плагинов
    protected $plugintype;
    // массив доступных плагинов
    protected $plugincode;
    // значения по умолчанию
    protected $defaults;
    // данные для пагинации
    protected $limitnum;
    protected $limitfrom;
    
    //Add elements to form
    public function definition() {
        $this->plugintype = $this->_customdata->plugintype;
        $this->plugincode = $this->_customdata->plugincode;
        $this->defaults = $this->_customdata->defaults;
        
        $this->limitnum = $this->_customdata->limitnum;
        $this->limitfrom = $this->_customdata->limitfrom;
        
        $this->dof = $this->_customdata->dof;
        
        $mform = $this->_form; // Don't forget the underscore!
        
            // Заголовок формы
            $mform->addElement(
                'header',
                'sort',
                $this->dof->get_string('sortheader', 'statushistory')
                );
            // select тип плагина
            $plugintype = $mform->addElement(
                'select',
                'plugintype',
                $this->dof->get_string('plugintype', 'statushistory')
                );
            $plugintype->addOption(
                $this->dof->get_string('notselected', 'statushistory'),
                'notselected'
                );
            foreach ($this->plugintype as $value){
                // Языковая сторока тип плагина
                $pluginstingtype ='';
                $pluginstingtype .= $this->dof->get_string(
                    $value . 's', 'admin', null, 'im', ['empry_result' => $value]
                    );
                if($pluginstingtype != $value){
                    $pluginstingtype .= ' (' . $value . ')';
                }
                // добавляем опцию
                $plugintype->addOption( $pluginstingtype, $value);
            }
            $plugintype->setSelected($this->defaults['plugintype']);
            
            // select код плагина
            $plugincode = $mform->addElement(
                'select',
                'plugincode',
                $this->dof->get_string('plugincode', 'statushistory')
                );
            $plugincode->addOption(
                $this->dof->get_string('notselected', 'statushistory'),
                'notselected'
                );
            foreach ($this->plugincode as $value){
                // Языковая сторока title плагина
                $pluginstingcode ='';
                $code = '';
                foreach ($this->plugintype as $val){
                    if($this->dof->plugin_exists($val, $value)){
                        $code = $this->dof->get_string(
                            'title', $value, null, $val, ['empry_result' => $value]
                            );
                    }
                    if($code != $value && !empty($code)){
                        $pluginstingcode = $code;
                        break;
                    }
                }
                if(empty($pluginstingcode)){
                    $pluginstingcode = $code;
                }
                if($pluginstingcode != $value){
                    $pluginstingcode .= ' (' . $value . ')';
                }
                // добавляем опцию
                $plugincode->addOption( $pluginstingcode, $value);
            }
            $plugincode->setSelected($this->defaults['plugincode']);
            
            // Поле ид обьекта
            $mform->addElement(
                'text',
                'objectid',
                $this->dof->get_string('objectid', 'statushistory')
                );
            $mform->setType('objectid', PARAM_INT);
            $mform->setDefault('objectid', $this->defaults['objectid']);
            
            // Поле c датой начало
            $mform->addElement(
                'date_selector',
                'datestart',
                $this->dof->get_string('datestart', 'statushistory')
                );
            $mform->setDefault('datestart', $this->defaults['datestart']);

            // Поле c датой конец
            $mform->addElement(
                'date_selector',
                'datefinish',
                $this->dof->get_string('datefinish', 'statushistory')
                );
            $mform->setDefault('datefinish', $this->defaults['datefinish']);
            
            // кнопки "сохранить" и "отмена"
            $this->add_action_buttons(false, $this->dof->get_string('send', 'statushistory'));
            
            // Убираем лишние пробелы со всех полей формы
            $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Процесс возвращает массив статусов
     * 
     * @return array array('data' => обьект статусов, 'conditions' => данные фильтра)
     */
    public function process() {
        // если выбран фильтр
        if ($formdata = $this->get_data()) {
            // Получение статусов
            $conditions = [
                $formdata->plugintype != 'notselected' ? $formdata->plugintype : null,
                $formdata->plugincode != 'notselected' ? $formdata->plugincode : null,
                !empty($formdata->objectid) ? $formdata->objectid : null,
                $formdata->datestart,
                $formdata->datefinish
            ];
            // поле и направление сортировки
            if($this->defaults['sdir'] == 'desc'){
                $sort = $this->defaults['sort'] . ' DESC';
            }else{
                $sort = $this->defaults['sort'] . ' ASC';
            }
            // формируем статусы
            $data = $this->dof->storage('statushistory')->get_statuses(
                $conditions[0] ,$conditions[1], $conditions[2], $conditions[3], $conditions[4],
                $sort, $this->limitfrom - 1, $this->limitnum
                );
            return ['data' => $data, 'conditions' => $conditions];
        } elseif(
            $this->defaults['plugintype'] != 'notselected' or
            $this->defaults['plugincode'] != 'notselected' or
            $this->defaults['objectid'] != '' or
            $this->defaults['datestart'] != $this->defaults['timedefault'] - (86400 * 92) or
            $this->defaults['datefinish'] != null
            )
        {
            // получение статусов используя данные get/post
            $conditions = [
                $this->defaults['plugintype'] != 'notselected' ? $this->defaults['plugintype'] : null,
                $this->defaults['plugincode'] != 'notselected' ? $this->defaults['plugincode'] : null,
                $this->defaults['objectid'],
                $this->defaults['datestart'],
                $this->defaults['datefinish']
            ];
            // поле и направление сортировки
            if($this->defaults['sdir'] == 'desc'){
                $sort = $this->defaults['sort'] . ' DESC';
            }else{
                $sort = $this->defaults['sort'] . ' ASC';
            }
            // формируем статусы
            $data = $this->dof->storage('statushistory')->get_statuses(
                $conditions[0] ,$conditions[1], $conditions[2], $conditions[3], $conditions[4],
                $sort, $this->limitfrom - 1, $this->limitnum
                );
            return ['data' => $data, 'conditions' => $conditions];
        }
       
    }
}

?>