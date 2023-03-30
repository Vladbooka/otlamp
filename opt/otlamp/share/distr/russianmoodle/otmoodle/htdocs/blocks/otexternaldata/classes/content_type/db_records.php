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

namespace block_otexternaldata\content_type;

class db_records extends \block_otexternaldata\content_type {
    
    use \block_otexternaldata\connector\db;
    
    public function extend_form_definition(&$mform)
    {
        $this->dbcon_extend_form_definition($mform);
        return true;
    }
    
    public function compose_config(array $formdata)
    {
        return $this->dbcon_compose_config($formdata);
    }
    
    public function validate_config(array $config)
    {
        // Проверка подключения к БД
        $this->dbcon_init_via_config($config);
        // Валидация запроса не проводится, так как может не пройти валидацию из-за того,
        // что запрос выполняется на странице, где нет необходимых данных для подстановки
    }
    
    protected function sql_substitutions()
    {
        return $this->get_standard_substitutions();
    }
    
    protected function get_items(array $config)
    {
        $extdb = $this->dbcon_init_via_config($config);
        $sql = $this->dbcon_extract_sql_from_config($config);
        
        $substitutions = $this->sql_substitutions();
        $sql = $this->replace_substitutions($substitutions, $sql);
        
        return $this->dbcon_get_records($extdb, $sql);
    }
    
}