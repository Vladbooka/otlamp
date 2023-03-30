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

namespace local_pprocessing;

defined('MOODLE_INTERNAL') || die();

trait composite_key {
    
    protected $processed = [];
    protected $composite_key_fields = null;
    
    public function set_composite_key_fields($compositekeyfields)
    {
        if( is_array($compositekeyfields) )
        {
            $this->composite_key_fields = $compositekeyfields;
        }
    }
    
    protected function get_composite_key_fields()
    {
        return $this->composite_key_fields;
    }
    
    protected function construct_composite_key($container)
    {
        if( is_null($this->get_composite_key_fields()) )
        {
            logger::write_log(
                'processor',
                $this->get_type().'__'.$this->get_code(),
                'error',
                [
                    'get_composite_key_fields' => $this->get_composite_key_fields()
                ],
                'isnull'
            );
            return false;
        }
        
        $compositedata = [];
        
        foreach($this->get_composite_key_fields() as $field)
        {
            $value = $container->read($field);
            if (!is_null($value) )
            {
                $compositedata[$field] = $value;
            } else
            {
                logger::write_log(
                    'processor',
                    $this->get_type().'__'.$this->get_code(),
                    'error',
                    [
                        'get_composite_key_fields' => $this->get_composite_key_fields(),
                        'field' => $field,
                        'value' => $value,
                        'container' => $container->get_all()
                    ],
                    '!incontainer'
                );
                return false;
            }
        }
        return json_encode($compositedata);
    }
    
    /**
     * Получение данных обработанных прецедентов
     *
     * @param string $scenariocode - код сценария
     * @param string $handlercode - код обработчика
     *
     * @return array - массив данных уже обработанных прецедентов
     */
    protected function get_processed($scenariocode, $handlercode = null)
    {
        global $DB;
        
        $handlercode = is_null($handlercode) ? self::get_code() : (string)$handlercode;
        
        if( isset($this->processed[$scenariocode][$handlercode]) )
        {
            return $this->processed[$scenariocode][$handlercode];
        }
        
        $processed = [];
        $processedrecords = $DB->get_records('local_pprocessing_processed', [
            'scenariocode' => $scenariocode,
            'handlercode' => $handlercode
        ]);
        
        if( ! empty($processedrecords) )
        {
            foreach( $processedrecords as $processedrecord )
            {
                $processed[] = $processedrecord->data;
            }
        }
        
        $this->processed[$scenariocode][$handlercode] = $processed;
        
        return $processed;
    }
    
    protected function is_precedent_processed($scenariocode, $container, $handlercode = null)
    {
        $processed = $this->get_processed($scenariocode, $handlercode);
        
        if( is_null($this->get_composite_key_fields()) )
        {
            // составной ключ не используется - продолжаем работать будто прецедент не был обработан ранее
            return false;
        }
        
        $compositekey = $this->construct_composite_key($container);
        if( $compositekey === false )
        {
            // не удалось собрать составной ключ
            // вернем результат, будто прецедент уже обрабатывался ранее, чтобы исключить повторные срабатывания в будущем
            // но занесем информацию в лог
            logger::write_log(
                'processor',
                $this->get_type().'__'.$this->get_code(),
                'error',
                [
                    'composite_key_fields' => $this->get_composite_key_fields()
                ]
            );
            return true;
        }
        return in_array($compositekey, $processed);
    }
    
    protected function add_processed($scenariocode, $container)
    {
        global $DB;
        
        if( is_null($this->composite_key_fields) )
        {
            // составной ключ не используется
            // это не ошибка добавления, поэтому возвращаем null
            return null;
        }
        
        $compositekey = $this->construct_composite_key($container);
        if( $compositekey === false )
        {
            // не удалось собрать составной ключ по переданным параметрам
            // ошибка сохранения
            return false;
        }
        
        $object = new \stdClass();
        $object->scenariocode = $scenariocode;
        $object->handlercode = self::get_code();
        $object->data = $compositekey;
        $object->timemodified = time();
        
        return $DB->insert_record('local_pprocessing_processed', $object);
    }
    
    protected function remove_processed($scenariocode, $container, $handlercode = null)
    {
        global $DB;
        
        $result = true;
        
        if( is_null($this->composite_key_fields) )
        {
            // составной ключ не используется
            // это не ошибка добавления, поэтому возвращаем null
            return null;
        }
        
        $compositekey = $this->construct_composite_key($container);
        if( $compositekey === false )
        {
            // не удалось собрать составной ключ по переданным параметрам
            // ошибка сохранения
            return false;
        }
        
        $params = [];
        $params['scenariocode'] = $scenariocode;
        $params['handlercode'] = is_null($handlercode) ? self::get_code() : $handlercode;
        $params['data'] = $compositekey;
        $sql = 'SELECT id FROM {local_pprocessing_processed}
                 WHERE scenariocode = :scenariocode
                   AND handlercode = :handlercode
                   AND ' . $DB->sql_compare_text('data') . ' = ' . $DB->sql_compare_text(':data');
        if( $processeds = $DB->get_records_sql($sql, $params) )
        {
            foreach($processeds as $processed)
            {
                $result = $result && $DB->delete_records('local_pprocessing_processed', ['id' => $processed->id]);
            }
            return $result;
        } else
        {
            return false;
        }
    }
}