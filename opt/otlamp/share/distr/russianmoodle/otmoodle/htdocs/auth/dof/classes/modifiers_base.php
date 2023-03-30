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
 * Класс базового модификатора.
 * 
 * Не раелизует методы проверки формы так-ка это задача form_fields_factory, проверку можно реализовать только 
 * в групповом иодификаторе
 *
 * @package    auth
 * @subpackage dof
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_dof;

use HTML_QuickForm;
use coding_exception;
use stdClass;
use core_user;

abstract class modifiers_base
{
    /**
     * Имя поля как в amma users
     * @var string
     */
    protected $fldname = '';
    
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
     * Запись из вгешнего источеика
     * @var array
     */
    protected $external_record = null;
    
    function __construct($fldname, $usercfgfields = [], $config = null, $externalrecord = null) {
        $this->fldname = $fldname;
        $this->user_cfg_fields = $usercfgfields;
        $this->config = $config;
        $this->external_record = $externalrecord;
    }
    
    /**
     * Получение языковой строки модификатора
     */
    abstract public static function get_name_string();
    
    /**
     * Если модификатор возврашает значение поля то тут должно быть true
     * нужно для определения что модификаторры не возвращают значение и его нужно взять из формы.
     */
    abstract public function is_field_data_returned();
    
    /**
     * 
     * @param HTML_QuickForm $mform
     */
    public function definition(form_fields_factory $fofifa) {
        ;
    }
    
    /**
     * 
     * @param stdClass $user - поля формы
     * @param stdClass $prepareuf - поля подготовленные модификаторами
     */
    abstract public function process(stdClass $user, stdClass &$prepareuf);
    
    /**
     * Преобразование имени поля хранимого в настройках в название поля формы регистрации,
     * это требуется для сохранения совместимости с ранее написанным кодом и кастомных полей 
     * 
     * @return string
     */
    public static function get_form_field_name(string $fldname) {
        if (stripos($fldname, 'user_field_') === 0) {
            $fldname = substr($fldname, 11);
        } elseif (stripos($fldname, 'user_profilefield_') === 0) {
            $fldname = 'profile_field_' . substr($fldname, 18);
        } else {
            print_error('Field "' . $fldname . '" not supported');
        }
        return $fldname;
    }
    
    /**
     * Получение значения переданной переменной из записи внешнего источника.
     * 
     * @param string $fldname
     * @param array|mixed $usercfgfields
     * @param array|mixed $externalrecord
     * @return string
     */
    protected static function get_src_field_value(string $fldname, $usercfgfields, $externalrecord) {
        if (! empty($usercfgfields[$fldname]['srcfld'])
            && is_array($usersrccfg = json_decode($usercfgfields[$fldname]['srcfld'], true)))
        {
            if(is_array($externalrecord)) {
                foreach ($externalrecord as $srcid => $srcfielsdata) {
                    $srcfieldname = $usersrccfg[$srcid];
                    if (array_key_exists($srcfieldname, $srcfielsdata)) {
                        // Очистим и вернем значение из внешнего источника
                        $cleanedvalue = self::clean_src_value($srcfielsdata[$srcfieldname], $fldname);
                        // Проверка длинны поля
                        self::check_data_length($cleanedvalue, $fldname);
                        return $cleanedvalue;
                    } else {
                        print_error('No configurated field found in external source');
                    }
                }
            } else {
                print_error('No external source data transmitted to modifier');
            }
        } else {
            print_error('User registration fields config data error');
        }
    }
    
    /**
     * Очищает переданное значение стандартного поля согласно его типу
     * или возврашает не очищенное значение если поле не поддерживается
     * 
     * @param string $param
     * @param string $fldname
     * @return mixed
     */
    private static function clean_src_value($param, string $fldname) {
        $fldname = self::get_form_field_name($fldname);
        try {
            $type = core_user::get_property_type($fldname);
        } catch (coding_exception $e) {
            return $param;
        }
        return clean_param($param, $type); 
    }
    
    /**
     * Проверяет длинну значения стандартного поля пользователя 
     * 
     * @param string $param
     * @param string $fldname
     */
    private static function check_data_length($param, string $fldname) {
        if (stripos($fldname, 'user_field_') === 0) {
            $fldname = substr($fldname, 11);
            $fieldproperties = form_fields_factory::user_field_properties($fldname);
            if (strlen($param) > $fieldproperties['maxlength']) {
                print_error('field_value_too_long', 'auth_dof', '', $fldname);
            }
        }
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