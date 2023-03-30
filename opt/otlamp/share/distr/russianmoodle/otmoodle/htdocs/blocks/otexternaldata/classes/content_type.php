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
 * Внешние данные
 *
 * @package    block_otexternaldata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_otexternaldata;

use context;

abstract class content_type {
    
    protected $blockinstance = null;
    
    public function __construct($blockinstance=null)
    {
        if (!is_null($blockinstance))
        {
            $this->blockinstance = $blockinstance;
        }
    }
    
    public abstract function extend_form_definition(&$mform);
    
    public abstract function compose_config(array $formdata);
    
    public abstract function validate_config(array $formdata);
    
    protected abstract function get_items(array $config);
    
    protected function get_additional_data(array $config) {
        return [];
    }
    
    protected function get_items_for_template(array $config)
    {
        return $this->get_items($config);
    }
    
    public function get_item_file(array $config, $data)
    {
        return 'Files are not forwarded for this type of content.';
    }
    
    public function export_for_template(array $config)
    {
        $items = $this->get_items_for_template($config);
        $countitems = count($items);
        
        
        $lastitem = null;
        foreach($items as &$item)
        {
            $i = ($i ?? 0) + 1;
            $item = (array)$item;
            // является ли первым элементом в массиве
            $item['first_item'] = ($i == 1);
            // является ли последним элементом в массиве
            $item['last_item'] = ($i == $countitems);
            // является ли четным элементом
            $item['even_item'] = ($i % 2 == 0);
            // является ли нечетным элементом
            $item['odd_item'] = !$item['even_item'];
            // порядковый номер в выборке
            $item['item_index_num'] = $i;
            
            foreach($item as $column=>$value)
            {
                if ($value != $lastitem[$column]??null)
                {// Изменилось значение в колонке, относительно предыдущего элемента
                    
                    // пометим, что с текущего элемента началась новая группа
                    $item['group_by_'.$column.'_first_in_group'] = true;
                    // пометим предыдущий элемент, что он был последним в группе
                    if (!is_null($lastitem))
                    {
                        $lastitem['group_by_'.$column.'_last_in_group'] = true;
                    }
                    // начнем считать элементы заново
                    $item['group_by_'.$column.'_index_num'] = 1;
                } else
                {
                    // продолжим вести счет элементам в группе
                    $item['group_by_'.$column.'_index_num'] = $lastitem['group_by_'.$column.'_index_num'] + 1;
                }
                
                // элементов в выборке вообще больше нет, поэтому этот тоже - последний в группе
                if ($item['last_item'])
                {
                    $item['group_by_'.$column.'_last_in_group'] = true;
                }
            }
            
            $lastitem = &$item;
        }
        
        return [
            'items' => $items,
            'has_items' => !empty($items),
            'count_items' => $countitems,
            'additional_data' => $this->get_additional_data($config)
        ];
    }
    
    /**
     * Получение списка типов контента
     * @return string[]
     */
    public static function get_content_types_list()
    {
        global $CFG;
        
        $contenttypes = [];
        
        $files = glob($CFG->dirroot . "/blocks/otexternaldata/classes/content_type/*.php");
        if (!empty($files))
        {
            foreach ( $files as $file )
            {
                $basename = basename($file, '.php');
                try {
                    // получение инстанса, чтобы убедиться, что инстанс способен сформироваться
                    // (класс существует, не является черновиком)
                    self::get_content_type_instance($basename);
                    $contenttypes[$basename] = get_string('content_type_'.$basename, 'block_otexternaldata');
                } catch (\Exception $ex){}
            }
        }
        return $contenttypes;
    }
    
    /**
     * Получение класса по коду типа контента
     * @param string $basename
     * @return string
     */
    public static function get_content_type_class_name($basename)
    {
        return '\\block_otexternaldata\\content_type\\'.$basename;
    }
    
    /**
     * Получение экземпляра класса для указанного типа контента
     * @param string $basename
     * @throws \Exception
     * @return content_type
     */
    public static function get_content_type_instance($basename, $blockinstance=null)
    {
        if (!empty($basename))
        {
            $classname = self::get_content_type_class_name($basename);
            if (class_exists($classname))
            {
                $instance = new $classname($blockinstance);
                if (!property_exists($instance, 'draft') || empty($instance->draft))
                {
                    return $instance;
                } else
                {
                    throw new \Exception('Content type in draft status');
                }
            } else {
                throw new \Exception('Class '.$classname.' not exists');
            }
        } else {
            throw new \Exception('Content type basename is empty');
        }
    }
    
    protected function replace_substitution_user($object, $property)
    {
        global $USER;
        
        if ($object == 'user' && !empty($USER) && property_exists($USER, $property))
        {
            return $USER->{$property};
        }
        
        return '{'.$object.'.'.$property.'}';
    }
    
    protected function replace_substitution_course($object, $property)
    {
        global $COURSE;
        
        if ($object == 'course' && property_exists($COURSE, $property))
        {
            return $COURSE->{$property};
        }
        
        return '{'.$object.'.'.$property.'}';
    }
    
    protected function replace_substitution_profilepage($object, $property)
    {
        global $SCRIPT;
        
        if ($object == 'profilepage' && $property == 'userid' && !is_null($this->blockinstance))
        {
            $parentcontext = context::instance_by_id($this->blockinstance->parentcontextid);
            if ($parentcontext->contextlevel == CONTEXT_USER && $SCRIPT !== '/my/index.php')
            {
                $userid = optional_param('id', null, PARAM_INT);
                if (!is_null($userid))
                {
                    return $userid;
                }
            }
        }
        
        return '{'.$object.'.'.$property.'}';
    }
    
    /**
     * Замена подстановок
     *
     * @param array $structure - структура заменяемых данных, пример:
     *              [
     *                  'user' => [ // название объекта для поиска в строке
     *                      'id' => [ // название свойства объекта для поиска в строке
     *                          [$this, 'replace_substitution_user'] // функция, которая выполнит замену
     *                      ]
     *                  ]
     *              ]
     * @param string $string - строка, в которой необходимо произвести подстановки
     * @return string - строка с замененными подстановками
     */
    protected function replace_substitutions(array $structure, string $string)
    {
        // формирование паттерна (чтобы не пришлось снова вникать в регулярку в будущем)
        $patternparts = [];
        foreach($structure as $object => $properties)
        {
            if (is_array($properties))
            {
                $patternparts[] = $object.'.(?:'.implode('|', array_keys($properties)).')';
            }
        }
        
        if (empty($patternparts))
        {
            return $string;
        }
        
        // замена подстановок
        return preg_replace_callback(
            '/{('.implode('|', $patternparts).')}/',
            function($matches) use ($structure) {
                
                $replace = $matches[0];
                $splitparts = explode('.', $matches[1]);
                $object = $splitparts[0];
                $property = $splitparts[1];
                
                if (array_key_exists($object, $structure) && is_array($structure[$object]) &&
                    array_key_exists($property, $structure[$object]) && array_key_exists('replacement', $structure[$object][$property]))
                {
                    $function = $structure[$object][$property]['replacement'];
                    if (is_callable($function))
                    {
                        $replace = call_user_func_array($function, [$object, $property]);
                    } else
                    {
                        $replace = $function;
                    }
                }
                
                return $replace;
            },
            $string
        );
    }
    
    protected function get_substitutions_description($substitutions)
    {
        $items = [];
        foreach($substitutions as $object => $properties)
        {
            foreach($properties as $property => $data)
            {
                $a = new \stdClass();
                $a->object = $object;
                $a->property = $property;
                $items[] = get_string($data['stringid'], 'block_otexternaldata', $a);
            }
        }
        return \html_writer::alist($items);
    }
    
    protected function get_standard_substitutions()
    {
        return [
            'user' => [
                'id' => [
                    'replacement' => [$this, 'replace_substitution_user'],
                    'stringid' => 'replacement_user_id'
                ],
                'username' => [
                    'replacement' => [$this, 'replace_substitution_user'],
                    'stringid' => 'replacement_user_username'
                ],
                'email' => [
                    'replacement' => [$this, 'replace_substitution_user'],
                    'stringid' => 'replacement_user_email'
                ],
                'idnumber' => [
                    'replacement' => [$this, 'replace_substitution_user'],
                    'stringid' => 'replacement_user_idnumber'
                ]
            ],
            'course' => [
                'id' => [
                    'replacement' => [$this, 'replace_substitution_course'],
                    'stringid' => 'replacement_course_id'
                ],
                'shortname' => [
                    'replacement' => [$this, 'replace_substitution_course'],
                    'stringid' => 'replacement_course_shortname'
                ],
                'idnumber' => [
                    'replacement' => [$this, 'replace_substitution_course'],
                    'stringid' => 'replacement_course_idnumber'
                ],
                'category' => [
                    'replacement' => [$this, 'replace_substitution_course'],
                    'stringid' => 'replacement_course_category'
                ],
            ],
            'profilepage' => [
                'userid' => [
                    'replacement' => [$this, 'replace_substitution_profilepage'],
                    'stringid' => 'replacement_profilepage_userid'
                ],
            ]
        ];
    }
}
