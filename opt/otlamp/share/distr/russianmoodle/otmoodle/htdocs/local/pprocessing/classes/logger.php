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

use dml_exception;


defined('MOODLE_INTERNAL') || die();

/**
 * Логгер
 *
 * @package    local_pprocessing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logger
{
    /**
     * БД объект
     *
     * @var \moodle_database
     */
    protected static $db = null;

    /**
     * запись в таблицу логов
     *
     * @param string $type - тип (сценарий/обработчик)
     * @param string $code - код
     * @param $status - статус операции
     * @param array $data - данные
     * @param string $comment
     * @param int $timestart - дата начала выполнения
     * @param int $timeend - дата окончания выполнения
     *
     * @return int $id - идентификатора записи лога
     */
    public static function write_log($type, $code = 0, $status = 'success', $data = '', $comment = '', $timestart = 0, $timeend = 0)
    {
        global $CFG;

        if ( is_null(static::$db) )
        {
            // установка объекта БД
            global $DB;
            static::$db = $DB;
        }

        $disablelogging = get_config('local_pprocessing', 'disable_logging');
        if (!empty($disablelogging)) {
            // процесс логирования выполнения сценариев отключен настройками
            return;
        }

        if ( empty($type) || empty($status) )
        {
            // обязательные поля
            return;
        }
        if( $status == 'debug' && ! $CFG->debugdeveloper)
        {
            // дебаг-логи пишем только если включен дебаг для разработчиков
            return;
        }
        if ( ! array_key_exists($type, constants::log_types) )
        {
            return;
        }
        if ( ! array_key_exists($status, constants::log_statuses) )
        {
            return;
        }
        if ( empty($timestart) )
        {
            $timestart = time();
        }
        if ( empty($timeend) )
        {
            $timeend = time();
        }
        try
        {
            return static::$db->insert_record('local_pprocessing_logs',
                    (object)[
                        'type' => (string)$type,
                        'code' => $code,
                        'data' => json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                        'comment' => (string)$comment,
                        'status' => $status,
                        'timestart' => intval($timestart),
                        'timeend' => intval($timeend)
                    ]);
        } catch ( dml_exception $e )
        {
            return 0;
        }
    }
}

