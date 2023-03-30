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

namespace local_pprocessing\processor\handler;

use local_pprocessing\container;
use local_pprocessing\logger;

defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика применения регулярки к переменной из контейнера
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apply_regex extends base
{
    const ONLY_FULL_MATCHES = 1;
    const ONLY_MASK_MATCHES = 2;
    
    // Возможности конфига и обработчика
    //
    // vartype - тип переменной, которая будет запрошена из контейнера для преобразований
    //      можно не указывать, если в конфиге varname указано непосредственно название переменной, имеющейся в контейнере
    //      можно указать user для указания в конфиге varname поля профиля пользователя (например, firstname, email, profile_field_speciality),
    //      в таком случае название поля профиля пользователя будет автоматически приведено к формату хранения в контейнере
    //
    // varname - наименование переменной в контейнере или поле профиля пользователя (тогда указать vartype=user)
    //
    // pattern - шаблон регулярки, который будет применён к переменной varname, по умолчанию полное соответствие - (.*)
    //
    // implode_glue - разделитель для склеивания результатов, можно не указывать, тогда результатом будет массив
    //
    // regexed_varname - наименование переменной в контейнере, в которую необходимо сохранить результат
    //      можно не указывать, тогда результат будет только возвращен хэндлером
    //      и сможет быть использован в следующем обработчике вызовом $this->result
    //      можно указать %varname% - тогда результат будет сохранен в ту же переменную контейнера, которая являлась и источником
    //
    // return - возвращает массив найденных полных полных соответствий регулярке
    //      или склеенный результат, разделенный указанным разделителем implode_glue, если указан
    
    
    
    
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        // Значение, которое нужно обработать
        $value = $this->get_required_parameter('value');
        // Шаблон регулярки, который будет применён к значению
        $pattern = $this->get_optional_parameter('pattern', '/(.*)/');
        // Разделитель для склеивания результатов, можно не указывать, тогда результатом будет массив
        $implodeglue = $this->get_optional_parameter('implode_glue', null);
        // Флаг (какие совпадения использовать в качестве результата)
        $flag = $this->get_optional_parameter('flag', self::ONLY_FULL_MATCHES);
        
        
        if (!is_null($value)) {
            preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);
            logger::write_log(
                'processor',
                $this->get_type()."__".$this->get_code(),
                'debug',
                [
                    'matches' => var_export($matches, true),
                ]
            );
            if (!empty($matches)) {
                $results = [];
                foreach($matches as $match)
                {
                    switch ($flag) {
                        case self::ONLY_MASK_MATCHES:
                            // собираем только полные совпадения
                            array_shift($match);
                            foreach ($match as $val) {
                                $results[] = $val;
                            }
                            break;
                        case self::ONLY_FULL_MATCHES:
                        default:
                            // собираем только полные совпадения
                            $results[] = array_shift($match);
                            break;
                    }
                }
                $result = (is_null($implodeglue) ? $results : implode($implodeglue, $results));
                return $result;
            } else {
                // Если совпадений не найдено, вернем переданное значение
                return $value;
            }
        }
    }
}
    
    