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
include_once $CFG->dirroot.'/user/profile/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Класс обработчика конвертации данных из поля профиля пользователя в массив
 *
 * @package    local
 * @subpackage pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class convert_userfield_to_array extends base
{
    /**
     * {@inheritDoc}
     * @see \local_pprocessing\processor\handler\base::execute()
     */
    protected function execution_process(container $container)
    {
        $result = [];
        
        $field = $this->config['field'];
        $separator = $this->config['separator'];
        
        if( strpos($field, 'profile_field_') !== false )
        {
            $field = str_replace('profile_field_', 'profile.', $field);
        }
        
        $fieldval = $container->read('user.'.$field);
        if (!is_null($fieldval))
        {
            $result = explode($separator, $fieldval);
        }
        $result = array_map('trim', $result);

        
        logger::write_log(
            'processor',
            $this->get_type()."__".$this->get_code(),
            'debug',
            [
                'field' => $field,
                'result' => $result,
                'fieldval' => $fieldval
            ],
            'curstate'
        );
        
        $container->write('userfieldconverted', $result, false);
        return $result;
    }
}

