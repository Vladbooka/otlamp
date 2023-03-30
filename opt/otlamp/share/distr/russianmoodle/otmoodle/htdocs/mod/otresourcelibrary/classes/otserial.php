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

namespace mod_otresourcelibrary;

use local_opentechnology\otserial_base;

class otserial extends otserial_base
{
    /**
     * Формирование конфигурации плагина
     *
     */
    protected function setup_plugin_cfg()
    {
        $this->component = 'mod_otresourcelibrary';
        $this->tariffcodes = [
            'free',
            'otresourcelibrary'
        ];
    }
    
    /**
     * Конструктор
     *
     * @param bool upgrade - обновить версию плагина
     */
    public function __construct($upgrade = false)
    {
        global $CFG;
    
        // Код плагина
        $pcode = 'mod_otresourcelibrary';
    
        // Версия плагина
        if ($upgrade)
        {// Получение версии из файла
            $plugin = new \stdClass();
            require($CFG->dirroot . '/mod/otresourcelibrary/version.php');
            $pversion = $plugin->version;
        } else
        {// Получение версии из конфигурации
            $pversion = get_config($pcode, 'version');
        }
    
        // URL приложения
        $purl = $CFG->wwwroot;
    
        // Конструктор родителя
        parent::__construct($pcode, $pversion, $purl, [ 'upgrade' => $upgrade ]);
    }
}