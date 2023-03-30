<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Condition main class.
 *
 * @package    availability_otcomparison
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_otcomparison;


defined('MOODLE_INTERNAL') || die();

/**
 * Condition main class.
 *
 * @package availability_otcomparison
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition 
{
    
    /** @var int value to compare */
    protected $amount;
    /** @var string comparing operator */
    protected $operator;
    /** @var string preprocessor */
    protected $preprocessor;
    /** @var string data source to compare with value */
    protected $source;
 
    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) 
    {
        global $CFG;
        
        
        if( property_exists($structure, 'preprocessor')
            && in_array((string)$structure->preprocessor, array_keys(self::get_preprocessors())) )
        {
            $this->preprocessor = (string)$structure->preprocessor;
        } else
        {
            throw new \coding_exception('Missing or invalid preprocessor for otcomparison condition');
        }
        
        if( property_exists($structure, 'amount') )
        {
            if( $this->preprocessor != 'date' && is_number($structure->amount) )
            {
                $this->amount = (int)$structure->amount;
            } else if( $this->preprocessor == 'date' )
            {
                try {
                    $amount = new \DateTime($structure->amount);
                    $this->amount = $structure->amount;
                } catch( \Exception $ex) 
                {
                    throw new \coding_exception('Invalid amount for otcomparison condition');
                }
            } else
            {
                throw new \coding_exception('Invalid amount for otcomparison condition');
            }
        } else
        {
            throw new \coding_exception('Missing amount for otcomparison condition');
        }
        
        if( property_exists($structure, 'operator') 
            && in_array((string)$structure->operator, array_keys(self::get_operators())) )
        {
            $this->operator = (string)$structure->operator;
        } else 
        {
            throw new \coding_exception('Missing or invalid operator for otcomparison condition');
        }
        
        if( property_exists($structure, 'source') 
            && in_array((string)$structure->source, array_keys(self::get_fields())) )
        {
            $this->source = (string)$structure->source;
        } else 
        {
            throw new \coding_exception('Missing or invalid source for otcomparison condition');
        }
    }
 
    /**
     * {@inheritDoc}
     * @see \core_availability\tree_node::save()
     */
    public function save() 
    {
        $saveobj = new \stdClass();
        $saveobj->amount = (int)$this->amount;
        $saveobj->operator = (string)$this->operator;
        $saveobj->preprocessor = (string)$this->preprocessor;
        $saveobj->source = (string)$this->source;
        $saveobj->type = 'otcomparison';
        
        return $saveobj;
    }
    
    /**
     * JSON код ограничения доступа
     * 
     * @return stdClass Object representing condition
     */
    public static function get_json($amount, $operator, $preprocessor, $source) 
    {
        return (object)[
            'type' => 'otcomparison', 
            'amount' => (int)$amount, 
            'operator' => (string)$operator,
            'preprocessor' => (string)$preprocessor,
            'source' => (string)$source,
        ];
    }
    
    /**
     * Восстановление из бэкапа
     * {@inheritDoc}
     * @see \core_availability\tree_node::update_after_restore()
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) 
    {
        return true;
    }
 
    
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::is_available()
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) 
    {
        global $DB, $CFG;//$USER;//, $PAGE, 

        $allow = false;
        
        $preparedvalues = $this->prepare_criteria_values($userid);
        if ( $preparedvalues !== false )
        {
            list($value1, $value2) = $preparedvalues;
            $allow = $this->criteriaMet($value1, $this->operator, $value2);
        } else
        {
            $allow = ( $this->operator == '!=' );
        }
        
        return ( $not ? !$allow : $allow );
    }
 
    
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::get_description()
     */
    public function get_description($full, $not, \core_availability\info $info) 
    {
        $a = new \stdClass();

        $userfields = self::get_fields();
        if( array_key_exists($this->source, $userfields) )
        {// Настроенное поле определено
            $a->source = $userfields[$this->source]->name;
        } else
        {
            $a->source = get_string('retrieve_source_failed', 'availability_otcomparison', $this->source);
        }
        
        
        $a->operator = $not ? $this->get_operator_anthonym() : $this->operator;
        
        if ( $this->preprocessor == 'date' )
        {
            
            try {
                $date = new \DateTime($this->amount);
                $a->amount = userdate($date->getTimestamp());
            } catch (\Exception $ex) {
                $a->amount = get_string('invalid_date', 'availability_otcomparison', $this->amount);
            }
        } else
        {
            if( is_number($this->amount) )
            {
                $a->amount = (int)$this->amount;
            } else
            {
                $a->amount = get_string('invalid_int', 'availability_otcomparison', $this->amount);
            }
        }
        
        return get_string('description_'.$this->preprocessor, 'availability_otcomparison', $a);
    }

    /**
     * Получить допустимые поля пользователя
     *
     * @return array - массив додопустимых полей пользователя [shortname => object]
     */
    public static function get_fields($userid=null)
    {
        global $CFG, $DB;
        
        $result = [];
        
        if ( file_exists($CFG->dirroot.'/blocks/myinfo/locallib.php') 
            && file_exists($CFG->dirroot.'/user/profile/lib.php') )
        {
            include_once($CFG->dirroot.'/blocks/myinfo/locallib.php');
            include_once($CFG->dirroot.'/user/profile/lib.php');
            
            $user = false;
            if( ! is_null($userid) )
            {
                $user = \core_user::get_user($userid);
                if( $user )
                {
                    $user->profile = profile_user_record($user->id, false);
                }
            }
            
            // Добавление нужных полей
            $datefields = [
                'timecreated' => get_string('timecreated', 'availability_otcomparison'),
                'firstaccess' => get_string('firstaccess', 'availability_otcomparison'),
                'lastlogin' => get_string('lastlogin', 'availability_otcomparison'),
                'currentlogin' => get_string('currentlogin', 'availability_otcomparison'),
                'lastaccess' => get_string('lastaccess', 'availability_otcomparison'),
                'timemodified' => get_string('timemodified', 'availability_otcomparison'),
            ];
            // Настраиваемые поля профиля
            $customfields = get_customfields_list();
            // Стандартные поля профиля
            $userfields = get_userfields_list($datefields);
            // Все поля
            $fields = array_merge($userfields, $customfields);
            if( array_key_exists('interests', $fields) )
            {
                unset($fields['interests']);
            }
        
            foreach ($fields as $shortname=>$name)
            {
                if( ! empty($user) )
                {
                    if ( substr($shortname, 0, 14) == 'profile_field_' )
                    {// кастомное поле
                        $field = get_customfield_data($user, substr($shortname, 14));
                    } else
                    {// обычное поле профиля
                        $field = get_userfield_data($user, $shortname, $name);
                    }
                    // Тип поля (нам нужно будет работать только с текстовыми полями и датами)
                    $field->type = null;
                } else
                {
                    $field = new \stdClass();
                    // Наименование поля
                    $field->name = $name;
                    // Краткое имя поля
                    $field->shortname = $shortname;
                    // Тип поля (нам нужно будет работать только с текстовыми полями и датами)
                    $field->type = null;
                    $field->value = null;
                }

                if ( substr($shortname, 0, 14) == 'profile_field_' )
                {// Настраиваемое поле профиля
                    
                    // Получение записи
                    $cfrecord = $DB->get_record('user_info_field', [
                        'shortname' => substr($shortname, 14)
                    ]);
                    
                    if( ! empty($cfrecord->datatype) )
                    {// Тип определен, укажем его
                        $field->type = $cfrecord->datatype;
                    }
                } else
                {// Обычное поле профиля
                    if( array_key_exists($shortname, $datefields) )
                    {// Добавленное нами поле с датами
                        $field->type = 'datetime';
                    } else if( array_key_exists($shortname, $fields) )
                    {
                        $field->type = 'text';
                    }
                }
                
                $result[$shortname] = $field;
            }
        }
        
        return $result;
    }

    /**
     * Получить допустимые препроцессоры
     *
     * @return array - массив додопустимых препроцессоров
     */
    public static function get_preprocessors()
    {
        return [
            'date' => get_string('preprocessor_date', 'availability_otcomparison'),
            'days' => get_string('preprocessor_days', 'availability_otcomparison'),            
            'int' => get_string('preprocessor_int', 'availability_otcomparison')
        ];
    }
    
    /**
     * Получить допустимые операторы
     * 
     * @return array - массив додопустимых операторов
     */
    public static function get_operators()
    {
        return [
            '<' => get_string('operator_less_than', 'availability_otcomparison'),
            '>' => get_string('operator_more_than', 'availability_otcomparison'),
            '==' => get_string('operator_equal_to', 'availability_otcomparison'),
            '!=' => get_string('operator_not_equal_to', 'availability_otcomparison'),
            '<=' => get_string('operator_less_than_or_equal', 'availability_otcomparison'),
            '>=' => get_string('operator_more_than_or_equal', 'availability_otcomparison')
        ];
    }
    
    /**
     * Возвращает противоположный оператор для отрицания условия
     * 
     * @return string - опреатор-антоним или false в случае ошибки
     */
    protected function get_operator_anthonym()
    {
        $result = false;
        switch($this->operator)
        {
            case '<':
                $result = '>=';
                break;
            case '>':
                $result = '<=';
                break;
            case '==':
                $result = '!=';
                break;
            case '!=':
                $result = '==';
                break;
            case '<=':
                $result = '>';
                break;
            case '>=':
                $result = '<';
                break;
        }
        return $result;
    }
    
    /**
     * Criteria checker
     *
     * @param string $value1 - the value to be compared
     * @param string $operator - the operator
     * @param string $value2 - the value to test against
     * @return boolean - criteria met/not met
     */
    protected function criteriaMet($value1, $operator, $value2)
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;
                break;
            case '<=':
                return $value1 <= $value2;
                break;
            case '>':
                return $value1 > $value2;
                break;
            case '>=':
                return $value1 >= $value2;
                break;
            case '==':
                return $value1 == $value2;
                break;
            case '!=':
                return $value1 != $value2;
                break;
            default:
                return false;
        }
        return false;
    }
    
    /**
     * Подготовка значений для сравнения в зависимости от выбранного препроцессора 
     * 
     * @param int $userid - идентификатор пользователя для получения значения профиля
     * 
     * @return array - массив из двух сравниваемых значений или false в случае ошибки
     */
    protected function prepare_criteria_values($userid)
    {
        $userfields = self::get_fields($userid);
        if( array_key_exists($this->source, $userfields) )
        {// Настроенное поле определено
            $userfield = $userfields[$this->source];
            
            $value1 = $userfield->value;
            $value2 = $this->amount;
            $error = false;
            
            
            switch($this->preprocessor)
            {
                case 'date':
                    try {
                        $value1 = new \DateTime('@'.$userfield->value);
                        $value2 = new \DateTime($this->amount);
                    } catch (\Exception $ex)
                    {
                        $error = true;
                    }
                    break;
                case 'days':
                    if( is_number($this->amount) )
                    {
                        try {
                            $now = new \DateTime();
                            $userfielddate = new \DateTime('@'.$userfield->value);
                            $value1 = (int)$userfielddate->diff($now)->format('%R%a');
                            $value2 = (int)$this->amount;
                        } catch(\Exception $ex) {
                            $error = true;
                        }
                    } else
                    {
                        $error = true;
                    }
                    break;
                case 'int':
                    if( is_number($userfield->value) && is_number($this->amount) )
                    {
                        $value1 = (int)$userfield->value;
                        $value2 = (int)$this->amount;
                    } else
                    {
                        $error = true;
                    }
                    break;
            }
            
            if( $error )
            {
                return false;
            } else
            {
                return [$value1, $value2];
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \core_availability\condition::get_debug_string()
     */
    protected function get_debug_string() 
    {
        return '';
    }
}
?>