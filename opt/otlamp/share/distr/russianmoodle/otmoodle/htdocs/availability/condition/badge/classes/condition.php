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
 * Условие показа по наличию значка. Класс условия.
 *
 * @package    availability_badge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_badge;
 
defined('MOODLE_INTERNAL') || die();

use \badge;
use \core_availability\info as info;
use \coding_exception;
use \stdClass;
use \restore_dbops;
use \core_availability\condition as base_condition;
use \base_logger;

class condition extends base_condition 
{
    /**
     * @var int - ID значка для проверки наличия у пользователя
     */
    protected $badgeid = NULL;
 
    /**
     * Конструктор
     *
     * @param stdClass $structure - Данные для инициализации
     * @throws coding_exception - Исключение при недостаточности данных
     */
    public function __construct($structure)
    {
        global $CFG;

        require_once ($CFG->libdir . '/badgeslib.php');

        if (isset($structure->badgeid) && is_number($structure->badgeid)) {
            $this->badgeid = $structure->badgeid;
        } else {
            throw new coding_exception('Selected badge no id data');
        }
    }
 
    /**
     * Сохранение данных
     * @see \core_availability\tree_node::save()
     */
    public function save() 
    {
        $structure = new stdClass();
        $structure->badgeid = (integer)$this->badgeid;
        $structure->type = "badge";
        return $structure;
    }
 
    /**
     * Проверка на доступность элемента
     */
    public function is_available($not, info $info, $grabthelot, $userid) 
    {
        global $USER, $SITE;
        
        $allow = false;
        if ( ! $userid) 
        {
            $userid = $USER->id;
        }
        // проверка на существования бейджа
        if ($this->badge_exist($this->badgeid)) {
            // Получение значка, наличие которого требуется
            $badge = new badge($this->badgeid);

            // Получение значков пользователя в курсе
            $ucoursebadge = badges_get_user_badges($userid, $badge->courseid);

            // Поиск значка у пользователя
            if (is_array($ucoursebadge)) {
                foreach ($ucoursebadge as $badge) {
                    if ($this->badgeid == $badge->id) {
                        $allow = true;
                    }
                }
            }
        }
        // Интерпретация в зависимости от инверсии
        if ($not) 
        {
            $allow = ! $allow;
        }
        return $allow;
    }
 
    /**
     * Получить описание условия
     */
    public function get_description($full, $not, info $info) 
    {
        global $DB;
        // проверка на существования бейджа
        if ($this->badge_exist($this->badgeid)) {
            // Получение значка
            $badge = new badge($this->badgeid);

            // Тип значка
            $type = '';
            switch ($badge->type) {
                case '1':
                    $type = get_string('site', 'availability_badge');
                    break;
                case '2':
                    $type = get_string('course', 'availability_badge');
                    break;
                default:
                    break;
            }

            // Курс, в котором получен значек
            if (! is_null($badge->courseid)) {
                $course = $DB->get_record('course', array(
                    'id' => $badge->courseid
                ), 'fullname');
                $badge->coursename = $course->fullname;
            } else {
                $badge->coursename = '';
            }

            // Описание
            return get_string($not ? 'notholdbadge' : 'holdbadge', 'availability_badge') . " " . $badge->name . ' [' . $type . $badge->coursename . ']'; 
        }
        return get_string($not ? 'notholdbadge' : 'holdbadge', 'availability_badge') . " " . get_string('missing', 'availability_badge');
    }
 
    protected function get_debug_string() 
    {
        return '';
    }
    /**
     * Восстановление из бэкапа
     * {@inheritDoc}
     * @see \core_availability\tree_node::update_after_restore()
     */
    public function update_after_restore($restoreid, $courseid, base_logger $logger, $name)
    {
        $rec = restore_dbops::get_backup_ids_record($restoreid, 'badge', $this->badgeid);
        if (! $rec || ! $rec->newitemid) {
            $this->badgeid = '0';
            $logger->process('Restored item (' . $name . ') has availability condition on module that was not restored', \backup::LOG_WARNING);
        } else {
            
            if ($this->badgeid == $rec->newitemid) {
                return false;
            }
            $this->badgeid = $rec->newitemid;
        }
        return true;
    }
    /**
     * Проверяет есть ли значек в системе
     * 
     * @param int $babgeid
     * @return boolean
     */
    protected function badge_exist ($babgeid) {
        // Получение значков
        $badges = array_merge(\badges_get_badges(BADGE_TYPE_SITE, 0, '', '', 0, 0),\badges_get_badges(BADGE_TYPE_COURSE, 0, '', '', 0, 0));
        // Проверка наличия значка
        $badgeisfound = false;
        foreach( $badges as $badge )
        {
            if ($badge->id == $babgeid )
            {// Выбранный значек найден
                $badgeisfound = true;
                break;
            }
        }
        return $badgeisfound;
    }
}
?>