<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Класс базового группового модификатора.
 * 
 * Групповой модификатор может исполнять метод process который запускается после того как отработают обычные
 * модификаторы и сформируют массив с данными предыдущих шагов. 
 * Реализовывать definition нет смысла так-как это задача обычных модификаторов. 
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof;

use HTML_QuickForm;
use stdClass;

abstract class group_modifiers_base
{
    /**
     * Настройки полей формы регистрации
     * @var array
     */
    protected $user_cfg_fields = [];
    
    /**
     * Конфиг плагина
     * @var array
     */
    protected $config = null;
    
    /**
     * Шаг регистрации
     * @var integer
     */
    protected $step = null;
    
    function __construct($usercfgfields, $config, $step) {
        $this->user_cfg_fields = $usercfgfields;
        $this->config = $config;
        $this->step = $step;
    }
    /**
     * Получение языковой строки модификатора
     */
    abstract public static function get_name_string();

    
    /**
     * 
     * @param stdClass $user - поля пользователя из формы
     * @param stdClass $prepareuf - подготовленные модификаторами поля
     * @return array
     */
    public function process(stdClass $user, array $prepareuf) {
        return [];
    }
    
    public function validation($data, $files) {
        return [];
    }
    
    /**
     * Валидация настроек на странице "Настройки полей формы регистрации"
     *
     * @param array $data
     * @param string $fldname
     */
    abstract public static function settings_validation(array $data, string $fldname);
    
    /**
     * Определяет будет ли можификатор отображаться на форме настроек
     *
     * @param string $fldname
     * @param array $src_config_fields
     */
    abstract public static function display_on_settings_form(string $fldname, array $srcconfigfields);
}